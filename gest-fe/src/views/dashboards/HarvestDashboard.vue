<script setup lang="ts">
import { onMounted, ref } from 'vue';
import deliveryService from '@/service/DeliveryService';
import type Panel from 'primevue/panel';
import { useDates } from '../composables/dates';

const deliveryGroups = ref<Map<number, Map<string, Map<string, number>>>>(new Map());
const { weekDays } = useDates();

onMounted(async () => {
    deliveryGroups.value = (await deliveryService.getAll())
        .reduce(function (x, delivery) {
            if (!x.has(delivery.harvestWeekDay)) {
                x.set(delivery.harvestWeekDay, new Map());
            }

            for (const dp of delivery.deliveryProducts) {
                if (!x.has(delivery.harvestWeekDay)) {
                    x.set(delivery.harvestWeekDay, new Map());
                }
                const weekDay = x.get(delivery.harvestWeekDay);
                if (!weekDay.has(delivery.customer.fullName)) {
                    weekDay.set(delivery.customer.fullName, new Map());

                }
                const customer = weekDay.get(delivery.customer.fullName);
                const base = customer.get(dp.product.name) ?? 0;

                customer.set(dp.product.name, base + dp.qty);
            }

            return x;
        }, new Map());

});
</script>

<template>
    <div class="card">
        <div class="grid">
            <div style="width:14.28%" v-for="weekDay of weekDays">
                <h5>{{ weekDay.label }}</h5>
                <div>
                    <Panel v-for="[customer, products] in deliveryGroups.get(weekDay.value)" :header="customer" toggleable>
                        <table>
                            <p v-for="[product, qty] in products">
                                <tr>
                                    <td>
                                        {{ product }}
                                    </td>
                                    <td>
                                        {{ qty }}
                                    </td>
                                </tr>
                            </p>
                        </table>
                    </Panel>
                </div>
            </div>
        </div>
    </div>
</template>
