<script setup lang="ts">
import type Delivery from '@/interfaces/delivery';
import { FilterMatchMode } from 'primevue/api';
import { ref, onMounted, onBeforeMount } from 'vue';
import deliveryService from '@/service/DeliveryService';
import userService from '@/service/UserService';
import productService from '@/service/ProductService';
import { useToast } from 'primevue/usetoast';
import InputNumber from 'primevue/inputnumber';
import type DeliveryProduct from '@/interfaces/deliveryProduct';
import type Product from '@/interfaces/product';
import type User from '@/interfaces/user';

const toast = useToast();

const data = ref<Array<Delivery>>([]);

const weekDays = [
    { label: 'Monday', value: 1 },
    { label: 'Tuesday', value: 2 },
    { label: 'Wednesday', value: 3 },
    { label: 'Thursday', value: 4 },
    { label: 'Friday', value: 5 },
    { label: 'Saturday', value: 6 },
    { label: 'Sunday', value: 7 },
];

const dialog = ref(false);
const deleteDialog = ref(false);
const single = ref<Delivery>(deliveryService.getNewDelivery());
const dt = ref(null);
const filters = ref({});
const submitted = ref(false);
const customers = ref<Array<User>>([])
const products = ref<Array<DeliveryProduct>>([])

onBeforeMount(() => {
    initFilters();
});
onMounted(async () => {
    data.value = await deliveryService.getDeliverys()
    customers.value = (await userService.getUsers())
        .filter(u => u.roles.includes('ROLE_CUSTOMER'))
    products.value = (await productService.getProducts())
        .map(p => ({ product: p, qty: 0 }))

});

const openNew = () => {
    single.value = deliveryService.getNewDelivery();
    submitted.value = false;
    dialog.value = true;
};

const hideDialog = () => {
    dialog.value = false;
    submitted.value = false;
};

const saveSingle = async () => {
    if (null === single.value) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No data Provided', life: 3000 });
        return
    }
    submitted.value = true;
    if (await deliveryService.save(single.value)) {
        if (single.value.id) {
            data.value.push(single.value);
        }
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Delivery Updated/Created', life: 3000 });
        single.value = deliveryService.getNewDelivery();
        dialog.value = false;
    } else {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Error updating/creating delivery', life: 3000 });
    }
};

const editData = (data: Delivery) => {
    single.value = { ...data };
    dialog.value = true;
};

const confirmDelete = (data: Delivery) => {
    single.value = data;
    deleteDialog.value = true;
};

const deleteData = async () => {
    if (null === single.value) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No data Provided', life: 3000 });
        return
    }
    if (await deliveryService.save(single.value)) {
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Delivery Deleted', life: 3000 });
    }
    else {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Error deleting delivery', life: 3000 });
    }
    deleteDialog.value = false;
    single.value = deliveryService.getNewDelivery();
};

const initFilters = () => {
    filters.value = {
        global: { value: null, matchMode: FilterMatchMode.CONTAINS }
    };
};

</script>

<template>
    <div class="grid">
        <div class="col-12">
            <div class="card">
                <Toast />
                <Toolbar class="mb-4">
                    <template v-slot:start>
                        <div class="my-2">
                            <Button label="New" icon="pi pi-plus" class="p-button-success mr-2" @click="openNew" />
                        </div>
                    </template>
                </Toolbar>

                <DataTable ref="dt" :value="data" dataKey="id" :paginator="true" :rows="10" :filters="filters"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    :rowsPerPageOptions="[5, 10, 25]"
                    currentPageReportTemplate="Showing {first} to {last} of {totalRecords} data" responsiveLayout="scroll">
                    <template #header>
                        <div class="flex flex-column md:flex-row md:justify-content-between md:align-items-center">
                            <h5 class="m-0">Manage Deliveries</h5>
                            <span class="block mt-2 md:mt-0 p-input-icon-left">
                                <i class="pi pi-search" />
                                <InputText v-model="filters['global'].value" placeholder="Search..." />
                            </span>
                        </div>
                    </template>

                    <Column field="id" header="Id" :sortable="true" headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Id</span>
                            {{ slotProps.data.id }}
                        </template>
                    </Column>
                    <Column field="weekDay" header="WeekDay" :sortable="true" headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">WeekDay</span>
                            {{ weekDays.find(d => d.value === slotProps.data.weekDay)?.label }}
                        </template>
                    </Column>
                    <Column field="customer" header="Customer" :sortable="true" headerStyle="width:14%; min-width:8rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Customer</span>
                            {{ slotProps.data.customer.fullName }}
                        </template>
                    </Column>
                    <Column field="deliveryProducts" header="Products" :sortable="true"
                        headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Products</span>
                            {{ slotProps.data.deliveryProducts.map((d: DeliveryProduct) => d.product.name + ': ' + d.qty).join(', ') }}
                        </template>
                    </Column>
                    <Column headerStyle="min-width:10rem;">
                        <template #body="slotProps">
                            <Button icon="pi pi-pencil" class="p-button-rounded p-button-success mr-2"
                                @click="editData(slotProps.data)" />
                            <Button icon="pi pi-trash" class="p-button-rounded p-button-warning mt-2"
                                @click="confirmDelete(slotProps.data)" />
                        </template>
                    </Column>
                </DataTable>

                <Dialog v-model:visible="dialog" :style="{ width: '450px' }" header="Delivery Details" :modal="true"
                    class="p-fluid">

                    <div class="field">
                        <label for="name">WeekDay</label>
                        <Dropdown id="type" v-model="single.weekDay" :options="weekDays" optionLabel="label"
                            optionValue="value" placeholder="Select a WeekDay">
                        </Dropdown>
                    </div>

                    <div class="field">
                        <label for="customer" class="mb-3">Customer</label>
                        <Dropdown id="customer" v-model="single.customer" :options="customers" optionLabel="fullName"
                            placeholder="Select a Customer">
                        </Dropdown>
                    </div>

                    <div class="field">
                        <label for="products" class="mb-3">Products</label>
                        <MultiSelect id="products" v-model="single.deliveryProducts" :options="products"
                            optionLabel="product.name" placeholder="Select some Products">
                        </MultiSelect>

                        <DataTable :value="single.deliveryProducts">
                            <Column field="product.name" header="Name"></Column>
                            <Column field="product.type" header="Type"></Column>
                            <Column field="qty" header="Quantity">
                                <template #body="slotProps">
                                    <span class="p-column-title">Quantity</span>
                                    <InputNumber v-model="slotProps.data.qty" />
                                </template>
                            </Column>
                        </DataTable>
                    </div>

                    <template #footer>
                        <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="hideDialog" />
                        <Button label="Save" icon="pi pi-check" class="p-button-text" @click="saveSingle" />
                    </template>
                </Dialog>

                <Dialog v-model:visible="deleteDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span v-if="single">Are you sure you want to delete <b>{{ single.name }}</b>?</span>
                    </div>
                    <template #footer>
                        <Button label="No" icon="pi pi-times" class="p-button-text" @click="deleteDialog = false" />
                        <Button label="Yes" icon="pi pi-check" class="p-button-text" @click="deleteData" />
                    </template>
                </Dialog>

            </div>
        </div>
    </div>
</template>

<style scoped lang="scss"></style>
