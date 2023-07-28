import type Activity from '@/interfaces/activity';
import type Delivery from '@/interfaces/delivery';
import type Planned from '@/interfaces/planned';
import { useDates } from '@/views/composables/dates';
import deliveryService from '@/service/DeliveryService';
import activityService from '@/service/ActivityService';
import type Step from '@/interfaces/step';
import { ref } from 'vue';

const { getWeekNumber, getDate, weekDays } = useDates();

export default class Planner {
    deliveries: Delivery[] = [];
    activities: Activity[] = [];
    planned = ref([] as Planned[]);

    async load() {
        this.deliveries = await deliveryService.getAll();
        this.activities = await activityService.getAll();

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
                            stepName: s.name,
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
            const harvestDate = getDate(year, week, p.delivery.harvestWeekDay);
            const deliveryDate = getDate(year, week, p.delivery.deliveryWeekDay);
            const date = new Date(harvestDate.getTime());
            date.setMinutes(date.getMinutes() - p.minutesBeforeHarvest);
            while (date < getDate(year, week, 0)) {
                date.setDate(date.getDate() + 7);
                deliveryDate.setDate(deliveryDate.getDate() + 7);
                harvestDate.setDate(harvestDate.getDate() + 7);
            }
            return {
                ...p,
                date: date,
                harvestDate: harvestDate,
                deliveryDate: deliveryDate,
                done: this.activities.filter((a) => a.delivery?.id === p.delivery.id && a.step.product?.id === p.product.id && a.step.name === p.stepName && a.year === year && a.week === week).reduce((i, dp) => i + dp.qty, 0)
            };
        });

        return this;
    }

    filter(selectedSteps: string[], day?: number) {
        return this.planned.value.filter((p) => {
            return undefined !== p.date && p.delivery.weeks.includes(getWeekNumber(p.date)) && selectedSteps.includes(p.stepName) && (undefined === day || day === p.date.getDay());
        });
    }

    groupByProduct(planned: Planned[]) {
        return planned.reduce((g, p) => {
            const productHash = p.product.name;
            const products = g.get(productHash) ?? [];
            products.push(p);
            g.set(productHash, products);

            return g;
        }, new Map<string, Planned[]>());
    }

    groupByWeekDayAndProduct(planned: Planned[]) {
        return planned.reduce((g, p) => {
            const weekDayHash = p.date?.getDay() ?? 100;
            const products = g.get(weekDayHash) ?? new Map();
            const productHash = p.product.id + p.stepName;
            let qty = p.qty
            if (products.has(productHash)) {
                qty += products.get(productHash)?.qty ?? 0;
            }
            products.set(productHash, {...p, qty});
            g.set(weekDayHash, products);

            return g;
        }, new Map<number, Map<string, Planned>>());
    }
}
