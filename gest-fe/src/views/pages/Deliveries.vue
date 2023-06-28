<script setup lang="ts">
import deliveryService from '@/service/DeliveryService';
import { useDataView } from '../composables/dataView'
import type User from '@/interfaces/user';
import type DeliveryProduct from '@/interfaces/deliveryProduct';
import { onMounted, ref } from 'vue';
import userService from '@/service/UserService';
import productService from '@/service/ProductService';
import { useDates } from '../composables/dates'
import { computed } from '@vue/reactivity';
import type { TreeNode } from 'primevue/tree';
import type Product from '@/interfaces/product';
import type InputText from 'primevue/inputtext';

const {
    filters,
    data, single, save,
    openNew, editData,
    dialog, hideDialog,
    deleteDialog, confirmDelete, deleteData
} = useDataView(deliveryService)
const { getWeekNumber, weeks, weekDays } = useDates()
const customers = ref<Array<User>>([])
const products = ref<Array<Product>>([])

onMounted(async () => {
    customers.value = (await userService.getAll())
        .filter(u => u.roles.includes('ROLE_CUSTOMER'))
    products.value = (await productService.getAll())
});

const selectedWeeks = computed({
    get(): TreeNode {
        let _weeks = {} as TreeNode;
        if ('undefined' === typeof single.value || !single.value.hasOwnProperty('weeks')) {
            return _weeks;
        }
        for (const month of weeks) {
            let partialChecked = false
            let checked = true
            for (const week of month.children) {
                if (single.value.weeks.includes(parseInt(week.key))) {
                    partialChecked = true
                }
                else {
                    checked = false
                }
                _weeks[week.key] = {
                    checked: single.value.weeks.includes(parseInt(week.key)),
                    partialChecked: false
                }
            }

            if (true === checked) {
                partialChecked = false
            }

            _weeks[month.key] = {
                checked,
                partialChecked
            }
        }

        return _weeks;
    },
    set(weeksTree: TreeNode) {
        if ('undefined' === typeof single.value) {
            return;
        }
        let _weeks = [];
        for (let i = 1; i <= 52; i++) {
            if (weeksTree.hasOwnProperty(i) && weeksTree[i].checked) {
                _weeks.push(i);
            }
        }
        single.value.weeks = _weeks
    }

})

const selectWeeks = function (type: string) {
    if ('undefined' === typeof single.value) {
        return;
    }
    switch (type) {
        case 'even':
            single.value.weeks = [1, 3, 5, 7, 9, 11, 13, 15, 17, 19, 21, 23, 25, 27, 29, 31, 33, 35, 37, 39, 41, 43, 45, 47, 49, 51, 53]
            break
        case 'odd':
            single.value.weeks = [2, 4, 6, 8, 10, 12, 14, 16, 18, 20, 22, 24, 26, 28, 30, 32, 34, 36, 38, 40, 42, 44, 46, 48, 50, 52]
            break
        case 'all':
            single.value.weeks = (Array.from(Array(53).keys())).map(x => ++x)
            break
        case 'suspend':
            single.value.weeks = single.value.weeks.filter((x => x <= getWeekNumber(new Date())))
            break
        case 'next':
            single.value.weeks = (Array.from(Array(53).keys())).map(x => ++x).filter((x => x >= getWeekNumber(new Date())))
    }
}
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
                            <h5 class="m-0">Manage Deliveries</h5>
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
                    <Column field="harvestWeekDay" header="Harvest" :sortable="true" headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Harvest</span>
                            {{ weekDays.find(d => d.value === slotProps.data.harvestWeekDay)?.label }}
                        </template>
                    </Column>
                    <Column field="deliveryWeekDay" header="Delivery" :sortable="true" headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Delivery</span>
                            {{ weekDays.find(d => d.value === slotProps.data.deliveryWeekDay)?.label }}
                        </template>
                    </Column>
                    <Column field="customer.fullName" header="Customer" :sortable="true" headerStyle="width:14%; min-width:8rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Customer</span>
                            {{ slotProps.data.customer.fullName }}
                        </template>
                    </Column>
                    <Column field="deliveryProducts" header="Products" :sortable="true"
                        headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Products</span>
                            {{ slotProps.data.deliveryProducts.map((d: DeliveryProduct) => d.product.name + ': ' +
                                d.qty).join(', ') }}
                        </template>
                    </Column>
                    <Column field="notes" header="Notes" :sortable="true"
                        headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Notes</span>
                            {{ slotProps.data.notes ?? '' }}
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
                    class="p-fluid" v-if="'undefined' !== typeof single">
                
                    <div class="field">
                        <label for="name">Harvest WeekDay</label>
                        <Dropdown id="type" v-model="single.harvestWeekDay" :options="weekDays" optionLabel="label"
                            optionValue="value" placeholder="Select a WeekDay">
                        </Dropdown>
                    </div>
                    <div class="field">
                        <label for="name">Delivery WeekDay</label>
                        <Dropdown id="type" v-model="single.deliveryWeekDay" :options="weekDays" optionLabel="label"
                            optionValue="value" placeholder="Select a WeekDay">
                        </Dropdown>
                    </div>
                    <div class="field">
                        <label for="name">Weeks</label>
                        <div class="mb-3 p-buttonset">
                            <Button label="even" @click="selectWeeks('even')" />
                            <Button label="odd" @click="selectWeeks('odd')" />
                            <Button label="all" @click="selectWeeks('all')" />
                            <Button label="suspend" @click="selectWeeks('suspend')" />
                            <Button label="next" @click="selectWeeks('next')" />
                        </div>
                        <TreeSelect v-model="selectedWeeks" :options="weeks" selectionMode="checkbox"
                            placeholder="Select Weeks" class="md:w-20rem w-full" />
                    </div>

                    <div class="field">
                        <label for="customer" class="mb-3">Customer</label>
                        <Dropdown id="customer" v-model="single.customer.id" :options="customers" optionLabel="fullName"
                            optionValue="id" placeholder="Select a Customer" :filter="true">
                        </Dropdown>
                    </div>

                    <div class="field">
                        <label for="products" class="mb-3">Products</label>

                        <Button label="Add" icon="pi pi-plus"
                            @click="single.deliveryProducts.push({ product: productService.getNew(), qty: 0 })" />
                        <DataTable :value="single.deliveryProducts">
                            <Column field="product.name" header="Product">
                                <template #body="slotProps">
                                    <Dropdown v-model="slotProps.data.product" optionLabel="name" :options="products" />
                                </template>
                            </Column>
                            <Column field="product.type" header="Type" />
                            <Column field="qty" header="Quantity">
                                <template #body="slotProps">
                                    <InputNumber v-model="slotProps.data.qty" />
                                </template>
                            </Column>
                        </DataTable>
                    </div>

                    <div class="field">
                        <label for="notes" class="mb-3">Notes</label>
                        <InputText id="notes" v-model="single.notes" />
                    </div>

                    <template #footer>
                        <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="hideDialog" />
                        <Button label="Save" icon="pi pi-check" class="p-button-text" @click="save" />
                    </template>
                </Dialog>

                <Dialog v-model:visible="deleteDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span v-if="single">Are you sure you want to delete this?</span>
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
