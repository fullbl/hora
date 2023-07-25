<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import deliveryService from '@/service/DeliveryService';
import activityService from '@/service/ActivityService';
import type Panel from 'primevue/panel';
import { useDates } from '../composables/dates';
import type Delivery from '@/interfaces/delivery';
import type Product from '@/interfaces/product';
import type Activity from '@/interfaces/activity';
import Toast from 'primevue/toast';
import type Step from '@/interfaces/step';
import ActivityButton from '@/components/ActivityButton.vue';

const deliveries = ref<Array<Delivery>>([]);
const activities = ref<Array<Activity>>([]);

const { getWeekNumber, getDate, weekDays } = useDates();

const today = new Date();
const week = ref(getWeekNumber(today));
const year = ref(today.getFullYear());

onMounted(async () => {
    deliveries.value = await deliveryService.getAll()
    activities.value = await activityService.getAll()
});

const deliveryGroups = computed(() => {
    return deliveries.value.reduce(function (x, delivery) {
        if (!x.has(delivery.deliveryWeekDay)) {
            x.set(delivery.deliveryWeekDay, new Map());
        }

        const deliveryDate = getDate(year.value, week.value, delivery.deliveryWeekDay);
        if (!delivery.weeks.includes(getWeekNumber(deliveryDate))) {
            return x;
        }

        for (const dp of delivery.deliveryProducts) {
            const weekDay = x.get(delivery.deliveryWeekDay);
            if (!delivery.customer || undefined === weekDay) {
                continue;
            }
            if (!weekDay.has(delivery.customer?.zone ?? '')) {
                weekDay.set(delivery.customer?.zone ?? '', new Map());
            }
            const zone = weekDay.get(delivery.customer?.zone ?? '');
            if (undefined === zone) {
                continue;
            }
            if (!zone.has(delivery.customer?.fullName)) {
                zone.set(delivery.customer.fullName, new Map());
            }
            const customer = zone.get(delivery.customer.fullName);

            customer?.set(
                dp.product.name,
                {
                    qty: (customer.get(dp.product.name)?.qty ?? 0) + dp.qty,
                    done: activities.value
                        .filter(a =>
                            a.delivery?.id === delivery.id &&
                            a.step.product?.id === dp.product.id &&
                            a.step.name === 'shipping' &&
                            a.year === year.value &&
                            a.week === week.value
                        )
                        .reduce((i, dp) => i + dp.qty, 0),
                    delivery: delivery,
                    product: dp.product
                }
            );
        }

        return x;
    }, new Map<number, Map<string, Map<string, Map<string, { qty: number, done: number, delivery: Delivery, product: Product }>>>>());
});

const zoneTotals = function (customers: Map<string, Map<string, { qty: number, done: number, delivery: Delivery, product: Product }>>) {
    let totals = new Map();

    customers.forEach(function (x) {
        x.forEach(function (y, k) {
            if (!totals.has(k)) {
                totals.set(k, 0);
            }

            totals.set(k, totals.get(k) + y.qty);
        })
    });

    return totals;
}


const customerTotal = function (products: Map<string, { qty: number, done: number, delivery: Delivery, product: Product }>) {
    let totals = 0;

    products.forEach(function (y, k) {
        totals += y.qty;
    })

    return totals;
}

const zoneTotal = function (customers: Map<string, Map<string, { qty: number, done: number, delivery: Delivery, product: Product }>>) {
    let totals = 0

    customers.forEach(function (x) {
        totals += customerTotal(x);
    });

    return totals;
}

const weekDayTotal = function (weekDay: number) {
    let totals = 0;
    const zones = deliveryGroups.value.get(weekDay)
    if (undefined === zones) {
        return 0
    }

    zones.forEach(function (x) {
        totals += zoneTotal(x);
    });

    return totals;
}

const weekTotal = computed(function () {
    let totals = 0;
    for (let i = 0; i < 6; i++) {
        totals += weekDayTotal(i);
    }

    return totals;
});

</script>

<template>
    <div class="card">
        <input type="number" v-model="year" placeholder="year" />
        <input type="number" v-model="week" min="1" max="53" placeholder="week" />
        Total: {{ weekTotal }}
    </div>
    <Toast />

    <div class="card">
        <div class="grid">
            <div style="width:14.28%" v-for="weekDay of weekDays">
                <h5>{{ weekDay.label }}: {{ weekDayTotal(weekDay.value) }}</h5>
                <div>
                    <Panel v-for="[zone, customers] in deliveryGroups.get(weekDay.value)"
                        :header="zone + ': ' + zoneTotal(customers)" toggleable collapsed>
                        <table>
                            <tr v-for="[product, qty] in zoneTotals(customers)">
                                <td>
                                    {{ product }}
                                </td>
                                <td>
                                    {{ qty }}
                                </td>
                            </tr>
                        </table>
                        <Panel v-for="[customer, products] in customers" :header="customer + ': ' + customerTotal(products)"
                            toggleable collapsed>
                            <p v-for="[name, dp] in products">
                                {{ name }}: {{ dp.qty }}
                                <ActivityButton
                                    v-if="0 < (dp.product.steps ?? []).filter((s: Step) => s.name === 'shipping').length"
                                    type="shipping" :baseProducts="[dp.product]" :year="year" :week="week"
                                    :delivery="dp.delivery" />
                                <ProgressBar :value="(dp.done / dp.qty) * 100">
                                    {{ dp.done }} / {{ dp.qty }}
                                </ProgressBar>
                            </p>
                        </Panel>
                    </Panel>
                </div>
            </div>
        </div>
    </div>
</template>
