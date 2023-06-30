<script setup lang="ts">
import productService from '@/service/ProductService';
import { useDataView } from '../composables/dataView'
import Steps from '@/components/Steps.vue';
const {
    filters, 
    data, single, save, 
    openNew, editData, 
    dialog, hideDialog, 
    deleteDialog, confirmDelete, deleteData,
    isInvalid
} = useDataView(productService)

const types = [
    {label: 'Ground', value: 'ground'},
    {label: 'Seeds', value: 'seeds'},
    {label: 'Seeds box', value: 'seeds_box'},
    {label: 'Water box', value: 'water_box'},
    {label: 'Blackout box', value: 'blackout_box'},
    {label: 'Light box', value: 'light_box'},
    {label: 'Shipping box', value: 'shipping_box'},
];
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

                <DataTable :value="data" dataKey="id" :paginator="true" :rows="10"
                    :filters="filters"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    :rowsPerPageOptions="[5, 10, 25]"
                    currentPageReportTemplate="Showing {first} to {last} of {totalRecords} data" responsiveLayout="scroll">
                    <template #header>
                        <div class="flex flex-column md:flex-row md:justify-content-between md:align-items-center">
                            <h5 class="m-0">Manage Products</h5>
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
                    <Column field="name" header="Name" :sortable="true" headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Name</span>
                            {{ slotProps.data.name }}
                        </template>
                    </Column>
                    <Column field="type" header="Type" :sortable="true" headerStyle="width:14%; min-width:8rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Type</span>
                            {{ slotProps.data.type }}
                        </template>
                    </Column>
                    <Column field="decigrams" header="Decigrams" :sortable="true" headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Decigrams</span>
                            {{ slotProps.data.decigrams }}
                        </template>
                    </Column>
                    <Column field="days" header="Days" :sortable="true" headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Days</span>
                            {{ slotProps.data.days }}
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

                <Dialog v-model:visible="dialog" v-if="single" :style="{ width: '450px' }" header="Product Details" :modal="true"
                    class="p-fluid">

                    <div class="field">
                        <label for="name">Name</label>
                        <InputText id="name" v-model.trim="single.name" required="true" autofocus  :class="{ 'p-invalid': isInvalid('name') }"/>
                    </div>

                    <div class="field">
                        <label for="type" class="mb-3">Type</label>
                        <Dropdown id="type" v-model="single.type" :options="types" optionLabel="label"
                            optionValue="value" placeholder="Select a Type" :class="{ 'p-invalid': isInvalid('type') }">
                        </Dropdown>
                    </div>

                    <div class="field">
                        <label for="decigrams">Decigrams</label>
                        <InputNumber type="number" id="decigrams" v-model="single.decigrams" required autofocus showButtons :class="{ 'p-invalid': isInvalid('decigrams') }" />
                    </div>

                    <div class="field">
                        <label for="days">Days</label>
                        <InputNumber type="number" id="days" v-model="single.days" required autofocus showButtons :class="{ 'p-invalid': isInvalid('days') }" />
                    </div>

                    <div class="field">
                        <label for="price">Price</label>
                        <InputNumber type="number" id="price" v-model="single.price" autofocus showButtons :class="{ 'p-invalid': isInvalid('price') }" />
                    </div>

                    <Steps v-model="single.steps" />

                    <template #footer>
                        <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="hideDialog" />
                        <Button label="Save" icon="pi pi-check" class="p-button-text" @click="save" />
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
