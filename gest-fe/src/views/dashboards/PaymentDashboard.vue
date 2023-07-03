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
const groupMode = ref('customer');

onMounted(async () => {
    deliveries.value = await deliveryService.getAll()
});

const payments = computed(() => {
    return deliveries.value.reduce(function (x, delivery) {
        const deliveryDate = getDate(year.value, week.value, delivery.deliveryWeekDay);
        if (!delivery.weeks.includes(getWeekNumber(deliveryDate))) {
            return x;
        }
        const amount =  delivery.deliveryProducts.reduce((i, p) => i + (p.product.price ?? 0) / 100 * p.qty, 0) - (delivery.customer?.discount ?? 0)


        x.push({customer: delivery.customer?.fullName ?? '', amount, method: delivery.paymentMethod ?? '-'})
        return x;
    }, [] as Array<{customer: string, amount: number, method: string|false}>);
});

</script>

<template>
    <div class="card">
        <input type="number" v-model="year" placeholder="year" />
        <input type="number" v-model="week" min="1" max="53" placeholder="week" />
    </div>

    <div class="card">
        Payments: 
        <div v-for="payment in payments">
            {{ payment.customer }} ({{ payment.method ?? '-' }}): {{ payment.amount }}€
        </div>
    </div>

</template>
