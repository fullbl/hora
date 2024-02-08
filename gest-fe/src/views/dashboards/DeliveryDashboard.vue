<script setup lang="ts">
import { computed, ref, watchEffect } from 'vue';
import deliveryService from '@/service/DeliveryService';
import activityService from '@/service/ActivityService';
import type Panel from 'primevue/panel';
import { useDates } from '../composables/dates';
import type { Delivery } from '@/interfaces/delivery';
import type Activity from '@/interfaces/activity';
import Toast from 'primevue/toast';
import QtyHolder from '@/components/QtyHolder.vue';
import YearWeek from '@/components/YearWeek.vue';
import dayjs from 'dayjs';
import DeliveryChangeForm from '@/components/forms/DeliveryChangeForm.vue';
import CustomerMenu from '@/components/CustomerMenu.vue';

const deliveries = ref<Array<Delivery>>([]);
const activities = ref<Array<Activity>>([]);

const { getDate, getWeekDates } = useDates();

const today = dayjs();
const week = ref(today.week());
const year = ref(today.year());
const single = ref<Delivery | null>(null);
const dialog = computed(() => single.value?.customer?.fullName ?? 'New Delivery');
const showDialog = computed({
    get: () => single.value !== null,
    set: (value) => {
        single.value = false === value ? null : single.value;
    }
});
const hideDialog = () => {
    single.value = null;
};
const form = ref(null as typeof DeliveryChangeForm | null);
const freeDeliveries = computed(() => {
    return deliveries.value.filter((d) => null === d.customer && d.deliveryDate.year() === year.value && d.deliveryDate.week() === week.value);
});
const save = async () => {
    form.value?.save();
    deliveries.value = await deliveryService.getFrom(getDate(year.value, week.value, 0).format('YYYY-MM-DD'));
    hideDialog();
};
const dayTotals = ref({ 0: 0, 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0 } as Record<number, number>);
const dayTotalsWithoutExtra = ref({ 0: 0, 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0 } as Record<number, number>);
const weekTotal = ref(0);
const weekTotalWithoutExtra = ref(0);
const deliveryGroups = ref({
    0: new Map(),
    1: new Map(),
    2: new Map(),
    3: new Map(),
    4: new Map(),
    5: new Map(),
    6: new Map()
} as Record<number, Map<string, { total: number; subZones: Map<string, { total: number; products: Map<string, number>; customers: Map<string, { total: number; products: Map<string, number>; delivery: Delivery }> }> }>>);

watchEffect(async () => {
    deliveries.value = await deliveryService.getFrom(getDate(year.value, week.value, 0).format('YYYY-MM-DD'));
    activities.value = await activityService.getAll();
});

watchEffect(async () => {
    dayTotals.value = { 0: 0, 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0 };
    dayTotalsWithoutExtra.value = { 0: 0, 1: 0, 2: 0, 3: 0, 4: 0, 5: 0, 6: 0 };
    weekTotal.value = 0;
    weekTotalWithoutExtra.value = 0;
    deliveryGroups.value = {
        0: new Map(),
        1: new Map(),
        2: new Map(),
        3: new Map(),
        4: new Map(),
        5: new Map(),
        6: new Map()
    };

    for (const delivery of deliveries.value) {
        if (delivery.deliveryDate.year() !== year.value || delivery.deliveryDate.week() !== week.value) {
            continue;
        }

        const weekday = delivery.deliveryDate.weekday();
        const zone = delivery.customer?.zones.find((z) => z.parent === null)?.name ?? 'EXTRA';
        const subZone = delivery.customer?.zones.find((z) => z.parent !== null)?.name ?? zone;

        let zoneGroup = deliveryGroups.value[weekday].get(zone);
        if (undefined === zoneGroup) {
            zoneGroup = { total: 0, subZones: new Map() };
        }
        let subZoneGroup = zoneGroup.subZones.get(subZone);
        if (undefined === subZoneGroup) {
            subZoneGroup = { total: 0, products: new Map(), customers: new Map() };
        }
        let customerGroup = subZoneGroup.customers.get(delivery.customer?.fullName ?? 'EXTRA');
        if (undefined === customerGroup) {
            customerGroup = { total: 0, products: new Map(), delivery: delivery };
        }

        for (const dp of delivery.deliveryProducts) {
            if (0 === dp.qty) {
                continue;
            }
            dayTotals.value[weekday] += dp.qty;
            weekTotal.value += dp.qty;
            zoneGroup.total += dp.qty;
            subZoneGroup.total += dp.qty;
            if (zone !== 'EXTRA') {
                dayTotalsWithoutExtra.value[weekday] += dp.qty;
                weekTotalWithoutExtra.value += dp.qty;
            }
            let product = subZoneGroup.products.get(dp.product.name);
            if (undefined === product) {
                product = 0;
            }

            subZoneGroup.products.set(dp.product.name, product + dp.qty);

            customerGroup.total += dp.qty;
            let customerProduct = customerGroup.products.get(dp.product.name);
            if (undefined === customerProduct) {
                customerProduct = 0;
            }
            customerGroup.products.set(dp.product.name, customerProduct + dp.qty);
        }

        subZoneGroup.customers.set(delivery.customer?.fullName ?? 'EXTRA', customerGroup);
        zoneGroup.subZones.set(subZone, subZoneGroup);
        deliveryGroups.value[weekday].set(zone, zoneGroup);
    }
});

const getWarningClass = function (delivery: Delivery) {
    if (delivery.lastWarning) return 'bg-red-600';
    if (delivery.warning) return 'bg-yellow-600';
    return '';
};

const changeDelivery = function (delivery: Delivery) {
    single.value = delivery;
};

const emptyDelivery = async function (delivery: Delivery) {
    if (undefined === delivery.id) {
        alert('error');
        return;
    }
    const freeDelivery = freeDeliveries.value.find(Boolean);
    const products = [...delivery.deliveryProducts, ...(freeDelivery?.deliveryProducts ?? [])];
    await deliveryService.move(delivery, [
        {
            delivery: delivery.id,
            deliveryProducts: []
        },
        {
            delivery: freeDelivery?.id ?? 0,
            deliveryProducts: products.map((dp) => ({
                product: { id: dp.product.id ?? 0 },
                qty: dp.qty
            }))
        }
    ]);
    deliveries.value = await deliveryService.getFrom(getDate(year.value, week.value, 0).format('YYYY-MM-DD'));
};

const deleteDelivery = async function (delivery: Delivery) {
    if (undefined === delivery.id) {
        alert('error');
        return;
    }
    const reason = prompt('Reason for deletion');
    await deliveryService.delete(delivery, reason ?? '');
    deliveries.value = await deliveryService.getFrom(getDate(year.value, week.value, 0).format('YYYY-MM-DD'));
};
</script>

<template>
    <div class="card">
        <YearWeek v-model:year="year" v-model:week="week" />
    </div>

    <div class="card">Week total: {{ weekTotal }} ({{ weekTotalWithoutExtra }})</div>

    <Toast />

    <div class="card">
        <div class="day">
            <div v-for="date of getWeekDates(year, week)">
                <h5>{{ date.format('dddd DD/MM/YY') }}</h5>
                <b>Day total: {{ dayTotals[date.weekday()] }} ({{ dayTotalsWithoutExtra[date.weekday()] }})</b>

                <div>
                    <Panel v-for="[zoneName, zoneGroup] in deliveryGroups[date.weekday()]" :pt="{ header: { title: zoneName + ': ' + zoneGroup.total } }" :header="zoneName + ': ' + zoneGroup.total" toggleable collapsed>
                        <Panel v-for="[subZoneName, subZoneGroup] in zoneGroup.subZones" :pt="{ header: { title: subZoneName + ': ' + subZoneGroup.total } }" :header="subZoneName + ': ' + subZoneGroup.total" toggleable collapsed>
                            <p v-for="[productName, qty] in Array.from(subZoneGroup.products).sort(([x, a], [y, b]) => x.localeCompare(y))" class="m-0">
                                <QtyHolder :qty="qty">{{ productName }}</QtyHolder>
                            </p>
                            <Panel
                                v-for="[customerName, customerGroup] in subZoneGroup.customers"
                                :pt="{
                                    header: {
                                        class: getWarningClass(customerGroup.delivery),
                                        title: customerName + ': ' + customerGroup.total
                                    }
                                }"
                                toggleable
                                collapsed
                            >
                                <template #header>
                                    <CustomerMenu :change="() => changeDelivery(customerGroup.delivery)" :empty="() => emptyDelivery(customerGroup.delivery)" :remove="() => deleteDelivery(customerGroup.delivery)" />
                                    <p class="px-1">{{ customerName }}: {{ customerGroup.total }}</p>
                                </template>
                                <p v-for="[productName, qty] in Array.from(customerGroup.products).sort(([x, a], [y, b]) => x.localeCompare(y))" class="m-0">
                                    <QtyHolder :qty="qty">{{ productName }}</QtyHolder>
                                </p>
                            </Panel>
                        </Panel>
                    </Panel>
                </div>
            </div>
        </div>
    </div>

    <Dialog v-model:visible="showDialog" :header="dialog" :modal="true" class="p-fluid" v-if="null !== single">
        <DeliveryChangeForm :single="single" :free-deliveries="freeDeliveries" ref="form" />

        <template #footer>
            <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="hideDialog" />
            <Button label="Save" icon="pi pi-check" class="p-button-text" @click="save()" />
        </template>
    </Dialog>
</template>

<style>
.p-panel .p-panel-header .p-panel-title {
    text-overflow: ellipsis;
    overflow: hidden;
}
.day {
    display: flex;
    justify-content: space-between;
}
</style>
