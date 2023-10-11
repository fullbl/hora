<script setup lang="ts">
import { computed, onMounted, ref, watch } from 'vue';
import { useDates } from '../composables/dates';
import type Calendar from 'primevue/calendar';
import Planner from '@/service/Planner';
import QtyHolder from '@/components/QtyHolder.vue';
import { useDialog } from '../composables/dialog';
import dataService from '@/service/DataService';
import productService from '@/service/ProductService';
import type Step from '@/interfaces/step';
import type { Delivery } from '@/interfaces/delivery';
import type { WaterBox } from '@/interfaces/product';

interface Soaking {
    name: string;
    step: Step;
    deliveries: Delivery[];
    qty: number;
    done: number;
    grams: number;
    hours: number;
    data: {
        box?: number;
        script?: number;
    };
}

interface Selected {
    box?: WaterBox;
    plantingTime?: Date;
    soakingTime?: Date;
    soakings: Soaking[];
}

const { dialog, hideDialog } = useDialog();
const date = ref(new Date());
const { getWeekNumber, locale } = useDates();
const planner = new Planner();
const boxes = ref<WaterBox[]>([]);

onMounted(async () => {
    (await planner.load()).flatPlanned();
    boxes.value = await productService.getWaterBoxes();
});

const single = ref<Selected>({ soakings: [], plantingTime: new Date() });

function select(s: Soaking) {
    if (single.value.soakings.filter((x) => x.name === s.name).length > 0) {
        single.value.soakings = single.value.soakings.filter((x) => x.name !== s.name);
    } else {
        single.value.soakings = single.value.soakings.concat([s]);
    }
}

watch(single.value, (a) => {
    if (!single.value?.plantingTime) {
        return;
    }

    const dayAfter = new Date(single.value.plantingTime.getTime());
    dayAfter.setDate(single.value.plantingTime.getDate() + 1);
    const hours = single.value.soakings[0].hours;
    dayAfter.setHours(single.value.plantingTime.getHours() - hours);
    dayAfter.setMinutes(single.value.plantingTime.getMinutes() - (single.value.box?.decigrams ?? 0)); //decigrams used as minutes

    if (single.value.soakingTime?.getTime() !== dayAfter.getTime()) {
        single.value.soakingTime = dayAfter;
    }
});

const planned = computed(() => {
    return planner.setDates(date.value.getFullYear(), getWeekNumber(date.value)).filter(['soaking'], date.value.getDay());
});

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
            data: {} as any
        };
        val.deliveries.push(p.delivery);

        x.set(p.product.id, {
            name: p.product.name,
            step: p.step,
            deliveries: val.deliveries,
            grams: (p.product.decigrams / 10) * p.qty + val.qty,
            hours: p.step.minutes / 60,
            qty: val.qty + p.qty,
            done: val.done + p.done,
            data: p.activities?.reduce((y, a) => {
                y.box = a.data.box;
                y.script = a.data.script;
                return y;
            }, val.data)
        });

        return x;
    }, new Map<number, Soaking>());
});

async function save() {
    try {
        if (undefined === single.value) {
            return;
        }

        if (undefined === single.value.box) {
            alert('Please select a water box');
            return;
        }

        await dataService.post(import.meta.env.VITE_API_URL + 'soaking', {
            box: single.value.box.name,
            time: single.value.soakingTime,
            soakings: single.value.soakings.map((s) => ({
                deliveries: s.deliveries.map((d) => d.id),
                step: s.step.id,
                qty: s.qty
            })),
            week: getWeekNumber(date.value),
            year: date.value.getFullYear()
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
            <Button @click="date = new Date(date.getTime() - 24 * 60 * 60 * 1000)">&lt;</Button>
            <Calendar v-model="date" />
            <Button @click="date = new Date(date.getTime() + 24 * 60 * 60 * 1000)">&gt;</Button>
        </div>
        <div class="flex justify-content-between mt-2">
            <h1>{{ date.toLocaleDateString(locale, { weekday: 'long' }) }}</h1>
            <Button @click="dialog = true" v-show="single.soakings.length > 0">SOAK</Button>
        </div>
    </div>

    <div class="flex flex-row flex-wrap justify-content-start">
        <div class="card mr-5" :class="single.soakings.filter((x) => x.name === p.name).length > 0 ? 'border-primary' : ''" v-for="[id, p] in products" @click="select(p)" style="width: 25em">
            <h2>{{ p.name }}</h2>
            <QtyHolder :qty="p.qty" class="mr-2"> {{ p.grams }} grams </QtyHolder>
            <QtyHolder :qty="p.hours"> hours </QtyHolder>
            <ProgressBar :value="(p.done / p.qty) * 100"> {{ p.done }} / {{ p.qty }} </ProgressBar>
            <div v-if="p.data.box">Water box: {{ p.data.box }}</div>
            <div v-if="p.data.script">Script id: {{ p.data.script }}</div>
        </div>

        <Dialog v-model:visible="dialog" header="Soaking Details" :modal="true" class="p-fluid">
            <div v-for="s in single.soakings">
                <p>{{ s.name }}: {{ s.grams }} grams ({{ s.hours }} hours)</p>
            </div>
            <hr />
            <div class="field">
                <label for="box">Water Box #</label>
                <Dropdown v-model="single.box" :options="boxes" optionLabel="name" dataKey="id" placeholder="Select a Water Box" showClear filter> </Dropdown>
            </div>
            <div class="field">
                <label for="box">Planting time</label>
                <Calendar type="number" v-model="single.plantingTime" timeOnly required autofocus />
            </div>
            <div class="field">
                <div v-if="single.soakingTime">Soaking time: {{ single.soakingTime.toLocaleString() }}</div>
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
