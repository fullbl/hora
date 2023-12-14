<script setup lang="ts">
import { computed, onMounted, ref, watch, watchEffect } from 'vue';
import type Calendar from 'primevue/calendar';
import Planner from '@/service/Planner';
import QtyHolder from '@/components/QtyHolder.vue';
import { useDialog } from '../composables/dialog';
import dataService from '@/service/DataService';
import productService from '@/service/ProductService';
import type {Step} from '@/interfaces/step';
import type { Delivery } from '@/interfaces/delivery';
import type { WaterBox } from '@/interfaces/product';
import dayjs, { Dayjs } from 'dayjs';
import Icon from '@/components/Icon.vue';
import type Planned from '@/interfaces/planned';

interface Planting {
    name: string;
    step: Step;
    deliveries: Delivery[];
    qty: number;
    done: number;
    grams: number;
    days: number;
}

interface Selected {
    plantings: Planting[];
}

const { dialog, hideDialog, showDialog } = useDialog();
const date = ref(dayjs());
const showDate = computed({
    get: () => date.value.toDate(),
    set: (val: Date) => {
        date.value = dayjs(val);
    }
});

const planner = new Planner();
const boxes = ref<WaterBox[]>([]);

onMounted(async () => {
    boxes.value = await productService.getWaterBoxes();
});
const planned = ref([] as Planned[]);
watchEffect(async () => {
    await planner.load(date.value.format('YYYY-MM-DD'));
    planned.value = planner.filterDay(['light', 'blackout'], date.value);
})

const single = ref<Selected>({ plantings: [] });

function select(s: Planting) {
    if (single.value.plantings.filter((x) => x.name === s.name).length > 0) {
        single.value.plantings = single.value.plantings.filter((x) => x.name !== s.name);
    } else {
        single.value.plantings = single.value.plantings.concat([s]);
    }
}

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
            steps: [] as Step[],
        };
        val.deliveries.push(p.delivery);

        x.set(p.product.id, {
            name: p.product.name,
            step: p.step,
            deliveries: val.deliveries,
            grams: (p.product.decigrams / 10) * p.qty + val.qty,
            days: p.step.minutes / 60 / 24,
            qty: val.qty + p.qty,
            done: val.done + p.done,
        });

        return x;
    }, new Map<number, Planting>());
});

async function save() {
    try {
        if (undefined === single.value) {
            return;
        }
        await dataService.post(import.meta.env.VITE_API_URL + 'planting', {
            plantings: single.value.plantings.map((s) => ({
                deliveries: s.deliveries.map((d) => d.id),
                step: s.step.id,
                qty: s.qty
            })),
            week: date.value.week(),
            year: date.value.year()
        });
        window.location.reload();
    } catch (e) {
        console.log(e);
        alert('error');
    }
}
</script>

<template>
    <div class="card">
        <div class="p-inputgroup flex-1">
            <Button @click="date = date.subtract(1, 'day')">&lt;</Button>
            <Calendar v-model="showDate" />
            <Button @click="date = date.add(1, 'day')">&gt;</Button>
        </div>
        <div class="flex justify-content-between mt-2">
            <h1>{{ date.format('dddd') }}</h1>
            <Button @click="dialog = 'Plant'" v-show="single.plantings.length > 0">PLANT</Button>
        </div>
    </div>

    <div class="flex flex-row flex-wrap justify-content-start">
        <div class="card mr-5" :class="single.plantings.filter((x) => x.name === p.name).length > 0 ? 'border-primary' : ''" v-for="[id, p] in products" @click="select(p)" style="width: 25em">
            <h2>{{ p.name }}</h2>
            <QtyHolder :qty="p.qty" class="mr-2"> {{ p.grams }} grams </QtyHolder>
            <QtyHolder :qty="p.days"> days </QtyHolder>
            <Icon :type="p.step.name" class="mx-2"/>{{ p.step.name }}
            <ProgressBar :value="(p.done / p.qty) * 100"> {{ p.done }} / {{ p.qty }} </ProgressBar>
        </div>

        <Dialog v-model:visible="showDialog" header="Planting Details" :modal="true" class="p-fluid">
            <h2>{{ dialog }}</h2>
            <div v-for="s in single.plantings">
                <p>{{ s.name }}: {{ s.grams }} grams ({{ s.step.name }}: {{ s.days }} days)</p>
            </div>
            <hr />

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
