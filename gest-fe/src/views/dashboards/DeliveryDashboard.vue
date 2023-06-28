<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import deliveryService from '@/service/DeliveryService';
import type Panel from 'primevue/panel';
import { useDates } from '../composables/dates';
import type Delivery from '@/interfaces/delivery';

const deliveries = ref<Array<Delivery>>([]);
const { getWeekNumber, getDate, weekDays } = useDates();

const today = new Date();
const week = ref(getWeekNumber(today));
const year = ref(today.getFullYear());

onMounted(async () => {
    deliveries.value = await deliveryService.getAll()
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
            if (undefined === delivery.customer || undefined === weekDay) {
                continue;
            }
            if (!weekDay.has(delivery.customer.zone ?? '')) {
                weekDay.set(delivery.customer.zone ?? '', new Map());
            }
            const zone = weekDay.get(delivery.customer.zone ?? '');
            if (undefined === zone) {
                continue;
            }
            if (!zone.has(delivery.customer.fullName)) {
                zone.set(delivery.customer.fullName, new Map());
            }
            const customer = zone.get(delivery.customer.fullName);

            customer?.set(
                dp.product.name,
                (customer?.get(dp.product.name) ?? 0) + dp.qty);
        }

        return x;
    }, new Map<number, Map<string, Map<string, Map<string, number>>>>());
});

const zoneTotals = function (customers: Map<string, Map<string, number>>) {
    let totals = new Map();

    customers.forEach(function (x) {
        x.forEach(function (y, k) {
            if(!totals.has(k)){
                totals.set(k, 0);
            }

            totals.set(k, totals.get(k) + y);
        })
    });

    return totals;
}
</script>

<template>
    <div class="card">
        <input type="number" v-model="year" placeholder="year" />
        <input type="number" v-model="week" min="1" max="53" placeholder="week" />
    </div>
    <div class="card">
        <div class="grid">
            <div style="width:14.28%" v-for="weekDay of weekDays">
                <h5>{{ weekDay.label }}</h5>
                <div>
                    <Panel v-for="[zone, customers] in deliveryGroups.get(weekDay.value)" :header="zone" toggleable>
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
                        <Panel v-for="[customer, products] in customers" :header="customer" toggleable>
                            <table>
                                <tr v-for="[product, qty] in products">
                                    <td>
                                        {{ product }}
                                    </td>
                                    <td>
                                        {{ qty }}
                                    </td>
                                </tr>
                            </table>
                        </Panel>
                    </Panel>
                </div>
            </div>
        </div>
    </div>
</template>
