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
        if (!x.has(delivery.harvestWeekDay)) {
            x.set(delivery.harvestWeekDay, new Map());
        }

        const harvestDate = getDate(year.value, week.value, delivery.harvestWeekDay);
        if(!delivery.weeks.includes(getWeekNumber(harvestDate))){
            return x;
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
        <input type="number" v-model="year" placeholder="year" />
        <input type="number" v-model="week" min="1" max="53" placeholder="week" />
    </div>

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
