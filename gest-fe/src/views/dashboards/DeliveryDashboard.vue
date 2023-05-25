<script setup lang="ts">
import { onMounted, ref } from 'vue';
import deliveryService from '@/service/DeliveryService';

const deliveryGroups = ref<Map<number, Map<string, number>>>(new Map());
const weekDays = [
    { label: 'Monday', value: 1 },
    { label: 'Tuesday', value: 2 },
    { label: 'Wednesday', value: 3 },
    { label: 'Thursday', value: 4 },
    { label: 'Friday', value: 5 },
    { label: 'Saturday', value: 6 },
    { label: 'Sunday', value: 0 },
];
onMounted(async () => {
    deliveryGroups.value = (await deliveryService.getAll())
        .reduce(function (x, delivery) {
            if (!x.has(delivery.deliveryWeekDay)) {
                x.set(delivery.deliveryWeekDay, new Map());
            }

            for (const dp of delivery.deliveryProducts) {
                const base = x.get(delivery.deliveryWeekDay).get(dp.product.name) ?? 0
                x.get(delivery.deliveryWeekDay).set(dp.product.name, base + dp.qty)
            }

            return x;
        }, new Map());

});
</script>

<template>
    <div class="card">
        <div class="grid">
            <div class="col-1" v-for="weekDay of weekDays">
                <h5>{{ weekDay.label }}</h5>
                <div class="col-12" v-for="[product, qty] in deliveryGroups.get(weekDay.value)">
                    {{ product }}: {{ qty }}
                </div>
            </div>
        </div>
    </div>
</template>
