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

const monthlyPayments = computed(() => {
    return deliveries.value.reduce(function (x, delivery) {
        if('monthly' === delivery.paymentMethod){
            x.push({customer: delivery.customer?.fullName ?? '', amount: delivery.price?? 0})
        }
        return x;
    }, [] as Array<{customer: string, amount: number}>);
});
const weeklyPayments = computed(() => {
    return deliveries.value.reduce(function (x, delivery) {
        if('monthly' === delivery.paymentMethod){
            return x;
        }

        const deliveryDate = getDate(year.value, week.value, delivery.deliveryWeekDay);
        if (!delivery.weeks.includes(getWeekNumber(deliveryDate))) {
            return x;
        }

        x.push({customer: delivery.customer?.fullName ?? '', amount: delivery.price?? 0})
        return x;
    }, [] as Array<{customer: string, amount: number}>);
});

</script>

<template>
    <div class="card">
        <input type="number" v-model="year" placeholder="year" />
        <input type="number" v-model="week" min="1" max="53" placeholder="week" />
    </div>

    <div class="card">
        Monthly payments: 
        <div v-for="payment in monthlyPayments">
            {{ payment.customer }}: {{ payment.amount }}€
        </div>
    </div>
    <div class="card">
        Weekly payments: 
        <div v-for="payment in weeklyPayments">
            {{ payment.customer }}: {{ payment.amount }}€
        </div>
    </div>

</template>
