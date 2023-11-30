<script setup lang="ts">
import type InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import DateInput from '@/components/DateInput.vue';
import type { Delivery } from '@/interfaces/delivery';
import dayjs from 'dayjs';
import productService from '@/service/ProductService';
import type User from '@/interfaces/user';
import type { Sellable } from '@/interfaces/product';
import type { PropType } from 'vue';
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
            <label for="name">Harvest WeekDay</label>
            <Dropdown id="type" v-model="single.harvestWeekDay" :options="weekDays" optionLabel="label" optionValue="value" placeholder="Select a WeekDay" :class="{ 'p-invalid': isInvalid('harvestWeekDay') }"> </Dropdown>
        </div>
        <div class="field col-3">
            <label for="name">Delivery WeekDay</label>
            <Dropdown id="type" v-model="single.deliveryWeekDay" :options="weekDays" optionLabel="label" optionValue="value" placeholder="Select a WeekDay" :class="{ 'p-invalid': isInvalid('deliveryWeekDay') }"> </Dropdown>
        </div>

        <div class="field col-3">
            <label for="paymentMethod">Payment method</label>
            <Dropdown id="paymentMethod" v-model="single.paymentMethod" :options="['weekly', 'monthly']" :class="{ 'p-invalid': isInvalid('paymentMethod') }" />
        </div>

        <div class="field col-12">
            <label for="notes">Notes</label>
            <InputText id="notes" v-model="single.notes" :class="{ 'p-invalid': isInvalid('notes') }" />
        </div>

        <div class="field col-6">
            <label for="products">Products</label>

            <Button label="Add" icon="pi pi-plus" @click="addProduct" />
            <DataTable :value="single.deliveryProducts">
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

        <div class="field col-6" v-if="single.id">
            <label for="harvestDate">Harvest Date</label>
            <DateInput id="harvestDate" v-model="single.harvestDate" dateFormat="dd/mm/yy" :class="{ 'p-invalid': isInvalid('harvestDate') }" />

            <label for="deliveryDate">Delivery Date</label>
            <DateInput id="deliveryDate" v-model="single.deliveryDate" dateFormat="dd/mm/yy" :class="{ 'p-invalid': isInvalid('deliveryDate') }" />
        </div>
        <div class="field col-6" v-else>
            <label for="harvestDate">Harvest Dates</label>
            <Calendar selectionMode="multiple" id="harvestDates" v-model="single.harvestDates" dateFormat="dd/mm/yy" :class="{ 'p-invalid': isInvalid('harvestDate') }" />
            <label for="deliveryDate">Delivery Dates</label>
            <Calendar selectionMode="multiple" id="deliveryDates" v-model="single.deliveryDates" dateFormat="dd/mm/yy" :class="{ 'p-invalid': isInvalid('deliveryDate') }" />
        </div>
    </div>
</template>
