<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { computed } from '@vue/reactivity';
import { useDates } from '../composables/dates';
import Toast from 'primevue/toast';
import Planner from '@/service/Planner';
import ProgressHolder from '@/components/ProgressHolder.vue';
import QtyHolder from '@/components/QtyHolder.vue';
import YearWeek from '@/components/YearWeek.vue';
import dayjs from 'dayjs';

const { getWeekDates } = useDates();

const today = dayjs();
const week = ref(today.week());
const year = ref(today.year());
const planner = new Planner

onMounted(async () => {
    (await planner.load()).flatPlanned()
});

const deliveryGroups = computed(() => {
    return planner.groupByWeekDayAndProduct(
        planner
            .setDates(year.value, week.value)
            .filter(['blackout'])
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
                <b>Day total: {{ dayTotal(date.weekday()) }}</b>
                <div v-for="[name, dp] in deliveryGroups.get(date.weekday()) ">
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