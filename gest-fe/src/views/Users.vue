<script setup lang="ts">
import type User from '@/interfaces/user';
import { FilterMatchMode } from 'primevue/api';
import { ref, onMounted, onBeforeMount } from 'vue';
import userService from '@/service/UserService';
import { useToast } from 'primevue/usetoast';
import MultiSelect from 'primevue/multiselect';

const toast = useToast();

const data = ref<Array<User>>([]);
const dialog = ref(false);
const deleteDialog = ref(false);
const single = ref<User>(userService.getNewUser());
const dt = ref(null);
const filters = ref({});
const submitted = ref(false);
const roles = [
    { label: 'Admin', value: 'ROLE_ADMIN' },
    { label: 'Operator', value: 'ROLE_OPERATOR' },
    { label: 'Customer', value: 'ROLE_CUSTOMER' }
];
const statuses = [
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' },
];


onBeforeMount(() => {
    initFilters();
});
onMounted(async () => {
    data.value = await userService.getUsers()
});

const openNew = () => {
    single.value = userService.getNewUser();
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
    if (await userService.save(single.value)){
        if(single.value.id){
            data.value.push(single.value);
        }
        toast.add({ severity: 'success', summary: 'Successful', detail: 'User Updated/Created', life: 3000 });
        single.value = userService.getNewUser();
        dialog.value = false;
    } else {
        toast.add({ severity: 'error', summary: 'Error', detail: 'Error updating/creating user', life: 3000 });
    }
};

const editData = (data: User) => {
    single.value = { ...data };
    dialog.value = true;
};

const confirmDelete = (data: User) => {
    single.value = data;
    deleteDialog.value = true;
};

const deleteData = async () => {
    if (null === single.value) {
        toast.add({ severity: 'error', summary: 'Error', detail: 'No data Provided', life: 3000 });
        return
    }
    if (await userService.save(single.value)){
        toast.add({ severity: 'success', summary: 'Successful', detail: 'User Deleted', life: 3000 });
    }
    else{
        toast.add({ severity: 'error', summary: 'Error', detail: 'Error deleting user', life: 3000 });
    }
    deleteDialog.value = false;
    single.value = userService.getNewUser();
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
                            <h5 class="m-0">Manage Users</h5>
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
                    <Column field="username" header="Username" :sortable="true" headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Username</span>
                            {{ slotProps.data.username }}
                        </template>
                    </Column>
                    <Column field="fullName" header="Full Name" :sortable="true" headerStyle="width:14%; min-width:8rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Full Name</span>
                            {{ slotProps.data.fullName }}
                        </template>
                    </Column>
                    <Column field="email" header="E-mail" :sortable="true" headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">E-mail</span>
                            {{ slotProps.data.email }}
                        </template>
                    </Column>
                    <Column field="vatNumber" header="VAT Number" :sortable="true"
                        headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">VAT Number</span>
                            {{ slotProps.data.vatNumber }}
                        </template>
                    </Column>
                    <Column field="roles" header="Roles" headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Roles</span>
                            {{ slotProps.data.roles.join(',') }}
                        </template>
                    </Column>
                    <Column field="status" header="Status" :sortable="true" headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Status</span>
                            {{ slotProps.data.status }}
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

                <Dialog v-model:visible="dialog" :style="{ width: '450px' }" header="User Details" :modal="true"
                    class="p-fluid">

                    <div class="field">
                        <label for="username">Username</label>
                        <InputText id="username" v-model.trim="single.username" required="true" autofocus
                            :class="{ 'p-invalid': submitted && !single.username }" />
                        <small class="p-invalid" v-if="submitted && !single.username">Username is required.</small>
                    </div>

                    <div class="field">
                        <label for="fullName">FullName</label>
                        <InputText id="fullName" v-model.trim="single.fullName" required="true" autofocus
                            :class="{ 'p-invalid': submitted && !single.fullName }" />
                        <small class="p-invalid" v-if="submitted && !single.fullName">FullName is required.</small>
                    </div>

                    <div class="field">
                        <label for="vatNumber">VAT Number</label>
                        <InputText id="vatNumber" v-model.trim="single.vatNumber" required="true" autofocus
                            :class="{ 'p-invalid': submitted && !single.vatNumber }" />
                        <small class="p-invalid" v-if="submitted && !single.vatNumber">VAT Number is required.</small>
                    </div>

                    <div class="field">
                        <label for="email">E-mail</label>
                        <InputText type="email" id="email" v-model.trim="single.email" required="true" autofocus
                            :class="{ 'p-invalid': submitted && !single.email }" />
                        <small class="p-invalid" v-if="submitted && !single.email">E-mail is required.</small>
                    </div>

                    <div class="field">
                        <label for="address">Address</label>
                        <InputText type="address" id="address" v-model.trim="single.address" required="true" autofocus
                            :class="{ 'p-invalid': submitted && !single.address }" />
                        <small class="p-invalid" v-if="submitted && !single.address">Address is required.</small>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <Password id="password" v-model="single.password" rows="3" cols="20" />
                    </div>

                    <div class="field">
                        <label for="roles" class="mb-3">Roles</label>
                        <MultiSelect id="roles" v-model="single.roles" :options="roles" optionLabel="label"
                            optionValue="value" placeholder="Select a Role">
                        </MultiSelect>
                    </div>

                    <div class="field">
                        <label for="status" class="mb-3">Status</label>
                        <Dropdown id="status" v-model="single.status" :options="statuses" optionLabel="label"
                            optionValue="value" placeholder="Select a Status">
                        </Dropdown>
                    </div>

                    <template #footer>
                        <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="hideDialog" />
                        <Button label="Save" icon="pi pi-check" class="p-button-text" @click="saveSingle" />
                    </template>
                </Dialog>

                <Dialog v-model:visible="deleteDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span v-if="single">Are you sure you want to delete <b>{{ single.username }}</b>?</span>
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
