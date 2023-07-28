<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import activityService from '@/service/ActivityService';

import deliveryService from '@/service/DeliveryService';
import type Panel from 'primevue/panel';
import { useDates } from '../composables/dates';
import type Delivery from '@/interfaces/delivery';
import type Activity from '@/interfaces/activity';
import Toast from 'primevue/toast';
import type Product from '@/interfaces/product';

const deliveries = ref<Array<Delivery>>([]);
const activities = ref<Array<Activity>>([]);

const { getWeekNumber, getDate, weekDays } = useDates();

const today = new Date();
const week = ref(getWeekNumber(today));
const year = ref(today.getFullYear());

onMounted(async () => {
    deliveries.value = await deliveryService.getAll()
    activities.value = await activityService.getAll()

});

const payments = computed(() => {
    return deliveries.value.reduce(function (x, delivery) {
        const deliveryDate = getDate(year.value, week.value, delivery.deliveryWeekDay);
        if (!delivery.weeks.includes(getWeekNumber(deliveryDate))) {
            return x;
        }
        const amount = delivery.deliveryProducts.reduce((i, p) => i + (p.product.price ?? 0) / 100 * p.qty, 0) - (delivery.customer?.discount ?? 0)
        const done = activities.value
            .filter(a =>
                ['payment'].includes(a.step.name) &&
                a.delivery?.id === delivery.id &&
                a.year === year.value &&
                a.week === week.value
            ).reduce((i, dp) => i + dp.qty, 0);

        x.push({
            customer: delivery.customer?.fullName ?? '',
            amount,
            done,
            delivery,
            products: delivery.deliveryProducts.map((dp) => dp.product),
            method: delivery.paymentMethod ?? '-'
        })
        return x;
    }, [] as Array<{ customer: string, amount: number, done: number, delivery: Delivery, products: Product[], method: string | false }>);
});

</script>

<template>
    <div class="card">
        <input type="number" v-model="year" placeholder="year" />
        <input type="number" v-model="week" min="1" max="53" placeholder="week" />
    </div>

    <Toast />

    <div class="card">
        Payments:
        <div v-for="payment in payments">
            {{ payment.customer }} ({{ payment.method ?? '-' }}): {{ payment.amount }}€
            <ProgressBar :value="(payment.done / payment.amount) * 100">
                {{ payment.done }} / {{ payment.amount }}
            </ProgressBar>
        </div>
    </div>
</template>
