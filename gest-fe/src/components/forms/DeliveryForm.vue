<script setup lang="ts">
import type InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import DateInput from '@/components/DateInput.vue';
import type { Delivery } from '@/interfaces/delivery';
import dayjs from 'dayjs';
import productService from '@/service/ProductService';
import type User from '@/interfaces/user';
import type { Sellable } from '@/interfaces/product';
import { ref, type PropType } from 'vue';
import type DeliveryProduct from '@/interfaces/deliveryProduct';
import Calendar from 'primevue/calendar';

const props = defineProps({
    single: {
        type: Object as PropType<Delivery>,
        required: true
    },
    isInvalid: {
        type: Function as PropType<(field: string) => boolean>,
        required: true
    },
    customers: {
        type: Array as PropType<Array<User>>,
        required: true
    },
    products: {
        type: Array as PropType<Array<Sellable>>,
        required: true
    }
});

const weekDays = dayjs.weekdays(true).map((w, i) => ({ label: w, value: i }));
const setDates = (dates: Date[], prop: 'harvestDates' | 'deliveryDates') => {
    if (0 === dates.length) {
        props.single[prop] = [];
        return;
    }
    const weekdayProp = prop === 'harvestDates' ? harvestWeekDay : deliveryWeekDay;
    const rightDates = dates.map((d) => {
        const date = dayjs(d);
        if (undefined === weekdayProp.value) {
            weekdayProp.value = date.weekday();
        }
        if (date.weekday() !== weekdayProp.value) {
            return date.weekday(weekdayProp.value).toDate();
        }

        return date.toDate();
    });

    props.single[prop] = rightDates.filter((date, i, self) => self.findIndex((d) => d.getTime() === date.getTime()) === i);
};
const harvestWeekDay = ref<number>();
const deliveryWeekDay = ref<number>();
const harvestDatesModel = defineModel('harvestDates', {
    get: () => props.single.harvestDates,
    set: (dates: Date[]) => setDates(dates, 'harvestDates')
});

const deliveryDatesModel = defineModel('deliveryDates', {
    get: () => props.single.deliveryDates,
    set: (dates: Date[]) => setDates(dates, 'deliveryDates')
});

const addProduct = () => {
    if (undefined !== props.single) {
        props.single.deliveryProducts.push({ product: productService.getNew(), qty: 1 });
    }
};
const removeProduct = (dp: DeliveryProduct) => {
    if (undefined !== props.single) {
        props.single.deliveryProducts = props.single.deliveryProducts.filter((_dp) => _dp !== dp);
    }
};
</script>

<template>
    <div class="formgrid grid">
        <div class="field col-3">
            <label for="customer">Customer</label>
            <Dropdown id="customer" v-model="single.customer" :options="customers" optionLabel="fullName" dataKey="id" placeholder="Select a Customer" showClear filter :class="{ 'p-invalid': isInvalid('customer') }"> </Dropdown>
        </div>

        <div class="field col-3">
            <label for="paymentMethod">Payment method</label>
            <Dropdown id="paymentMethod" v-model="single.paymentMethod" :options="['weekly', 'monthly']" :class="{ 'p-invalid': isInvalid('paymentMethod') }" />
        </div>

        <div class="field col-6">
            <label for="notes">Notes</label>
            <InputText id="notes" v-model="single.notes" :class="{ 'p-invalid': isInvalid('notes') }" />
        </div>

        <div class="field col-6">
            <label for="products">Products</label>

            <Button label="Add" icon="pi pi-plus" @click="addProduct" />
            <DataTable :value="single.deliveryProducts.sort((a, b) => a.product.name.localeCompare(b.product.name))">
                <Column field="product.name" header="Product">
                    <template #body="slotProps">
                        <Dropdown v-model="slotProps.data.product" optionLabel="name" dataKey="id" :options="products" />
                    </template>
                </Column>
                <Column field="product.type" header="Type" />
                <Column field="qty" header="Quantity">
                    <template #body="slotProps">
                        <InputNumber v-model="slotProps.data.qty" showButtons />
                    </template>
                </Column>
                <Column header="x">
                    <template #body="slotProps">
                        <Button icon="pi pi-trash" class="p-button-rounded p-button-warning mt-2" @click="removeProduct(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <div class="field col-6">
            <div v-if="single.id">
                <label for="harvestDate">Harvest Date</label>
                <DateInput id="harvestDate" v-model="single.harvestDate" dateFormat="dd/mm/yy" :class="{ 'p-invalid': isInvalid('harvestDate') }" />
            </div>
            <div v-else>
                <label for="harvestDates">Harvest Dates</label>
                <Dropdown id="type" v-model="harvestWeekDay" :options="weekDays" optionLabel="label" optionValue="value" placeholder="Select a WeekDay" :class="{ 'p-invalid': isInvalid('harvestWeekDay') }"> </Dropdown>
                <Calendar selectionMode="multiple" id="harvestDates" v-model="harvestDatesModel" dateFormat="dd/mm/yy" :class="{ 'p-invalid': isInvalid('harvestDates') }" />
            </div>

            <div v-if="single.id">
                <label for="deliveryDate">Delivery Date</label>
                <DateInput id="deliveryDate" v-model="single.deliveryDate" dateFormat="dd/mm/yy" :class="{ 'p-invalid': isInvalid('deliveryDate') }" />
            </div>
            <div v-else>
                <label for="deliveryDates">Delivery Dates</label>
                <Dropdown id="type" v-model="deliveryWeekDay" :options="weekDays" optionLabel="label" optionValue="value" placeholder="Select a WeekDay" :class="{ 'p-invalid': isInvalid('deliveryWeekDay') }"> </Dropdown>
                <Calendar selectionMode="multiple" id="deliveryDates" v-model="deliveryDatesModel" dateFormat="dd/mm/yy" :class="{ 'p-invalid': isInvalid('deliveryDates') }" />
            </div>
        </div>
    </div>
</template>
