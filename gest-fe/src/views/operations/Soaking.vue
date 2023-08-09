<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';

import { useDates } from '../composables/dates';
import type Calendar from 'primevue/calendar';
import ActivityButton from '@/components/ActivityButton.vue';
import Planner from '@/service/Planner';
import QtyHolder from '@/components/QtyHolder.vue';

const date = ref(new Date)
const { getWeekNumber } = useDates()

const planner = new Planner()

onMounted(async () => {
    (await planner.load()).flatPlanned()
});

const box = ref()
const time = ref()

const soakingTime = computed(() => time.value ? time.value.toLocaleString() : '')

const products = computed(() => {
    const dayAfter = new Date(date.value.getTime())
    dayAfter.setDate(date.value.getDate() + 1)
    return planner
        .setDates(dayAfter.getFullYear(), getWeekNumber(dayAfter))
        .filter(
            ['soaking'],
            dayAfter.getDay()
        ).reduce((x, p) => {
            if (!p.product.id) {
                return x;
            }

            const val = x.get(p.product.id) ?? { qty: 0, grams: 0, done: 0 }
            x.set(p.product.id, {
                name: p.product.name,
                grams: (p.product.decigrams / 10 * p.qty) + val.qty,
                hours: p.step.minutes / 60,
                qty: val.qty + p.qty,
                done: val.done + p.done
            })

            return x
        }, new Map<number, { name: string, qty: number, done: number, grams: number, hours: number }>)
})

</script>

<template>
    <div class="card">
        <div class="p-inputgroup flex-1">
            <Button @click="date = new Date(date.getTime() - 24 * 60 * 60 * 1000)">&lt;</Button>
            <Calendar v-model="date" />
            <Button @click="date = new Date(date.getTime() + 24 * 60 * 60 * 1000)">&gt;</Button>
        </div>
        <div>{{ date.toLocaleDateString(undefined, { weekday: 'long' }) }}</div>
    </div>

    <div class="flex flex-row flex-wrap justify-content-start">
        <div class="card mr-5" v-for="[id, p] in products" style="width: 25em">
            <h2>{{ p.name }}</h2>
            <QtyHolder :qty="p.qty" class="mr-2">
                {{ p.grams }} grams
            </QtyHolder>
            <QtyHolder :qty="p.hours">
                hours
            </QtyHolder>
            <ProgressBar :value="(p.done / p.qty) * 100">
                {{ p.done }} / {{ p.qty }}
            </ProgressBar>
            <ActivityButton type="soaking" :baseProducts="[{ qty: p.qty - p.done, product: p.product }]"
                :year="date.getFullYear()" :week="getWeekNumber(date)" :delivery="p.delivery" />

        </div>

        <div class="field">
            <label for="box">Box</label>
            <InputNumber type="number" v-model="box" required autofocus />
        </div>
        <div class="field">
            <label for="box">Planting tim</label>
            <Calendar type="number" v-model="time" timeOnly required autofocus />
            Soaking time: {{ soakingTime }}
        </div>
    </div>
</template>