<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import { useDates } from '../composables/dates';
import type Calendar from 'primevue/calendar';
import Planner from '@/service/Planner';
import QtyHolder from '@/components/QtyHolder.vue';
import { useDialog } from '../composables/dialog';
import dataService from '@/service/DataService';
import productService from '@/service/ProductService';
import type Step from '@/interfaces/step';
import type { Delivery } from '@/interfaces/delivery';
import type Product from '@/interfaces/product';

interface Soaking { name: string, step: Step, deliveries: Delivery[], qty: number, done: number, grams: number, hours: number }

const { dialog, deleteDialog, hideDialog } = useDialog();
const date = ref(new Date)
const { getWeekNumber } = useDates()
const planner = new Planner()
const boxes = ref<Product[]>([])

onMounted(async () => {
    (await planner.load()).flatPlanned()
    boxes.value = await productService.getAll()
});

const selected = ref<string[]>([])

function select(p: Soaking) {
    if (selected.value.filter(x => x === p.name).length > 0) {
        selected.value = selected.value.filter(x => x !== p.name)
    }
    else {
        selected.value = selected.value.concat([p.name])
    }
}


const box = ref()
const time = ref()
const single = ref<Soaking>()

const soakingTime = computed(() => {
    if (!time.value || !single.value) {
        return
    }

    const dayAfter = new Date(time.value.getTime())
    dayAfter.setDate(time.value.getDate() + 1)
    dayAfter.setHours(time.value.getHours() - single.value.hours)

    return dayAfter
})

const planned = computed(() => {
    return planner
        .setDates(date.value.getFullYear(), getWeekNumber(date.value))
        .filter(
            ['soaking'],
            date.value.getDay()
        )
})

const products = computed(() => {
    return planned.value.reduce((x, p) => {
        if (!p.product.id) {
            return x;
        }

        const val = x.get(p.product.id) ?? {
            qty: 0,
            grams: 0,
            done: 0,
            deliveries: [] as Delivery[],
            steps: [] as Step[]
        }
        val.deliveries.push(p.delivery)

        x.set(p.product.id, {
            name: p.product.name,
            step: p.step,
            deliveries: val.deliveries,
            grams: (p.product.decigrams / 10 * p.qty) + val.qty,
            hours: p.step.minutes / 60,
            qty: val.qty + p.qty,
            done: val.done + p.done,
        })

        return x
    }, new Map<number, Soaking>)
})


async function save() {
    try {
        await dataService.post(import.meta.env.VITE_API_URL + 'soaking', {
            box: box.value,
            time: soakingTime.value,
            deliveries: single.value?.deliveries.map(d => d.id),
            step: single.value?.step.id,
            qty: single.value?.qty,
            week: getWeekNumber(date.value),
            year: date.value.getFullYear()
        })
        window.location.reload()
    }
    catch (e) {
        alert('error')
    }


}

</script>

<template>
    <div class="card">
        <div class="p-inputgroup flex-1">
            <Button @click="date = new Date(date.getTime() - 24 * 60 * 60 * 1000)">&lt;</Button>
            <Calendar v-model="date" />
            <Button @click="date = new Date(date.getTime() + 24 * 60 * 60 * 1000)">&gt;</Button>
        </div>
        <div class="flex justify-content-between mt-2">
            <h1>{{ date.toLocaleDateString(undefined, { weekday: 'long' }) }}</h1>
            <Button @click="dialog = true" v-show="selected.length > 0">SOAK</Button>
        </div>
    </div>

    <div class="flex flex-row flex-wrap justify-content-start">
        <div class="card mr-5" :class="selected.filter(x => x === p.name).length > 0 ? 'border-primary' : ''" v-for="[id, p] in products" @click="select(p)"
            style="width: 25em">
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
        </div>

        <Dialog v-model:visible="dialog" style="width: 40rem" header="Soaking Details" :modal="true">
            <div class="field col-4">
                <label for="box">Water Box #</label>
                <InputNumber type="number" v-model="box" required autofocus />
                <label for="box">Planting time</label>
                <Calendar type="number" v-model="time" timeOnly required autofocus />
                <div v-if="soakingTime">
                    Soaking time: {{ soakingTime.toLocaleString() }}
                </div>
            </div>

            <template #footer>
                <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="hideDialog" />
                <Button label="Save" icon="pi pi-check" class="p-button-text" @click="save" />
            </template>
        </Dialog>
    </div>
</template>

<style>
    .card {
        cursor: pointer;
    }
</style>