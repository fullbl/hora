<script setup lang="ts">
import productService from '@/service/ProductService';
import { useDataView } from '../composables/dataView';
import {  ref } from 'vue';
import Logger from '@/components/Logger.vue';
import Extra from './products/Extra.vue';
import Seed from './products/Seed.vue';
import type {Step} from '@/interfaces/step';

const { filters, data, single, save, openNew, editData, 
    dialog, hideDialog, showDialog, deleteDialog, 
    confirmDelete, deleteData, isInvalid } = useDataView(productService);

const logEntity = ref(null);

const types = [
    { label: 'Ground', value: 'ground' },
    { label: 'Seeds', value: 'seeds' },
    { label: 'Seeds box', value: 'seeds_box' },
    { label: 'Water box', value: 'water_box' },
    { label: 'Blackout box', value: 'blackout_box' },
    { label: 'Light box', value: 'light_box' },
    { label: 'Shipping box', value: 'shipping_box' },
    { label: 'Extra', value: 'extra' }
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

                <DataTable
                    :value="data"
                    dataKey="id"
                    :paginator="true"
                    :rows="10"
                    :filters="filters"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    :rowsPerPageOptions="[5, 10, 25]"
                    currentPageReportTemplate="Showing {first} to {last} of {totalRecords} data"
                    responsiveLayout="scroll"
                >
                    <template #header>
                        <div class="flex flex-column md:flex-row md:justify-content-between md:align-items-center">
                            <h5 class="m-0">Manage Products</h5>
                            <span class="block mt-2 md:mt-0 p-input-icon-left">
                                <i class="pi pi-search" />
                                <InputText v-model="filters.global.value" placeholder="Search..." />
                            </span>
                        </div>
                    </template>

                    <Column field="id" header="Id" sortable>
                        <template #body="slotProps">
                            <span class="p-column-title">Id</span>
                            {{ slotProps.data.id }}
                        </template>
                    </Column>
                    <Column field="name" header="Name" sortable>
                        <template #body="slotProps">
                            <span class="p-column-title">Name</span>
                            {{ slotProps.data.name }}
                        </template>
                    </Column>
                    <Column field="type" header="Type" sortable>
                        <template #body="slotProps">
                            <span class="p-column-title">Type</span>
                            {{ slotProps.data.type }}
                        </template>
                    </Column>
                    <Column field="weight" header="Weight" sortable>
                        <template #body="slotProps">
                            <span class="p-column-title">Weight</span>
                            {{ slotProps.data.weight ? 'Yes' : 'No' }}
                        </template>
                    </Column>
                    <Column field="decigrams" header="Decigrams" sortable>
                        <template #body="slotProps">
                            <span class="p-column-title">Decigrams</span>
                            {{ slotProps.data.decigrams }}
                        </template>
                    </Column>
                    <Column field="days" header="Days" sortable>
                        <template #body="slotProps">
                            <span class="p-column-title">Days</span>
                            {{ slotProps.data.days }}
                        </template>
                    </Column>
                    <Column field="price" header="Price" sortable>
                        <template #body="slotProps">
                            <span class="p-column-title">Price</span>
                            {{ (slotProps.data.price ?? 0) / 100 }} €
                        </template>
                    </Column>
                    <Column header="Soaking hours">
                        <template #body="slotProps">
                            <span class="p-column-title">Soaking hours</span>
                            {{ Math.round(slotProps.data.steps.filter((s: Step) => s.name === 'soaking').reduce((x: number, s: Step) => x + s.minutes, 0) / 60) }}
                        </template>
                    </Column>
                    <Column header="Light days">
                        <template #body="slotProps">
                            <span class="p-column-title">Light days</span>
                            {{ Math.round(slotProps.data.steps.filter((s: Step) => s.name === 'light').reduce((x: number, s: Step) => x + s.minutes, 0) / 60 / 24) }}
                        </template>
                    </Column>
                    <Column headerStyle="min-width:12rem;">
                        <template #body="slotProps">
                            <Button icon="pi pi-pencil" class="p-button-rounded p-button-success mr-2" @click="editData(slotProps.data)" />
                            <Button icon="pi pi-trash" class="p-button-rounded p-button-warning mt-2" @click="confirmDelete(slotProps.data)" />
                            <Button icon="pi pi-book" class="p-button-rounded p-button-primary ml-2" @click="logEntity = slotProps.data.id" />
                        </template>
                    </Column>
                </DataTable>

                <Dialog v-model:visible="showDialog" v-if="single" style="width: 40rem" :header="dialog" :modal="true" class="p-fluid">
                    <div class="formgrid grid">
                        <div class="field col-6">
                            <label for="name">Name</label>
                            <InputText id="name" v-model.trim="single.name" required="true" autofocus :class="{ 'p-invalid': isInvalid('name') }" />
                        </div>
    
                        <div class="field col-6">
                            <label for="type">Type</label>
                            <Dropdown id="type" v-model="single.type" :options="types" 
                                optionLabel="label" optionValue="value" 
                                placeholder="Select a Type" :class="{ 'p-invalid': isInvalid('type') }" />
                        </div>
                    </div>

                    <Seed v-if="'seeds' === single.type" :seed="single" :isInvalid="isInvalid" />
                    <Extra v-if="'extra' === single.type" :extra="single" :isInvalid="isInvalid" />
                    

                    <template #footer>
                        <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="hideDialog" />
                        <Button label="Save" icon="pi pi-check" class="p-button-text" @click="save" />
                    </template>
                </Dialog>

                <Dialog v-model:visible="deleteDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span v-if="single"
                            >Are you sure you want to delete <b>{{ single.name }}</b
                            >?</span
                        >
                    </div>
                    <template #footer>
                        <Button label="No" icon="pi pi-times" class="p-button-text" @click="deleteDialog = false" />
                        <Button label="Yes" icon="pi pi-check" class="p-button-text" @click="deleteData" />
                    </template>
                </Dialog>

                <Logger entity-name="App\Entity\Product" :entity-id="logEntity" @close="logEntity = null" />
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss"></style>
