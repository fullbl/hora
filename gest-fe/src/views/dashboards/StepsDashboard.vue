<script setup lang="ts">
import { onMounted, ref } from 'vue';
import stepService from '@/service/StepService';
import { computed } from '@vue/reactivity';
import { useDates } from '../composables/dates';
import Toast from 'primevue/toast';
import SelectButton from 'primevue/selectbutton';
import Planner from '@/service/Planner';
import QtyHolder from '@/components/QtyHolder.vue';
import ProgressHolder from '@/components/ProgressHolder.vue';
import YearWeek from '@/components/YearWeek.vue';
import dayjs from 'dayjs';

const { getWeekDates } = useDates();

const today = dayjs();
const week = ref(today.week());
const year = ref(today.year());
const selectedSteps = ref(['soaking']);
const steps = stepService.getTypes();
const planner = new Planner();

onMounted(async () => {
    (await planner.load()).flatPlanned();
});

const deliveryGroups = computed(() => {
    return planner.groupByWeekDayAndProduct(planner.setDates(year.value, week.value).filter(selectedSteps.value));
});

const weekDayTotal = function (weekDay: number) {
    let totals = 0;
    const group = deliveryGroups.value.get(weekDay);
    if (undefined === group) {
        return 0;
    }

    group.forEach(function (x) {
        totals += x.qty;
    });

    return totals;
};

const weekTotal = computed(function () {
    let totals = 0;
    for (let i = 0; i < 6; i++) {
        totals += weekDayTotal(i);
    }

    return totals;
});
</script>

<template>
    <div class="card">
        <YearWeek v-model:year="year" v-model:week="week" />
        <SelectButton class="mt-3" v-model="selectedSteps" :options="steps" optionLabel="label" optionValue="value" multiple aria-labelledby="multiple">
            <template #option="slotProps">
                <i :class="stepService.getIcon(slotProps.option.value)"> {{ slotProps.option.value }}</i>
            </template>
        </SelectButton>
    </div>

    <div class="card">
        Week total: {{ weekTotal }}
    </div>

    <Toast />

    <div class="card">
        <div class="grid">
            <div style="width: 14.28%" v-for="date of getWeekDates(year, week)">
                <h5>{{ date.format('dddd DD/MM/YYYY') }}</h5>

                <div v-for="[name, dp] in deliveryGroups.get(date.weekday())">
                    <QtyHolder :qty="dp.qty">
                        <i :class="stepService.getIcon(dp.step.name)" />
                        {{ dp.step.name }}
                        {{ dp.product.name }}
                        <br />
                        <i v-if="'soaking' === dp.step.name"> {{ dp.step.minutes / 60 }} hours </i>
                    </QtyHolder>
                    <ProgressHolder :dp="dp" />
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss">
td,
th {
    padding: 4px;
}
</style>
