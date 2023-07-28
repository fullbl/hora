<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';

import type Delivery from '@/interfaces/delivery';
import { useDates } from '../composables/dates';
import type Calendar from 'primevue/calendar';
import ActivityButton from '@/components/ActivityButton.vue';
import type Activity from '@/interfaces/activity';
import Planner from '@/service/Planner';
import QtyHolder from '@/components/QtyHolder.vue';

const date = ref(new Date)
const { getWeekNumber } = useDates();

const planner = new Planner()
onMounted(async () => {
    (await planner.load()).flatPlanned()
});

const deliveryGroups = computed(() => {
    return planner.groupByProduct(
        planner
            .setDates(date.value.getFullYear(), getWeekNumber(date.value))
            .filter(['soaking'], date.value.getDay())
    )
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
        <div class="card" v-for="[name, planned] in deliveryGroups" style="width: 25em">
            <h2>{{ name }}</h2>

            <div class="card" v-for="p in planned">
                <QtyHolder :qty="p.qty + ''">
                    {{ p.delivery.customer?.fullName }}
                </QtyHolder>
                <p>
                    Harvest: {{ p.harvestDate?.toLocaleDateString() }}
                    <br>
                    Delivery: {{ p.deliveryDate?.toLocaleDateString() }}
                </p>
                <ProgressBar :value="(p.done / p.qty) * 100">
                    {{ p.done }} / {{ p.qty }}
                </ProgressBar>

                <ActivityButton type="soaking" :baseProducts="[{ qty: p.qty - p.done, product: p.product }]"
                    :year="date.getFullYear()" :week="getWeekNumber(date)" :delivery="p.delivery" />
            </div>
        </div>
    </div>
</template>