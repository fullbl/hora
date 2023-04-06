<script setup lang="ts">
import { onMounted, ref } from 'vue';
import deliveryService from '@/service/DeliveryService';
import { computed } from '@vue/reactivity';
import type Delivery from '@/interfaces/delivery';

const deliveries = ref<Array<Delivery>>([]);

const date = ref(new Date())
onMounted(async () => {
    deliveries.value = await deliveryService.getAll()
});

const deliveryGroups = computed(() => {
    return deliveries.value.reduce(function (x, delivery) {
        for (const dp of delivery.deliveryProducts) {
            const checkDate = new Date(date.value)
            checkDate.setDate(checkDate.getDate() + dp.product.days)
            console.log(checkDate, delivery.weekDay)
            if (checkDate.getDay() !== delivery.weekDay) {
                continue;
            }

            const obj = x.get(dp.product.name) ?? {
                qty: 0,
                grams: dp.product.grams
            }
            obj.qty += dp.qty

            x.set(dp.product.name, obj)
        }

        return x;
    }, new Map());
})
</script>

<template>
    <div class="card">
        <Calendar v-model="date" />
    </div>
    <div class="card">
        <table class="p-datatable-table">
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Grams</th>
            </tr>
            <tr class="col-12" v-for="[product, obj] in deliveryGroups">
                <td>{{ product }}</td>
                <td>{{ obj.qty }}</td>
                <td>{{ obj.qty * obj.grams }}</td>
            </tr>
        </table>
    </div>
</template>
