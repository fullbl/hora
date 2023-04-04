<script setup lang="ts">
import type Product from '@/interfaces/product';
import { FilterMatchMode } from 'primevue/api';
import { ref, onMounted, onBeforeMount } from 'vue';
import productService from '@/service/ProductService';
import { useToast } from 'primevue/usetoast';
import MultiSelect from 'primevue/multiselect';
import InputNumber from 'primevue/inputnumber';

const toast = useToast();

const data = ref<Array<Product>>([]);
const dialog = ref(false);
const deleteDialog = ref(false);
const single = ref<Product>(productService.getNewProduct());
const dt = ref(null);
const filters = ref({});
const submitted = ref(false);

onBeforeMount(() => {
    initFilters();
});
onMounted(async () => {
    data.value = await productService.getProducts()
});

const openNew = () => {
    single.value = productService.getNewProduct();
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
    if (await productService.save(single.value)){
        if(single.value.id){
            data.value.push(single.value);
        }
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Product Updated/Created', life: 3000 });
        single.value = productService.getNewProduct();
        dialog.value = false;
    } else {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Error updating/creating product', life: 3000 });
    }
};

const editData = (data: Product) => {
    single.value = { ...data };
    dialog.value = true;
};

const confirmDelete = (data: Product) => {
    single.value = data;
    deleteDialog.value = true;
};

const deleteData = async () => {
    if (null === single.value) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No data Provided', life: 3000 });
        return
    }
    if (await productService.save(single.value)){
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Product Deleted', life: 3000 });
    }
    else{
        toast.add({ severity: 'error', summary: 'Error', detail: 'Error deleting product', life: 3000 });
    }
    deleteDialog.value = false;
    single.value = productService.getNewProduct();
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

                <DataTable ref="dt" :value="data" dataKey="id" :paginator="true" :rows="10"
                    :filters="filters"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    :rowsPerPageOptions="[5, 10, 25]"
                    currentPageReportTemplate="Showing {first} to {last} of {totalRecords} data" responsiveLayout="scroll">
                    <template #header>
                        <div class="flex flex-column md:flex-row md:justify-content-between md:align-items-center">
                            <h5 class="m-0">Manage Products</h5>
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
                    <Column field="grams" header="Grams" :sortable="true" headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">E-mail</span>
                            {{ slotProps.data.grams }}
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
                        <label for="name">Name</label>
                        <InputText id="name" v-model.trim="single.name" required="true" autofocus
                            :class="{ 'p-invalid': submitted && !single.name }" />
                        <small class="p-invalid" v-if="submitted && !single.name">Name is required.</small>
                    </div>

                    <div class="field">
                        <label for="type">Type</label>
                        <InputText id="type" v-model.trim="single.type" required="true" autofocus
                            :class="{ 'p-invalid': submitted && !single.type }" />
                        <small class="p-invalid" v-if="submitted && !single.type">Type is required.</small>
                    </div>

                    <div class="field">
                        <label for="vatNumber">Grams</label>
                        <InputNumber type="number" id="vatNumber" v-model="single.grams" required="true" autofocus
                            :class="{ 'p-invalid': submitted && !single.grams }" />
                        <small class="p-invalid" v-if="submitted && !single.grams">VAT Number is required.</small>
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
