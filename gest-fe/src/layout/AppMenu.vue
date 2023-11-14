<script setup lang="ts">
import { ref } from 'vue';

import AppMenuItem from './AppMenuItem.vue';
import stepService from '@/service/StepService';
const model = ref([
    {
        label: 'HORA',
        items: [
            { label: 'Users', icon: 'pi pi-fw pi-user', to: '/users' },
            { label: 'Products', icon: 'pi pi-fw pi-list', to: '/products' },
            // { label: 'Orders', icon: 'pi pi-fw pi-shopping-cart', to: '/orders' },
            { label: 'Deliveries', icon: 'pi pi-fw pi-car', to: '/deliveries' }
        ]
    },
    {
        label: 'Dashboards',
        items: [
            { label: 'Steps Dashboard', icon: 'pi pi-check-square', to: '/dashboards/steps' },
            { label: 'Planting Dashboard', icon: 'pi pi-fw pi-download', to: '/dashboards/planting' },
            { label: 'Harvest Dashboard', icon: 'pi pi-fw pi-upload', to: '/dashboards/harvest' },
            { label: 'Delivery Dashboard', icon: 'pi pi-fw pi-shopping-cart', to: '/dashboards/delivery' },
            { label: 'Payment Dashboard', icon: 'pi pi-fw pi-euro', to: '/dashboards/payment' }
        ]
    },
    {
        label: 'Operations',
        items: [{ label: 'Soaking', icon: stepService.getIcon('soaking'), to: '/operations/soaking' }]
    }
]);
</script>

<template>
    <ul class="layout-menu">
        <template v-for="(item, i) in model" :key="item">
            <app-menu-item :item="item" :index="i"></app-menu-item>
        </template>
    </ul>
</template>

<style lang="scss" scoped></style>
