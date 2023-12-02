<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import activityService from '@/service/ActivityService';

import deliveryService from '@/service/DeliveryService';
import { useDates } from '../composables/dates';
import type {Delivery} from '@/interfaces/delivery';
import type Activity from '@/interfaces/activity';
import Toast from 'primevue/toast';
import type Product from '@/interfaces/product';
import YearWeek from '@/components/YearWeek.vue';
import dayjs from 'dayjs';

const deliveries = ref<Array<Delivery>>([]);
const activities = ref<Array<Activity>>([]);

const { getDate } = useDates();

const today = dayjs();
const week = ref(today.week());
const year = ref(today.year());

watch([week, year], async () => {
    deliveries.value = await deliveryService.getFrom(getDate(year.value, week.value, 0).format('YYYY-MM-DD'))
    activities.value = await activityService.getAll()

});

const payments = computed(() => {
    return deliveries.value.reduce(function (x, delivery) {
        const deliveryDate = getDate(year.value, week.value, delivery.deliveryWeekDay);
        if (!delivery.weeks.includes(deliveryDate.week())) {
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
        <YearWeek v-model:year="year" v-model:week="week" />
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
