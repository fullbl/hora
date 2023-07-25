<script setup lang="ts">
import { onMounted, ref } from 'vue';
import deliveryService from '@/service/DeliveryService';
import activityService from '@/service/ActivityService';
import stepService from '@/service/StepService';
import { computed } from '@vue/reactivity';
import { useDates } from '../composables/dates';
import Toast from 'primevue/toast';
import type Delivery from '@/interfaces/delivery';
import type Product from '@/interfaces/product';
import type Activity from '@/interfaces/activity';
import SelectButton from 'primevue/selectbutton';

const deliveries = ref<Array<Delivery>>([]);
const activities = ref<Array<Activity>>([]);
const { getWeekNumber, getDate, weekDays } = useDates();

const today = new Date();
const week = ref(getWeekNumber(today));
const year = ref(today.getFullYear());
const selectedSteps = ref(['soaking']);
const steps = stepService.getTypes()

onMounted(async () => {
    deliveries.value = await deliveryService.getAll()
    activities.value = await activityService.getAll()
});

const deliveryGroups = computed(() => {
    return deliveries.value.reduce(function (x, delivery) {
        for (const dp of delivery.deliveryProducts) {
            const date = getDate(year.value, week.value, delivery.harvestWeekDay)

            for (const step of dp.product.steps?.reverse() ?? []) {
                date.setMinutes(date.getMinutes() - step.minutes)
                while (date < getDate(year.value, week.value, 0)) {
                    date.setDate(date.getDate() + 7);
                }
                if (!delivery.weeks.includes(getWeekNumber(date))) {
                    continue;
                }
                if (selectedSteps.value.includes(step.name)) {
                    const dateHash = date.getDay();
                    if (!x.has(dateHash)) {
                        x.set(dateHash, new Map());
                    }
                    const products = x.get(dateHash);
                    if (undefined === products) {
                        continue;
                    }

                    const productHash = '' + dp.product.id + step.id
                    if (!products.has(productHash)) {
                        products.set(productHash, {
                            qty: 0,
                            done: 0,
                            decigrams: dp.product.decigrams,
                            delivery: delivery,
                            product: dp.product,
                            step: step.name
                        });
                    }
                    const product = products.get(productHash);
                    if (undefined === product) {
                        continue;
                    }
                    product.qty += dp.qty;
                    product.done = activities.value
                        .filter(a =>
                            a.delivery?.id === delivery.id &&
                            a.step.product?.id === dp.product.id &&
                            selectedSteps.value.includes(a.step.name) &&
                            a.year === year.value &&
                            a.week === week.value
                        )
                        .reduce((i, dp) => i + dp.qty, 0)
                }
            }

        }

        return x;
    }, new Map<number, Map<string, { step: string, qty: number, done: number, decigrams: number, delivery: Delivery, product: Product }>>());
});
</script>

<template>
    <div class="card">
        <div class="py-3">
            <input type="number" v-model="year" placeholder="year" />
            <input type="number" v-model="week" min="1" max="53" placeholder="week" />
        </div>
        <SelectButton v-model="selectedSteps" :options="steps" optionLabel="label" optionValue="value" multiple
            aria-labelledby="multiple">
            <template #option="slotProps">
                <i :class="stepService.getIcon(slotProps.option.value)"> {{ slotProps.option.value }}</i>
            </template>
        </SelectButton>
    </div>

    <Toast />

    <div class="card">
        <div class="grid">
            <div style="width:14.28%" v-for="weekDay of weekDays ">
                <h5>{{ weekDay.label }}<br>{{ getDate(year, week, weekDay.value).toLocaleDateString() }}</h5>
                <div v-for="[name, dp] in deliveryGroups.get(weekDay.value) ">
                    <i :class="stepService.getIcon(dp.step)">{{ dp.step }}</i>
                    {{ dp.product.name }}: {{ dp.qty }} ({{ dp.qty * dp.decigrams / 10 }}g)
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