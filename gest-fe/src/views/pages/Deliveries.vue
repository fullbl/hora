<script setup lang="ts">
import deliveryService from '@/service/DeliveryService';
import { useDataView } from '../composables/dataView';
import type User from '@/interfaces/user';
import type DeliveryProduct from '@/interfaces/deliveryProduct';
import { onMounted, ref } from 'vue';
import userService from '@/service/UserService';
import productService from '@/service/ProductService';
import { computed } from '@vue/reactivity';
import type Sellable from '@/interfaces/product';
import type InputText from 'primevue/inputtext';
import Button from 'primevue/button';
import type { Delivery } from '@/interfaces/delivery';
import dayjs from 'dayjs';
import DeliveryForm from '@/components/forms/DeliveryForm.vue';
import type Zone from '@/interfaces/zone';

const { filters, data: _data, single, save, openNew: _openNew, editData: _editData, cloneData, dialog, hideDialog, showDialog, deleteDialog, confirmDelete, deleteData, isInvalid } = useDataView(deliveryService);

const customers = ref<Array<User>>([]);
const products = ref<Array<Sellable>>([]);
const logEntity = ref(null);
const nextOnly = ref(false);
const activeOnly = ref(false);
const today = dayjs();
const expandedRowGroups = ref();

const data = computed(() => {
    const rows = _data.value;

    if (activeOnly.value) {
        return rows?.filter((d) => d.deliveryDate > today);
    }

    return rows;
});

const openNew = () => {
    nextOnly.value = false;
    _openNew();
};
const editData = (item: Delivery) => {
    nextOnly.value = false;
    _editData(item);
};

onMounted(async () => {
    customers.value = (await userService.getAll()).filter((u) => u.roles.includes('ROLE_CUSTOMER'));
    products.value = await productService.getSellable();
});

const getCustomerNumber = (item: Delivery) => (data.value?.filter((d) => d.customer?.id === item.customer?.id).findIndex((d) => d.id === item.id) ?? 0) + 1;

const preSave = function () {
    if (!single.value?.id && (!single.value?.deliveryDates || !single.value.harvestDates)) {
        alert('Please select delivery dates and harvest dates');
    }
    save();
};

const deleteReason = ref('');
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
                    v-model:expandedRowGroups="expandedRowGroups"
                    expandableRowGroups
                    rowGroupMode="subheader"
                    groupRowsBy="customer.fullName"
                    sortMode="single"
                    sortField="customer.fullName"
                    :paginator="false"
                    :rows="10"
                    :filters="filters"
                    :rowClass="(data) => (data.deletedAt ? 'bg-red-900' : '')"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    :rowsPerPageOptions="[5, 10, 25]"
                    currentPageReportTemplate="Showing {first} to {last} of {totalRecords} data"
                    responsiveLayout="scroll"
                >
                    <template #header>
                        <div class="flex flex-column md:flex-row md:justify-content-between md:align-items-center">
                            <h5 class="m-0">Manage Deliveries</h5>
                            <Button @click="activeOnly = !activeOnly" :severity="activeOnly ? 'primary' : 'secondary'">active only</Button>
                            <span class="block mt-2 md:mt-0 p-input-icon-left">
                                <i class="pi pi-search" />
                                <InputText v-model="filters.global.value" placeholder="Search..." />
                            </span>
                        </div>
                    </template>

                    <template #groupheader="slotProps">
                        <span class="vertical-align-middle ml-2 font-bold line-height-3">{{ slotProps.data.customer?.fullName ?? 'EXTRA' }}</span>
                    </template>

                    <Column field="id" header="Id" sortable>
                        <template #body="slotProps">
                            <span class="p-column-title">Id</span>
                            <span class="font-bold text-blue-500 cursor-pointer" v-if="slotProps.data.notes" v-tooltip.left="slotProps.data.notes">{{ slotProps.data.id }}</span>
                            <span v-else>{{ slotProps.data.id }}</span>
                        </template>
                    </Column>
                    <Column header="#">
                        <template #body="slotProps">
                            {{ getCustomerNumber(slotProps.data) }}
                        </template>
                    </Column>
                    <Column field="customer.fullName" header="Customer" sortable headerStyle="min-width:8rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Customer</span>
                            {{ slotProps.data.customer?.fullName }}
                        </template>
                    </Column>
                    <Column field="harvestDate" header="Harvest" sortable headerStyle="min-width:8rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Harvest</span>
                            {{ slotProps.data.harvestDate.format('dddd DD/MM/YYYY') }}
                        </template>
                    </Column>
                    <Column field="deliveryDate" header="Delivery" sortable headerStyle="min-width:8rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Delivery</span>
                            {{ slotProps.data.deliveryDate.format('dddd DD/MM/YYYY') }}
                        </template>
                    </Column>
                    <Column field="deliveryProducts" header="Products" sortable headerStyle="min-width:10rem;">
                        <template #body="slotProps">
                            <span v-if="slotProps.data.deletedAt">Deleted on {{ slotProps.data.deletedAt.format('DD/MM/YYYY HH:mm:ss') }} because {{ slotProps.data.deletedReason }}</span>
                            <span class="p-column-title" v-else>Products</span>
                            {{
                                slotProps.data.deliveryProducts
                                    .map((d: DeliveryProduct) => d.product.name + ': ' + d.qty)
                                    .sort()
                                    .join(', ')
                            }}
                        </template>
                    </Column>
                    <Column field="customer.zones" header="Zones" sortable>
                        <template #body="slotProps">
                            <span class="p-column-title">Zones</span>
                            {{
                                slotProps.data.customer.zones
                                    .filter((zone: Zone) => null === zone.parent)
                                    .map((zone: Zone) => zone.name)
                                    .join(',')
                            }}
                            <br />
                            {{
                                slotProps.data.customer.zones
                                    .filter((zone: Zone) => null !== zone.parent)
                                    .map((zone: Zone) => zone.name)
                                    .join(',')
                            }}
                        </template>
                    </Column>
                    <Column field="price" header="Price">
                        <template #body="slotProps">
                            <span class="p-column-title">Price</span>
                            {{ slotProps.data.deliveryProducts.reduce((i: number, p: DeliveryProduct) => i + ((p.product.price ?? 0) / 100) * p.qty, 0) - (slotProps.data.customer?.discount ?? 0, 0) }}€
                        </template>
                    </Column>
                    <Column header="Quantity">
                        <template #body="slotProps">
                            <span class="p-column-title">Quantity</span>
                            {{ slotProps.data.deliveryProducts.reduce((i: number, p: DeliveryProduct) => i + p.qty, 0) }}
                        </template>
                    </Column>
                    <Column headerStyle="min-width:14rem;">
                        <template #body="slotProps">
                            <Button icon="pi pi-pencil" class="p-button-rounded p-button-success mr-2" @click="editData(slotProps.data)" />
                            <Button icon="pi pi-copy" class="p-button-rounded p-button-info mr-2" @click="cloneData(slotProps.data)" />
                            <Button icon="pi pi-trash" class="p-button-rounded p-button-warning mr-2" @click="confirmDelete(slotProps.data)" />
                            <Button icon="pi pi-book" class="p-button-rounded p-button-primary mr-2" @click="logEntity = slotProps.data.id" />
                        </template>
                    </Column>
                </DataTable>

                <Dialog v-model:visible="showDialog" :style="{ width: '60rem' }" :header="dialog" :modal="true" class="p-fluid" v-if="'undefined' !== typeof single">
                    <DeliveryForm :single="single" :is-invalid="isInvalid" :customers="customers" :products="products" />

                    <template #footer>
                        <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="hideDialog" />
                        <Button label="Save" icon="pi pi-check" class="p-button-text" @click="preSave" />
                    </template>
                </Dialog>

                <Dialog v-model:visible="deleteDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span v-if="single">Are you sure you want to delete this?</span>
                        <InputText v-model="deleteReason" placeholder="Reason" />
                    </div>
                    <template #footer>
                        <Button label="No" icon="pi pi-times" class="p-button-text" @click="deleteDialog = false" />
                        <Button label="Yes" icon="pi pi-check" class="p-button-text" @click="() => deleteData(deleteReason)" />
                    </template>
                </Dialog>
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss"></style>
