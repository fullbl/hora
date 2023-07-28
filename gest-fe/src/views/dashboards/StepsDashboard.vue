<script setup lang="ts">
import { onMounted, ref } from 'vue';
import deliveryService from '@/service/DeliveryService';
import activityService from '@/service/ActivityService';
import plannedService from '@/service/PlannedService';
import stepService from '@/service/StepService';
import { computed } from '@vue/reactivity';
import { useDates } from '../composables/dates';
import Toast from 'primevue/toast';
import type Delivery from '@/interfaces/delivery';
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
    return plannedService.getPlanned(deliveries.value, activities.value, selectedSteps.value, year.value, week.value)
});

const weekDayTotal = function (weekDay: number) {
    let totals = 0;
    const group = deliveryGroups.value.get(weekDay)
    if (undefined === group) {
        return 0
    }

    group.forEach(function (x) {
        totals += x.qty;
    });

    return totals;
}

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
        <div class="py-3">
            <input type="number" v-model="year" placeholder="year" />
            <input type="number" v-model="week" min="1" max="53" placeholder="week" />
            Total: {{ weekTotal }}
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
                <h5>{{ weekDay.label }} ({{ weekDayTotal(weekDay.value) }})<br>
                    {{ getDate(year, week, weekDay.value).toLocaleDateString() }}</h5>
                <div v-for="[name, dp] in deliveryGroups.get(weekDay.value) ">
                    <i :class="stepService.getIcon(dp.stepName)">{{ dp.stepName }}</i>
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