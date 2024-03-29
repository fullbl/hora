<script setup lang="ts">
import { onMounted, ref, watch, watchEffect } from 'vue';
import { computed } from '@vue/reactivity';
import { useDates } from '../composables/dates';
import Toast from 'primevue/toast';
import type Planned from '@/interfaces/planned';
import Planner from '@/service/Planner';
import ProgressHolder from '@/components/ProgressHolder.vue';
import QtyHolder from '@/components/QtyHolder.vue';
import YearWeek from '@/components/YearWeek.vue';
import dayjs, { Dayjs } from 'dayjs';

const { getWeekDates, getDate } = useDates();

const today = dayjs();
const week = ref(today.week());
const year = ref(today.year());
const planner = new Planner
const deliveryGroups = ref<Map<string, Map<string, Planned>>> (new Map());

watchEffect(async () => {
    (await planner.load(getDate(year.value, week.value, 0).format('YYYY-MM-DD')))

    deliveryGroups.value = planner.groupByDayAndProduct(
        planner.filterWeek(['blackout'], year.value, week.value)
    )
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

const dayTotal = function (day: Dayjs) {
    let total = 0;
    deliveryGroups.value.get(day.format('YYYYMMDD'))?.forEach(function (y) {
        total += y.qty;
    });

    return total;
}

</script>

<template>
    <div class="card">
        <YearWeek v-model:year="year" v-model:week="week" />
    </div>

    <div class="card">
        Week total: {{ weekTotal }}
    </div>

    <Toast />

    <div class="card">
        <div class="grid">
            <div style="width:14.28%" v-for="date of getWeekDates(year, week)">
                <h5>{{ date.format('dddd DD/MM/YYYY') }}</h5>
                <b>Day total: {{ dayTotal(date) }}</b>
                <div v-for="[name, dp] in Array.from(deliveryGroups.get(date.format('YYYYMMDD')) ?? []).sort(([x, a], [y, b]) => a.product.name.localeCompare(b.product.name)) ">
                    <QtyHolder :qty="dp.qty">{{ dp.product.name }} ({{ dp.qty * dp.decigrams / 10 }}g{{ dp.product.weight ? ' + weight' : '' }})</QtyHolder>
                    <ProgressHolder :dp="dp" />
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