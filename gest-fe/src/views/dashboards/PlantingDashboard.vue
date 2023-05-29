<script setup lang="ts">
import { onMounted, ref } from 'vue';
import deliveryService from '@/service/DeliveryService';
import { computed } from '@vue/reactivity';
import type Delivery from '@/interfaces/delivery';
import { useDates } from '../composables/dates';

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
        for (const dp of delivery.deliveryProducts) {
            debugger
            const plantingDate = getDate(year.value, week.value, delivery.harvestWeekDay - dp.product.days);
            while(plantingDate < getDate(year.value, week.value, 0)){
                plantingDate.setDate(plantingDate.getDate() + 7);
            }
            const harvestDate = new Date(plantingDate);
            harvestDate.setDate(harvestDate.getDate() + dp.product.days);
                        
            if(!delivery.weeks.includes(getWeekNumber(harvestDate))){
                continue;
            }
            
            const hash = plantingDate.getDay();
            if (!x.has(hash)) {
                x.set(hash, new Map());
            }
            const products = x.get(hash);
            if (!products.has(dp.product.name)) {
                products.set(dp.product.name, {
                    qty: 0,
                    grams: dp.product.grams
                });
            }
            const product = products.get(dp.product.name);
            product.qty += dp.qty;
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
                <h5>{{ weekDay.label }} {{ getDate(year, week, weekDay.value).toLocaleDateString() }}</h5>
                <div v-for="[name, product] in deliveryGroups.get(weekDay.value)">
                        {{ name }} {{ product.qty }} ({{ product.qty * product.grams / 10 }} grams)
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">
td,
th {
    padding: 4px
}
</style>