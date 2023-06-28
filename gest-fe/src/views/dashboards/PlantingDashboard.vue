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
            const plantingDate = getDate(year.value, week.value, delivery.harvestWeekDay - dp.product.days);
            while (plantingDate < getDate(year.value, week.value, 0)) {
                plantingDate.setDate(plantingDate.getDate() + 7);
            }
            const harvestDate = new Date(plantingDate);
            harvestDate.setDate(harvestDate.getDate() + dp.product.days);

            if (!delivery.weeks.includes(getWeekNumber(harvestDate))) {
                continue;
            }

            const hash = plantingDate.getDay();
            if (!x.has(hash)) {
                x.set(hash, new Map());
            }
            const products = x.get(hash);
            if (undefined === products) {
                continue;
            }
            if (!products.has(dp.product.name)) {
                products.set(dp.product.name, {
                    qty: 0,
                    decigrams: dp.product.decigrams
                });
            }
            const product = products.get(dp.product.name);
            if (undefined !== product) {
                product.qty += dp.qty;
            }
        }

        return x;
    }, new Map<number, Map<string, { qty: number, decigrams: number }>>());
});

const weekTotal = computed(() => {
    let total = 0;
    deliveryGroups.value.forEach(function (x) {
        x.forEach(function (y) {
            total += y.decigrams * y.qty;
        })
    });

    return total / 10;
});

const dayTotal = function (weekDay: number) {
    let total = 0;
    deliveryGroups.value.get(weekDay)?.forEach(function (y) {
        total += y.decigrams * y.qty;
    });

    return total / 10;
}

</script>

<template>
    <div class="card">
        <input type="number" v-model="year" placeholder="year" />
        <input type="number" v-model="week" min="1" max="53" placeholder="week" />
    </div>

    <div class="card">
        Week total: {{ weekTotal }}g
    </div>

    <div class="card">
        <div class="grid">
            <div style="width:14.28%" v-for="weekDay of weekDays">
                <h5>{{ weekDay.label }}<br>{{ getDate(year, week, weekDay.value).toLocaleDateString() }}</h5>
                <b>Day total: {{ dayTotal(weekDay.value) }}g</b>
                <div v-for="[name, product] in deliveryGroups.get(weekDay.value)">
                    {{ name }} {{ product.qty }} ({{ product.qty * product.decigrams / 10 }}g)
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