<script setup lang="ts">
import { onMounted, ref } from 'vue';
import deliveryService from '@/service/DeliveryService';
import { computed } from '@vue/reactivity';
import type Delivery from '@/interfaces/delivery';
import { useDates } from '../composables/dates';

const deliveries = ref<Array<Delivery>>([]);
const { getWeekNumber } = useDates();

const date = ref(new Date())
onMounted(async () => {
    deliveries.value = await deliveryService.getAll()
});

const deliveryGroups = computed(() => {
    return deliveries.value.reduce(function (x, delivery) {
        for (const dp of delivery.deliveryProducts) {
            const checkDate = new Date(date.value)
            checkDate.setDate(checkDate.getDate() + dp.product.days)
            if (
                checkDate.getDay() !== delivery.weekDay ||
                !delivery.weeks.includes(getWeekNumber(date.value))
                ) {
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

const moveDate = function (side: string) {
    const _date = new Date(date.value)
    switch (side) {
        case '+':
            _date.setDate(date.value.getDate() + 1)
            break;
        case '-':
            _date.setDate(date.value.getDate() - 1)
            break;
    }
    date.value = _date
}
</script>

<template>
    <div class="card">
        <Button icon="pi pi-arrow-left" class="mr-3" @click="moveDate('-')" />
        <Calendar v-model="date" dateFormat="dd/mm/yy" />
        <Button icon="pi pi-arrow-right" class="ml-3" @click="moveDate('+')" />
    </div>
    <div class="card">
        <table>
            <tr>
                <th>Product</th>
                <th>Qty</th>
                <th>Decigrams</th>
            </tr>
            <tr class="col-12" v-for="[product, obj] in deliveryGroups">
                <td>{{ product }}</td>
                <td>{{ obj.qty }}</td>
                <td>{{ obj.qty * obj.grams }}</td>
            </tr>
        </table>
    </div>
</template>

<style scoped lang="scss">
td,
th {
    padding: 4px
}
</style>