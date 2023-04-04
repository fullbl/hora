<script setup lang="ts">
import type Order from '@/interfaces/order';
import { FilterMatchMode } from 'primevue/api';
import { ref, onMounted, onBeforeMount } from 'vue';
import orderService from '@/service/OrderService';
import productService from '@/service/ProductService';
import { useToast } from 'primevue/usetoast';
import InputNumber from 'primevue/inputnumber';
import type Product from '@/interfaces/product';

const toast = useToast();

const data = ref<Array<Order>>([]);
const dialog = ref(false);
const deleteDialog = ref(false);
const single = ref<Order>(orderService.getNewOrder());
const dt = ref(null);
const filters = ref({});
const submitted = ref(false);
const statuses = [
    { label: 'Draft', value: 'draft' },
    { label: 'Ordered', value: 'ordered' },
    { label: 'Arrived', value: 'arrived' },
    { label: 'Stored', value: 'stored' },
    { label: 'Canceled', value: 'canceled' },
];
const products = ref<Array<Product>>([])

onBeforeMount(() => {
    initFilters();
});
onMounted(async () => {
    data.value = await orderService.getOrders()
    products.value = await productService.getProducts()
});

const openNew = () => {
    single.value = orderService.getNewOrder()
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
    if (await orderService.save(single.value)) {
        if (single.value.id) {
            data.value.push(single.value);
        }
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Order Updated/Created', life: 3000 });
        single.value = orderService.getNewOrder();
        dialog.value = false;
    } else {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Error updating/creating order', life: 3000 });
    }
};

const editData = (data: Order) => {
    single.value = { ...data };
    dialog.value = true;
};

const confirmDelete = (data: Order) => {
    single.value = data;
    deleteDialog.value = true;
};

const deleteData = async () => {
    if (null === single.value) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No data Provided', life: 3000 });
        return
    }
    if (await orderService.save(single.value)) {
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Order Deleted', life: 3000 });
    }
    else {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Error deleting order', life: 3000 });
    }
    deleteDialog.value = false;
    single.value = orderService.getNewOrder();
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
                            <h5 class="m-0">Manage Orders</h5>
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
                    <Column field="product" header="Product" :sortable="true" headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Product</span>
                            {{ slotProps.data.product.name }}
                        </template>
                    </Column>
                    <Column field="status" header="Status" :sortable="true" headerStyle="width:14%; min-width:8rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Status</span>
                            {{ slotProps.data.status }}
                        </template>
                    </Column>
                    <Column field="quantity" header="Quantity" :sortable="true" headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Quantity</span>
                            {{ slotProps.data.quantity }}
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

                <Dialog v-model:visible="dialog" :style="{ width: '450px' }" header="Single Details" :modal="true"
                    class="p-fluid">

                    <div class="field">
                        <label for="product" class="mb-3">Product</label>
                        <Dropdown id="product" v-model="single.product.id" :options="products" optionLabel="name"
                            optionValue="id" placeholder="Select a Product">
                        </Dropdown>
                    </div>

                    <div class="field">
                        <label for="status" class="mb-3">Status</label>
                        <Dropdown id="status" v-model="single.status" :options="statuses" optionLabel="label"
                            optionValue="value" placeholder="Select a Status">
                        </Dropdown>
                    </div>

                    <div class="field">
                        <label for="vatNumber">Quantity</label>
                        <InputNumber type="number" id="vatNumber" v-model="single.quantity" required="true" autofocus
                            :class="{ 'p-invalid': submitted && !single.quantity }" />
                        <small class="p-invalid" v-if="submitted && !single.quantity">Quantity is required.</small>
                    </div>

                    <template #footer>
                        <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="hideDialog" />
                        <Button label="Save" icon="pi pi-check" class="p-button-text" @click="saveSingle" />
                    </template>
                </Dialog>

                <Dialog v-model:visible="deleteDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span v-if="single">Are you sure you want to delete this order?</span>
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
