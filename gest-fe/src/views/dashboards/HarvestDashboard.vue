<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import deliveryService from '@/service/DeliveryService';
import activityService from '@/service/ActivityService';
import type Panel from 'primevue/panel';
import { useDates } from '../composables/dates';
import Toast from 'primevue/toast';
import type {Delivery} from '@/interfaces/delivery';
import type Activity from '@/interfaces/activity';
import type Product from '@/interfaces/product';
import ProgressHolder from '@/components/ProgressHolder.vue';
import QtyHolder from '@/components/QtyHolder.vue';
import YearWeek from '@/components/YearWeek.vue';
import dayjs from 'dayjs';

const deliveries = ref<Array<Delivery>>([]);
const activities = ref<Array<Activity>>([]);

const { getDate, getWeekDates } = useDates();

const today = dayjs();
const week = ref(today.week());
const year = ref(today.year());

const groupMode = ref('customer');

onMounted(async () => {
    deliveries.value = await deliveryService.getAll()
    activities.value = await activityService.getAll()
});

const deliveryGroups = computed(() => {
    return deliveries.value.reduce(function (x, delivery) {
        if (!x.has(delivery.harvestDate.weekday())) {
            x.set(delivery.harvestDate.weekday(), new Map());
        }

        if (
            delivery.harvestDate.year() !== year.value ||
            delivery.harvestDate.week() !== week.value
        ) {
            return x;
        }


        for (const dp of delivery.deliveryProducts) {
            if(dp.product.type !== 'seeds') {
                continue;
            }
            if (!x.has(delivery.harvestDate.weekday())) {
                x.set(delivery.harvestDate.weekday(), new Map());
            }
            const weekDay = x.get(delivery.harvestDate.weekday());
            if (undefined === weekDay) {
                continue;
            }

            let hash = dp.product.name;
            let inverseHash = delivery.customer?.fullName ?? '';
            if ('customer' === groupMode.value) {
                hash = delivery.customer?.fullName ?? '';
                inverseHash = dp.product.name;
            }

            if (!weekDay.has(hash)) {
                weekDay.set(hash, new Map());

            }
            const group = weekDay.get(hash);
            if (undefined === group) {
                continue;
            }

            group.set(
                inverseHash,
                {
                    qty: (group.get(dp.product.name)?.qty ?? 0) + dp.qty,
                    done: activities.value
                        .filter(a =>
                            a.delivery?.id === delivery.id &&
                            a.step.product?.id === dp.product.id &&
                            ['light', 'dark'].includes(a.step.name) &&
                            a.year === year.value &&
                            a.week === week.value
                        )
                        .reduce((i, dp) => i + dp.qty, 0)
                    ,
                    delivery: delivery,
                    product: dp.product
                }
            );
        }

        return x;
    }, new Map<number, Map<string, Map<string, { qty: number, done: number, delivery: Delivery, product: Product }>>>());
});

const weekTotal = computed(() => {
    let total = 0;
    deliveryGroups.value.forEach(function (x) {
        x.forEach(function (y) {
            y.forEach(function (z) {
                total += z.qty;
            });
        })
    });

    return total;
});

const dayTotal = function (weekDay: number) {
    let total = 0;
    deliveryGroups.value.get(weekDay)?.forEach(function (y) {
        y.forEach(function (z) {
            total += z.qty;
        });
    });

    return total;
}

const groupWeekTotal = function (weekDay: number, groupName: string) {
    let total = 0;
    deliveryGroups.value.get(weekDay)?.get(groupName)?.forEach(function (z) {
        total += z.qty;
    });

    return total;
}

const groupTotal = function (groupName: string) {
    let total = 0;
    deliveryGroups.value.forEach(function (weekDay) {
        weekDay.get(groupName)?.forEach(function (z) {
            total += z.qty;
        });
    });

    return total;
}

const groupNames = computed(function () {
    let names: string[] = [];
    deliveryGroups.value.forEach(function (x) {
        names = names.concat(Array.from(x.keys()))
    });
    return Array.from(new Set(names)).sort((a,b) => a.localeCompare(b));
});

</script>

<template>
    <div class="card">
        <YearWeek v-model:year="year" v-model:week="week" />

        <div>
            <input type="radio" value="customer" v-model="groupMode" />
            <label for="customer">By customer</label>

            <input type="radio" value="product" v-model="groupMode" />
            <label for="product">By product</label>
        </div>
    </div>

    <div class="card">
        <Panel toggleable>
            <template #header>
                Week total: {{ weekTotal }}
            </template>
            <div class="flex flex-wrap justify-content-around">
                <div v-for="name in groupNames">
                    <QtyHolder :qty="groupTotal(name)">{{ name }}</QtyHolder>
                </div>
            </div>
        </Panel>
    </div>

    <Toast />

    <div class="card">
        <div class="grid">
            <div v-for="date of getWeekDates(year,week)" class="pr-1">
                <div v-if="dayTotal(date.weekday()) > 0">
                    <h5>{{ date.format('dddd DD/MM/YYYY') }}</h5>
                    <b>Day total: {{ dayTotal(date.weekday()) }}</b>
                    <div class="flex flex-wrap justify-content-between">
                        <Panel v-for="[groupName, products] in deliveryGroups.get(date.weekday())" :header="groupName"
                            toggleable>
                            <template #header>
                                {{ groupName }} {{ groupWeekTotal(date.weekday(), groupName) }}
                            </template>
                            <p v-for="[name, dp] in Array.from(products).sort(([x, a],[y, b]) => a.product.name.localeCompare(b.product.name))">
                                <QtyHolder :qty="dp.qty">{{ name }}</QtyHolder>
                                <ProgressHolder :dp="dp" />
                            </p>
                        </Panel>
                    </div>
                </div>
            </div>
        </div>
    </div>
</template>
