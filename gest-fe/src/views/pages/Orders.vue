<script setup lang="ts">
import orderService from '@/service/OrderService';
import productService from '@/service/ProductService';
import { useDataView } from '../composables/dataView'
import type Product from '@/interfaces/product';
import { onMounted, ref } from 'vue';

const {
    filters, 
    data, single, save, 
    openNew, editData, 
    dialog, hideDialog, 
    deleteDialog, confirmDelete, deleteData,
    isInvalid
} = useDataView(orderService)

const statuses = [
    { label: 'Draft', value: 'draft' },
    { label: 'Ordered', value: 'ordered' },
    { label: 'Arrived', value: 'arrived' },
    { label: 'Stored', value: 'stored' },
    { label: 'Canceled', value: 'canceled' },
];
const products = ref<Array<Product>>([])

onMounted(async () => {
    products.value = await productService.getAll()
});

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

                <DataTable :value="data" dataKey="id" :paginator="true" :rows="10" :filters="filters"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    :rowsPerPageOptions="[5, 10, 25]"
                    currentPageReportTemplate="Showing {first} to {last} of {totalRecords} data" responsiveLayout="scroll">
                    <template #header>
                        <div class="flex flex-column md:flex-row md:justify-content-between md:align-items-center">
                            <h5 class="m-0">Manage Orders</h5>
                            <span class="block mt-2 md:mt-0 p-input-icon-left">
                                <i class="pi pi-search" />
                                <InputText v-model="filters.global.value" placeholder="Search..." />
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
                    class="p-fluid" v-if="'undefined' !== typeof single">

                    <div class="field">
                        <label for="product" class="mb-3">Product</label>
                        <Dropdown id="product" v-model="single.product.id" :options="products" optionLabel="name"
                            optionValue="id" placeholder="Select a Product" :class="{ 'p-invalid': isInvalid('name') }">
                        </Dropdown>
                    </div>

                    <div class="field">
                        <label for="status" class="mb-3">Status</label>
                        <Dropdown id="status" v-model="single.status" :options="statuses" optionLabel="label"
                            optionValue="value" placeholder="Select a Status" :class="{ 'p-invalid': isInvalid('status') }">
                        </Dropdown>
                    </div>

                    <div class="field">
                        <label for="vatNumber">Quantity</label>
                        <InputNumber type="number" id="vatNumber" v-model="single.quantity" required="true" autofocus :class="{ 'p-invalid': isInvalid('quantity') }" />
                    </div>

                    <template #footer>
                        <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="hideDialog" />
                        <Button label="Save" icon="pi pi-check" class="p-button-text" @click="save" />
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
