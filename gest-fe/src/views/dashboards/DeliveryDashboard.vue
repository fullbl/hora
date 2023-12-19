<script setup lang="ts">
import { computed, ref, watchEffect } from 'vue';
import deliveryService from '@/service/DeliveryService';
import activityService from '@/service/ActivityService';
import type Panel from 'primevue/panel';
import { useDates } from '../composables/dates';
import type { Delivery } from '@/interfaces/delivery';
import type Product from '@/interfaces/product';
import type Activity from '@/interfaces/activity';
import Toast from 'primevue/toast';
import QtyHolder from '@/components/QtyHolder.vue';
import ProgressHolder from '@/components/ProgressHolder.vue';
import YearWeek from '@/components/YearWeek.vue';
import dayjs from 'dayjs';
import DeliveryChangeForm from '@/components/forms/DeliveryChangeForm.vue';

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
    try{
        deliveries.value = await deliveryService.getFrom(getDate(year.value, week.value, 0).format('YYYY-MM-DD'));
    }
    catch (e) {
        alert("C'è stato un errore nel salvataggio")
    }
    hideDialog();
}
watchEffect(async () => {
    deliveries.value = await deliveryService.getFrom(getDate(year.value, week.value, 0).format('YYYY-MM-DD'));
    activities.value = await activityService.getAll();
});

const deliveryGroups = computed(() => {
    return deliveries.value.reduce(function (x, delivery) {
        if (!x.has(delivery.deliveryDate.weekday())) {
            x.set(delivery.deliveryDate.weekday(), new Map());
        }
        if (delivery.deliveryDate.year() !== year.value || delivery.deliveryDate.week() !== week.value) {
            return x;
        }

        for (const dp of delivery.deliveryProducts) {
            const weekDay = x.get(delivery.deliveryDate.weekday());
            if (undefined === weekDay) {
                continue;
            }
            if (!weekDay.has(delivery.customer?.zone ?? '')) {
                weekDay.set(delivery.customer?.zone ?? '', new Map());
            }
            const zone = weekDay.get(delivery.customer?.zone ?? '');
            if (undefined === zone) {
                continue;
            }
            if (!zone.has(delivery.customer?.subZone ?? '')) {
                zone.set(delivery.customer?.subZone ?? '', new Map());
            }

            const subZone = zone.get(delivery.customer?.subZone ?? '');
            if (undefined === subZone) {
                continue;
            }
            if (!subZone.has(delivery.customer?.fullName ?? 'EXTRA')) {
                subZone.set(delivery.customer?.fullName ?? 'EXTRA', {
                    delivery: delivery,
                    products: new Map()
                });
            }
            const customer = subZone.get(delivery.customer?.fullName ?? 'EXTRA');

            customer?.products.set(dp.product.name, {
                qty: (customer.products.get(dp.product.name)?.qty ?? 0) + dp.qty,
                done: activities.value.filter((a) => a.delivery?.id === delivery.id && a.step.product?.id === dp.product.id && a.step.name === 'shipping' && a.year === year.value && a.week === week.value).reduce((i, dp) => i + dp.qty, 0),
                delivery: delivery,
                product: dp.product
            });
        }

        return x;
    }, new Map<number, Map<string, Map<string, Map<string, { delivery: Delivery, products: Map<string, { qty: number; done: number; delivery: Delivery; product: Product }>}>>>>());
});

const zoneTotals = function (customers: Map<string, {delivery: Delivery, products: Map<string, { qty: number; done: number; delivery: Delivery; product: Product }>}>) {
    let totals = new Map();

    customers.forEach(function (x) {
        x.products.forEach(function (y, k) {
            if (!totals.has(k)) {
                totals.set(k, 0);
            }

            totals.set(k, totals.get(k) + y.qty);
        });
    });

    return totals;
};

const customerTotal = function (products: Map<string, { qty: number; done: number; delivery: Delivery; product: Product }>) {
    let totals = 0;

    products.forEach(function (y, k) {
        totals += y.qty;
    });

    return totals;
};

const subZoneTotal = function (customers: Map<string, {delivery: Delivery, products: Map<string, { qty: number; done: number; delivery: Delivery; product: Product }>}>) {
    let totals = 0;

    customers.forEach(function (x) {
        totals += customerTotal(x.products);
    });

    return totals;
};

const zoneTotal = function (subZones: Map<string, Map<string, {delivery: Delivery, products: Map<string, { qty: number; done: number; delivery: Delivery; product: Product }>}>>) {
    let totals = 0;

    subZones.forEach(function (x) {
        totals += subZoneTotal(x);
    });

    return totals;
};

const dayTotal = function (weekDay: number) {
    let totals = 0;
    const zones = deliveryGroups.value.get(weekDay);
    if (undefined === zones) {
        return 0;
    }

    zones.forEach(function (x) {
        totals += zoneTotal(x);
    });

    return totals;
};

const weekTotal = computed(function () {
    let totals = 0;
    for (let i = 0; i < 6; i++) {
        totals += dayTotal(i);
    }

    return totals;
});

const getWarningClass = function (delivery: Delivery) {
    if (delivery.lastWarning) return 'bg-red-600';
    if (delivery.warning) return 'bg-yellow-600';
    return '';
};
</script>

<template>
    <div class="card">
        <YearWeek v-model:year="year" v-model:week="week" />
    </div>

    <div class="card">Week total: {{ weekTotal }}</div>

    <Toast />

    <div class="card">
        <div class="grid">
            <div style="width: 14.28%" v-for="date of getWeekDates(year, week)">
                <h5>{{ date.format('dddd DD/MM/YYYY') }}</h5>
                <b>Day total: {{ dayTotal(date.weekday()) }}</b>

                <div>
                    <Panel v-for="[zoneName, subZones] in deliveryGroups.get(date.weekday())" :header="zoneName + ': ' + zoneTotal(subZones)" toggleable collapsed>
                        <Panel v-for="[subZoneName, customers] in subZones" :header="subZoneName + ': ' + subZoneTotal(customers)" toggleable collapsed>
                            <p v-for="[productName, qty] in Array.from(zoneTotals(customers)).sort(([x, a], [y, b]) => x.localeCompare(y))" class="m-0">
                                <QtyHolder :qty="qty">{{ productName }}</QtyHolder>
                            </p>
                            <Panel v-for="[customerName, customerData] in customers" :header="customerName + ': ' + customerTotal(customerData.products)" toggleable collapsed>
                                <template #icons>
                                    <button class="p-panel-header-icon p-link mr-2" @click="single = customerData.delivery">
                                        <span class="pi pi-cog"></span>
                                    </button>
                                </template>
                                <p v-for="[productName, dp] in Array.from(customerData.products).sort(([x, a], [y, b]) => x.localeCompare(y))" class="m-0" :class="getWarningClass(dp.delivery)">
                                    <QtyHolder :qty="dp.qty">{{ productName }}</QtyHolder>
                                    <ProgressHolder :dp="dp" />
                                </p>
                            </Panel>
                        </Panel>
                    </Panel>
                </div>
            </div>
        </div>
    </div>

    <Dialog v-model:visible="showDialog" :header="dialog" :modal="true" class="p-fluid" v-if="null !== single">
        <DeliveryChangeForm :single="single" :free-deliveries="freeDeliveries" ref="form"/>

        <template #footer>
            <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="hideDialog" />
            <Button label="Save" icon="pi pi-check" class="p-button-text" @click="save()" />
        </template>
    </Dialog>
</template>
