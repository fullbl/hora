import type Activity from '@/interfaces/activity';
import type Planned from '@/interfaces/planned';
import deliveryService from '@/service/DeliveryService';
import activityService from '@/service/ActivityService';
import { ref } from 'vue';
import type { Step, StepName } from '@/interfaces/step';
import type dayjs from 'dayjs';

if (!Array.prototype.toReversed) {
    Array.prototype.toReversed = function () {
        for (var i = this.length - 1, arr = []; i >= 0; --i) {
            arr.push(this[i]);
        }

        return arr;
    };
}

export default class Planner {
    activities: Activity[] = [];
    planned = ref([] as Planned[]);

    async load(from: string) {
        const deliveries = await deliveryService.getFrom(from);
        this.activities = await activityService.getAll();

        this.planned.value = deliveries
            .map((delivery) =>
                delivery.deliveryProducts.map((dp) => {
                    let minutes = 0;
                    return (dp.product.steps?.toReversed() ?? []).map((s: Step) => {
                        minutes += s.minutes;
                        //const activities = this.activities.filter((a) => a.delivery?.id === p.delivery.id && a.step.product?.id === p.product.id && a.step.name === p.step.name && a.year === year && a.week === week);
                        return {
                            qty: dp.qty,
                            done: 0,
                            decigrams: dp.product.decigrams,
                            delivery: delivery,
                            product: dp.product,
                            step: s,
                            date: delivery.harvestDate.subtract(minutes, 'minute')
                        };
                    });
                })
            )
            .flat(2);
        return this;
    }

    filterWeek(selectedSteps: StepName[], year: number, week: number) {
        return this.planned.value.filter((p) => {
            return selectedSteps.includes(p.step.name) && 
                p.date.week() === week && 
                p.date.year() === year
            ;
        });
    }

    filterDay(selectedSteps: StepName[], date: dayjs.Dayjs) {
        return this.planned.value.filter((p) => {
            return selectedSteps.includes(p.step.name) && 
                p.date.format('YYYYMMDD') === date.format('YYYYMMDD')
            ;
        });
    }

    groupByDayAndProduct(planned: Planned[]) {
        return planned.reduce((g, p) => {
            if (undefined === p.date) {
                return g;
            }
            let dayHash = p.date.format('YYYMMDD');
            const products = g.get(dayHash) ?? new Map();
            const productHash = p.product.id + p.step.name;
            let qty = p.qty;
            if (products.has(productHash)) {
                qty += products.get(productHash)?.qty ?? 0;
            }
            products.set(productHash, { ...p, qty });
            g.set(dayHash, products);

            return g;
        }, new Map<string, Map<string, Planned>>());
    }
}
