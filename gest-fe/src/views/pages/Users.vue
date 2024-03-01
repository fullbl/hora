<script setup lang="ts">
import userService from '@/service/UserService';
import zoneService from '@/service/ZoneService';
import { useDataView } from '../composables/dataView';
import Logger from '@/components/Logger.vue';
import { computed, onMounted, ref } from 'vue';
import type Zone from '@/interfaces/zone';
import Button from 'primevue/button';

const { filters, data: _data, single, save, openNew, editData, dialog, hideDialog, showDialog, deleteDialog, confirmDelete, deleteData, isInvalid } = useDataView(userService);
const roles = [
    { label: 'Admin', value: 'ROLE_ADMIN' },
    { label: 'Operator', value: 'ROLE_OPERATOR' },
    { label: 'Customer', value: 'ROLE_CUSTOMER' }
];
const statuses = [
    { label: 'Active', value: 'active' },
    { label: 'Inactive', value: 'inactive' }
];
const zones = ref<Array<Zone>>([]);
const subzones = ref<Array<Zone>>([]);
const filteredSubzones = computed<Array<Zone>>(() => subzones.value.filter((zone: Zone) => single.value?.zones.some((z: Zone) => z.id === zone.parent?.id)));

const filterZones = ref<Array<Zone>>([]);

const toggleFilterZones = (zone: Zone) => {
    if (filterZones.value.includes(zone)) {
        filterZones.value = filterZones.value.filter((z) => z.id !== zone.id);
    } else {
        filterZones.value = [...filterZones.value, zone];
    }
};

onMounted(async () => {
    const z = await zoneService.getAll();
    zones.value = z.filter((zone: Zone) => null === zone.parent);
    subzones.value = z.filter((zone: Zone) => null !== zone.parent);
});

const data = computed(() => {
    if (!_data.value) return [];

    return _data.value.filter((x) => filterZones.value.length === 0 || x.zones.some((z) => filterZones.value.some((s) => s.id === z.id)));
});

const zoneModel = computed<Zone>({
    get: () => {
        return single.value?.zones.find((zone: Zone) => null === zone.parent) ?? zoneService.getNew();
    },
    set: (zone: Zone) => {
        if (single.value) {
            single.value.zones = [zone];
        }
    }
});

const subzoneModel = computed<Zone>({
    get: () => {
        return single.value?.zones.find((zone: Zone) => null !== zone.parent) ?? zoneService.getNew();
    },
    set: (zone: Zone | null) => {
        if (single.value) {
            const zones = single.value.zones.filter((z: Zone) => null === z.parent);
            if (zone) {
                zones.push(zone);
            }

            single.value.zones = zones;
        }
    }
});
const logEntity = ref(null);
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
                        <div class="flex flex-wrap justify-content-center gap-2">
                            <Button v-for="zone in zones" :key="zone.id" class="p-button" :severity="filterZones.includes(zone) ? 'warning' : 'info'" @click="toggleFilterZones(zone)">{{ zone.name }}</Button>
                        </div>
                        <div class="flex flex-wrap justify-content-center gap-2 my-4">
                            <Button v-for="zone in subzones" :key="zone.id" class="p-button" :severity="filterZones.includes(zone) ? 'warning' : 'secondary'" @click="toggleFilterZones(zone)">{{ zone.name }}</Button>
                        </div>
                        <div class="flex flex-column md:flex-row md:justify-content-between md:align-items-center">
                            <h5 class="m-0">Manage Users</h5>
                            <span class="block mt-2 md:mt-0 p-input-icon-left">
                                <i class="pi pi-search" />
                                <InputText v-model="filters.global.value" placeholder="Search..." />
                            </span>
                        </div>
                    </template>

                    <Column field="id" header="Id" :sortable="true">
                        <template #body="slotProps">
                            <span class="p-column-title">Id</span>
                            {{ slotProps.data.id }}
                        </template>
                    </Column>
                    <Column field="fullName" header="Full Name" :sortable="true">
                        <template #body="slotProps">
                            <span class="p-column-title">Full Name</span>
                            {{ slotProps.data.fullName }}
                        </template>
                    </Column>
                    <Column field="zones" header="Zones" :sortable="true">
                        <template #body="slotProps">
                            <span class="p-column-title">Zones</span>
                            {{
                                slotProps.data.zones
                                    .filter((zone: Zone) => null === zone.parent)
                                    .map((zone: Zone) => zone.name)
                                    .join(',')
                            }}
                            <br />
                            {{
                                slotProps.data.zones
                                    .filter((zone: Zone) => null !== zone.parent)
                                    .map((zone: Zone) => zone.name)
                                    .join(',')
                            }}
                        </template>
                    </Column>
                    <Column field="roles" header="Roles">
                        <template #body="slotProps">
                            <span class="p-column-title">Roles</span>
                            {{ slotProps.data.roles.join(',') }}
                        </template>
                    </Column>
                    <Column field="status" header="Status" :sortable="true">
                        <template #body="slotProps">
                            <span class="p-column-title">Status</span>
                            {{ slotProps.data.status }}
                        </template>
                    </Column>
                    <Column field="position" header="Position" :sortable="true">
                        <template #body="slotProps">
                            <span class="p-column-title">Position</span>
                            {{ slotProps.data.position }}
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

                <Dialog v-model:visible="showDialog" :style="{ width: '450px' }" :header="dialog" :modal="true" v-if="single" class="p-fluid">
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
                        <InputText type="email" id="email" v-model.trim="single.email" required autofocus :class="{ 'p-invalid': isInvalid('email') }" />
                    </div>

                    <div class="field">
                        <label for="address">Address</label>
                        <InputText type="address" id="address" v-model.trim="single.address" required autofocus :class="{ 'p-invalid': isInvalid('address') }" />
                    </div>

                    <div class="field">
                        <label for="password">Password</label>
                        <Password id="password" v-model="single.password" :class="{ 'p-invalid': isInvalid('password') }" />
                    </div>

                    <div class="field">
                        <label for="zone">Zones</label>
                        <Dropdown id="zone" v-model="zoneModel" :options="zones" optionLabel="name" placeholder="Select a Zone" />
                    </div>

                    <div class="field" v-show="filteredSubzones.length > 0">
                        <label for="subzone">SubZones</label>
                        <Dropdown id="subzone" showClear v-model="subzoneModel" :options="filteredSubzones" optionLabel="name" placeholder="Select a SubZone" />
                    </div>

                    <div class="field">
                        <label for="discount">Discount</label>
                        <InputNumber id="discount" v-model="single.discount" :class="{ 'p-invalid': isInvalid('discount') }" />
                    </div>

                    <div class="field">
                        <label for="roles" class="mb-3">Roles</label>
                        <MultiSelect id="roles" v-model="single.roles" :options="roles" optionLabel="label" optionValue="value" placeholder="Select a Role"> </MultiSelect>
                    </div>

                    <div class="field">
                        <label for="status" class="mb-3">Status</label>
                        <Dropdown id="status" v-model="single.status" :options="statuses" optionLabel="label" optionValue="value" placeholder="Select a Status"> </Dropdown>
                    </div>

                    <div class="field">
                        <label for="position">Position</label>
                        <InputNumber id="position" v-model="single.position" :class="{ 'p-invalid': isInvalid('position') }" showButtons />
                    </div>

                    <template #footer>
                        <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="hideDialog" />
                        <Button label="Save" icon="pi pi-check" class="p-button-text" @click="save" />
                    </template>
                </Dialog>

                <Dialog v-model:visible="deleteDialog" :style="{ width: '450px' }" header="Confirm" :modal="true">
                    <div class="flex align-items-center justify-content-center">
                        <i class="pi pi-exclamation-triangle mr-3" style="font-size: 2rem" />
                        <span v-if="single"
                            >Are you sure you want to delete <b>{{ single.username }}</b
                            >?</span
                        >
                    </div>
                    <template #footer>
                        <Button label="No" icon="pi pi-times" class="p-button-text" @click="deleteDialog = false" />
                        <Button label="Yes" icon="pi pi-check" class="p-button-text" @click="deleteData()" />
                    </template>
                </Dialog>

                <Logger entity-name="App\Entity\Product" :entity-id="logEntity" @close="logEntity = null" />
            </div>
        </div>
    </div>
</template>

<style scoped lang="scss"></style>
