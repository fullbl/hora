<script setup lang="ts">
import { computed, onMounted, ref } from 'vue';
import deliveryService from '@/service/DeliveryService';
import activityService from '@/service/ActivityService';
import type Panel from 'primevue/panel';
import { useDates } from '../composables/dates';
import Toast from 'primevue/toast';
import type Delivery from '@/interfaces/delivery';
import type Activity from '@/interfaces/activity';
import type Product from '@/interfaces/product';

const deliveries = ref<Array<Delivery>>([]);
const activities = ref<Array<Activity>>([]);

const { getWeekNumber, getDate, weekDays } = useDates();

const today = new Date();
const week = ref(getWeekNumber(today));
const year = ref(today.getFullYear());
const groupMode = ref('customer');

onMounted(async () => {
    deliveries.value = await deliveryService.getAll()
    activities.value = await activityService.getAll()
});

const deliveryGroups = computed(() => {
    return deliveries.value.reduce(function (x, delivery) {
        if (!x.has(delivery.harvestWeekDay)) {
            x.set(delivery.harvestWeekDay, new Map());
        }

        const harvestDate = getDate(year.value, week.value, delivery.harvestWeekDay);
        if (!delivery.weeks.includes(getWeekNumber(harvestDate))) {
            return x;
        }

        for (const dp of delivery.deliveryProducts) {
            if (!x.has(delivery.harvestWeekDay)) {
                x.set(delivery.harvestWeekDay, new Map());
            }
            const weekDay = x.get(delivery.harvestWeekDay);
            if (undefined === weekDay) {
                continue;
            }

            const customerHash = delivery.customer?.fullName ?? '';
            switch (groupMode.value) {
                case 'customer':
                    if (!weekDay.has(customerHash)) {
                        weekDay.set(customerHash, new Map());

                    }
                    const customer = weekDay.get(customerHash);
                    if (undefined === customer) {
                        continue;
                    }

                    customer.set(
                        dp.product.name,
                        {
                            qty: (customer.get(dp.product.name)?.qty ?? 0) + dp.qty,
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
                    break;

                case 'product':
                    if (!weekDay.has(dp.product.name)) {
                        weekDay.set(dp.product.name, new Map());

                    }
                    const product = weekDay.get(dp.product.name);
                    if (undefined === product) {
                        continue;
                    }

                    product.set(
                        customerHash,
                        {
                            qty: (product.get(dp.product.name)?.qty ?? 0) + dp.qty,
                            done: activities.value
                                .filter(a =>
                                    a.step.product?.id === dp.product.id &&
                                    ['light', 'dark'].includes(a.step.name) &&
                                    a.year === year.value &&
                                    a.week === week.value
                                )
                                .reduce((i, dp) => i + dp.qty, 0),
                            delivery: delivery,
                            product: dp.product
                        },
                    );
                    break;
            }
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
    return Array.from(new Set(names));
});

</script>

<template>
    <div class="card">
        <input type="number" v-model="year" placeholder="year" />
        <input type="number" v-model="week" min="1" max="53" placeholder="week" />

        <div>
            <input type="radio" value="customer" v-model="groupMode" />
            <label for="customer">By customer</label>

            <input type="radio" value="product" v-model="groupMode" />
            <label for="product">By product</label>
        </div>
    </div>

    <div class="card">
        Week total: {{ weekTotal }}
        <div v-for="name in groupNames">
            {{ name }} total: {{ groupTotal(name) }}
        </div>
    </div>

    <Toast />

    <div class="card">
        <div class="grid">
            <div style="width:14.28%" v-for="weekDay of weekDays">
                <h5>{{ weekDay.label }}</h5>
                <b>Day total: {{ dayTotal(weekDay.value) }}</b>
                <div>
                    <Panel v-for="[groupName, products] in deliveryGroups.get(weekDay.value)" :header="groupName"
                        toggleable>
                        <template #header>
                            {{ groupName }} {{ groupWeekTotal(weekDay.value, groupName) }}
                        </template>
                        <p v-for="[name, dp] in products">
                            {{ name }}: {{ dp.qty }}
                            <ProgressBar :value="(dp.done / dp.qty) * 100">
                                {{ dp.done }} / {{ dp.qty }}
                            </ProgressBar>
                        </p>
                    </Panel>
                </div>
            </div>
        </div>
    </div>
</template>
