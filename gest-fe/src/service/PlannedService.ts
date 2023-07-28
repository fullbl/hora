import type Activity from '@/interfaces/activity';
import type Delivery from '@/interfaces/delivery';
import type Planned from '@/interfaces/planned';
import { useDates } from '@/views/composables/dates';
const { getWeekNumber, getDate, weekDays } = useDates();

const service = {
    getPlanned(
        deliveries: Delivery[], 
        activities: Activity[],
        selectedSteps: string[], 
        year: number, 
        week: number, 
        day?: number
        ) {
        return deliveries.reduce(function (x, delivery) {
            for (const dp of delivery.deliveryProducts) {
                const date = getDate(year, week, delivery.harvestWeekDay);

                for (const step of dp.product.steps?.toReversed() ?? []) {
                    date.setMinutes(date.getMinutes() - step.minutes);
                    while (date < getDate(year, week, 0)) {
                        date.setDate(date.getDate() + 7);
                    }
                    if (!delivery.weeks.includes(getWeekNumber(date))) {
                        continue;
                    }
                    if (!selectedSteps.includes(step.name)) {
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
                    product.done = activities
                        .filter(a =>
                            a.delivery?.id === delivery.id &&
                            a.step.product?.id === dp.product.id &&
                            selectedSteps.includes(a.step.name) &&
                            a.year === year &&
                            a.week === week
                        )
                        .reduce((i, dp) => i + dp.qty, 0)
                }
            }

            return x;
        }, new Map<number, Map<string, Planned>>());
    }
};

export default service;
