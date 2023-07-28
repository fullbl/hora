<script setup lang="ts">
import { onMounted, ref } from 'vue';
import { computed } from '@vue/reactivity';
import { useDates } from '../composables/dates';
import Toast from 'primevue/toast';
import Planner from '@/service/Planner';

const { getWeekNumber, getDate, weekDays } = useDates();

const today = new Date();
const week = ref(getWeekNumber(today));
const year = ref(today.getFullYear());
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