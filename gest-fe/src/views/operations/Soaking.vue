<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';

import type Delivery from '@/interfaces/delivery';
import { useDates } from '../composables/dates';
import type Calendar from 'primevue/calendar';
import ActivityButton from '@/components/ActivityButton.vue';
import type Activity from '@/interfaces/activity';
import Planner from '@/service/Planner';

const date = ref(new Date)
const { getWeekNumber } = useDates();

const deliveries = ref<Array<Delivery>>([]);
const activities = ref<Array<Activity>>([]);
const planner = new Planner()
onMounted(async () => {
    (await planner.load()).groupByProduct()
});

const deliveryGroups = computed(() => {
    return planner.getPlanned(
        deliveries.value,
        activities.value,
        ['soaking'],
        date.value.getFullYear(),
        getWeekNumber(date.value),
        date.value.getDay()
    ).get(date.value.getDay())
});

</script>

<template>
    <div class="card">
        <div class="p-inputgroup flex-1">
            <Button @click="date = new Date(date.getTime() - 24 * 60 * 60 * 1000)">&lt;</Button>
            <Calendar v-model="date" />
            <Button @click="date = new Date(date.getTime() + 24 * 60 * 60 * 1000)">&gt;</Button>
        </div>
    </div>
    <div class="flex flex-row flex-wrap justify-content-between">
        <div class="card" v-for="[a, g] in deliveryGroups" style="width: 25em">
            <h2>{{ g.delivery.customer?.fullName }}</h2>
            {{ g.stepName }} {{ g.qty }} {{ g.product.name }}
            <ProgressBar :value="(g.done / g.qty) * 100">
                {{ g.done }} / {{ g.qty }}
            </ProgressBar>
    
            <ActivityButton type="soaking" :baseProducts="[{qty: g.qty - g.done, product: g.product}]" :year="date.getFullYear()" :week="getWeekNumber(date)"
                :delivery="g.delivery" />
        </div>
    </div>
</template>