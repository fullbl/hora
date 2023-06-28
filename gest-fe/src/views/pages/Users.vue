<script setup lang="ts">
import userService from '@/service/UserService';
import { useDataView } from '../composables/dataView'
const { filters, data, single, save, openNew, editData, dialog, hideDialog, deleteDialog, confirmDelete, deleteData, isInvalid } = useDataView(userService);
const roles = [
    { label: 'Admin', value: 'ROLE_ADMIN' },
    { label: 'Operator', value: 'ROLE_OPERATOR' },
    { label: 'Customer', value: 'ROLE_CUSTOMER' }
];
const statuses = [
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' },
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

                <DataTable :value="data" dataKey="id" :paginator="true" :rows="10" :filters="filters"
                    paginatorTemplate="FirstPageLink PrevPageLink PageLinks NextPageLink LastPageLink CurrentPageReport RowsPerPageDropdown"
                    :rowsPerPageOptions="[5, 10, 25]"
                    currentPageReportTemplate="Showing {first} to {last} of {totalRecords} data" responsiveLayout="scroll">
                    <template #header>
                        <div class="flex flex-column md:flex-row md:justify-content-between md:align-items-center">
                            <h5 class="m-0">Manage Users</h5>
                            <span class="block mt-2 md:mt-0 p-input-icon-left">
                                <i class="pi pi-search" />
                                <InputText v-model="filters.global.value" placeholder="Search..." />
                            </span>
                        </div>
                    </template>

                    <Column field="id" header="Id" :sortable="true" headerStyle="width:10%; min-width:5rem;">
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
                    <Column field="sdi" header="Sdi" :sortable="true" headerStyle="width:10%; min-width:5rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Sdi</span>
                            {{ slotProps.data.sdi }}
                        </template>
                    </Column>
                    <Column field="zone" header="Zone" :sortable="true" headerStyle="width:14%; min-width:10rem;">
                        <template #body="slotProps">
                            <span class="p-column-title">Zone</span>
                            {{ slotProps.data.zone }}
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

                <Dialog v-model:visible="dialog" :style="{ width: '450px' }" header="User Details" :modal="true" v-if="single"
                    class="p-fluid">

                    <div class="field">
                        <label for="username">Username</label>
                        <InputText id="username" v-model.trim="single.username" required autofocus :class="{ 'p-invalid': isInvalid('username') }" />
                    </div>

                    <div class="field">
                        <label for="fullName">FullName</label>
                        <InputText id="fullName" v-model.trim="single.fullName" required autofocus :class="{ 'p-invalid': isInvalid('fullName') }" />
                    </div>

                    <div class="field">
                        <label for="vatNumber">VAT Number</label>
                        <InputText id="vatNumber" v-model.trim="single.vatNumber" required autofocus :class="{ 'p-invalid': isInvalid('vatNumber') }" />
                    </div>

                    <div class="field">
                        <label for="sdi">SDI Number</label>
                        <InputText id="sdi" v-model.trim="single.sdi" required autofocus :class="{ 'p-invalid': isInvalid('sdi') }" />
                    </div>

                    <div class="field">
                        <label for="email">E-mail</label>
                        <InputText type="email" id="email" v-model.trim="single.email" required autofocus :class="{ 'p-invalid': isInvalid('email') }"/>
                    </div>

                    <div class="field">
                        <label for="address">Address</label>
                        <InputText type="address" id="address" v-model.trim="single.address" required autofocus :class="{ 'p-invalid': isInvalid('address') }"/>
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <Password id="password" v-model="single.password" :class="{ 'p-invalid': isInvalid('password') }"/>
                    </div>

                    <div class="field">
                        <label for="zone">Zone</label>
                        <InputText id="zone" v-model="single.zone" :class="{ 'p-invalid': isInvalid('zone') }"/>
                    </div>

                    <div class="field">
                        <label for="discount">Discount</label>
                        <InputNumber id="discount" v-model="single.discount" :class="{ 'p-invalid': isInvalid('discount') }"/>
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
                        <Button label="Save" icon="pi pi-check" class="p-button-text" @click="save" />
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
