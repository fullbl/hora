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
            const date = getDate(year, week, p.delivery.harvestWeekDay);
            date.setMinutes(date.getMinutes() - p.minutesBeforeHarvest);
            while (date < getDate(year, week, 0)) {
                date.setDate(date.getDate() + 7);
            }
            return { ...p, date: date };
        });

        return this;
    }

    filter(selectedSteps: string[], day?: number) {
        return this.planned.value.filter((p) => {
            return undefined !== p.date && p.delivery.weeks.includes(getWeekNumber(p.date)) && selectedSteps.includes(p.stepName) && (undefined === day || day === p.date.getDay());
        });

    }

    groupByWeekAndProduct(planned: Planned[]) {
        return planned.reduce((g, p) => {
            const weekDayHash = p.date?.getDay() ?? 100;
            const products = g.get(weekDayHash) ?? new Map();
            const productHash = p.product.id + p.stepName;
            if (products.has(productHash)) {
                p.qty += products.get(productHash)?.qty ?? 0;
            }
            products.set(productHash, p);
            g.set(weekDayHash, products);

            return g;
        }, new Map<number, Map<string, Planned>>());
    }

    getPlanned(deliveries: Delivery[], activities: Activity[], selectedSteps: string[], year: number, week: number, day?: number) {
        return deliveries.reduce(function (x, delivery) {
            const date = getDate(year, week, delivery.harvestWeekDay);
            for (const dp of delivery.deliveryProducts) {
                for (const step of dp.product.steps?.toReversed() ?? []) {
                    if (!selectedSteps.includes(step.name)) {
                        continue;
                    }
                    date.setMinutes(date.getMinutes() - step.minutes);
                    while (date < getDate(year, week, 0)) {
                        date.setDate(date.getDate() + 7);
                    }
                    if (!delivery.weeks.includes(getWeekNumber(date))) {
                        continue;
                    }
                    if (undefined !== day && day !== date.getDay()) {
                        continue;
                    }
                    const dateHash = date.getDay();
                    if (!x.has(dateHash)) {
                        x.set(dateHash, new Map());
                    }
                    const products = x.get(dateHash);
                    if (undefined === products) {
                        continue;
                    }

                    const productHash = '' + dp.product.id + step.id;
                    if (!products.has(productHash)) {
                        products.set(productHash, {
                            qty: 0,
                            done: 0,
                            decigrams: dp.product.decigrams,
                            delivery: delivery,
                            product: dp.product,
                            stepName: step.name
                        });
                    }
                    const product = products.get(productHash);
                    if (undefined === product) {
                        continue;
                    }
                    product.qty += dp.qty;
                    product.done = activities.filter((a) => a.delivery?.id === delivery.id && a.step.product?.id === dp.product.id && selectedSteps.includes(a.step.name) && a.year === year && a.week === week).reduce((i, dp) => i + dp.qty, 0);
                }
            }

            return x;
        }, new Map<number, Map<string, Planned>>());
    }
}
