<script setup lang="ts">
import { onMounted, ref } from 'vue';
import deliveryService from '@/service/DeliveryService';
import activityService from '@/service/ActivityService';
import { computed } from '@vue/reactivity';
import { useDates } from '../composables/dates';
import Toast from 'primevue/toast';
import ActivityButton from '@/components/ActivityButton.vue';
import type Delivery from '@/interfaces/delivery';
import type Product from '@/interfaces/product';
import type Activity from '@/interfaces/activity';
import type Step from '@/interfaces/step';

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
                    done: 0,
                    decigrams: dp.product.decigrams,
                    delivery: delivery,
                    product: dp.product
                });
            }
            const product = products.get(dp.product.name);
            if (undefined === product) {
                continue;
            }
            product.qty += dp.qty;
            product.done = activities.value
                .filter(a =>
                    a.delivery?.id === delivery.id &&
                    a.step.product?.id === dp.product.id &&
                    ['planting'].includes(a.step.name) &&
                    a.year === year.value &&
                    a.week === week.value
                )
                .reduce((i, dp) => i + dp.qty, 0)
        }

        return x;
    }, new Map<number, Map<string, { qty: number, done: number, decigrams: number, delivery: Delivery, product: Product }>>());
});

const weekTotal = computed(() => {
    let total = 0;
    deliveryGroups.value.forEach(function (x) {
        x.forEach(function (y) {
            total += y.qty;
        })
    });

    return total;
});

const dayTotal = function (weekDay: number) {
    let total = 0;
    deliveryGroups.value.get(weekDay)?.forEach(function (y) {
        total += y.qty;
    });

    return total;
}

</script>

<template>
    <div class="card">
        <input type="number" v-model="year" placeholder="year" />
        <input type="number" v-model="week" min="1" max="53" placeholder="week" />
    </div>

    <div class="card">
        Week total: {{ weekTotal }}
    </div>

    <Toast />

    <div class="card">
        <div class="grid">
            <div style="width:14.28%" v-for="weekDay of  weekDays ">
                <h5>{{ weekDay.label }}<br>{{ getDate(year, week, weekDay.value).toLocaleDateString() }}</h5>
                <b>Day total: {{ dayTotal(weekDay.value) }}</b>
                <div v-for="[name, dp] in deliveryGroups.get(weekDay.value) ">
                    {{ name }}: {{ dp.qty }} ({{ dp.qty * dp.decigrams / 10 }}g)
                    <ActivityButton v-if="0 < (dp.product.steps ?? []).filter((s: Step) => s.name === 'planting').length"
                        type="planting" :baseProducts="[dp.product]" :year="year" :week="week" :delivery="dp.delivery" />
                    <ProgressBar :value="(dp.done / dp.qty) * 100">
                        {{ dp.done }} / {{ dp.qty }}
                    </ProgressBar>
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