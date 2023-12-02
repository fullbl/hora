import type Activity from '@/interfaces/activity';
import type { Delivery } from '@/interfaces/delivery';
import type Planned from '@/interfaces/planned';
import { useDates } from '@/views/composables/dates';
import deliveryService from '@/service/DeliveryService';
import activityService from '@/service/ActivityService';
import { ref } from 'vue';
import type { Step, StepName } from '@/interfaces/step';

const { getDate } = useDates();
if (!Array.prototype.toReversed) {
    Array.prototype.toReversed = function () {
        for (var i = this.length - 1, arr = []; i >= 0; --i) {
            arr.push(this[i]);
        }

        return arr;
    };
}

export default class Planner {
    deliveries: Delivery[] = [];
    activities: Activity[] = [];
    planned = ref([] as Planned[]);

    async load(from: string) {
        this.deliveries = await deliveryService.getFrom(from);
        //this.activities = await activityService.getAll();

        return this;
    }

    flatPlanned() {
        this.planned.value = this.deliveries
            .map((delivery) =>
                delivery.deliveryProducts.map((dp) => {
                    let minutes = 0;
                    return (dp.product.steps?.toReversed() ?? []).map((s: Step) => {
                        minutes += s.minutes;
                        return {
                            qty: dp.qty,
                            done: 0,
                            decigrams: dp.product.decigrams,
                            delivery: delivery,
                            product: dp.product,
                            step: s,
                            minutesBeforeHarvest: minutes
                        };
                    });
                })
            )
            .flat(2);

        return this;
    }

    setDates(year: number, week: number) {
        this.planned.value = this.planned.value.map((p) => {
            let harvestDate = p.delivery.harvestDate;
            let deliveryDate = p.delivery.deliveryDate;
            let date = harvestDate.subtract(p.minutesBeforeHarvest, 'minute');
            while (date < getDate(year, week, 1)) {
                date = date.add(1, 'week')
                deliveryDate = deliveryDate.add(1, 'week')
                harvestDate = harvestDate.add(1, 'week')
            }
            const activities = this.activities.filter((a) => a.delivery?.id === p.delivery.id && a.step.product?.id === p.product.id && a.step.name === p.step.name && a.year === year && a.week === week);
            return {
                ...p,
                date,
                harvestDate,
                deliveryDate,
                done: activities.reduce((i, dp) => i + dp.qty, 0),
                activities
            };
        });

        return this;
    }

    filter(selectedSteps: StepName[], day?: number) {
        return this.planned.value.filter((p) => {
            return undefined !== p.deliveryDate && 
                p.delivery.deliveryDate.week() === p.deliveryDate.week() && 
                selectedSteps.includes(p.step.name) && 
                (undefined === day || (undefined !== p.date && day === p.date.weekday()));
        });
    }

    groupByWeekDayAndProduct(planned: Planned[]) {
        return planned.reduce((g, p) => {
            if (undefined === p.date) {
                return g;
            }
            let weekDayHash = p.date.weekday();
            const products = g.get(weekDayHash) ?? new Map();
            const productHash = p.product.id + p.step.name;
            let qty = p.qty;
            if (products.has(productHash)) {
                qty += products.get(productHash)?.qty ?? 0;
            }
            products.set(productHash, { ...p, qty });
            g.set(weekDayHash, products);

            return g;
        }, new Map<number, Map<string, Planned>>());
    }
}
