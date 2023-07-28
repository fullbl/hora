<script setup lang="ts">
import { ref } from 'vue';

import AppMenuItem from './AppMenuItem.vue';
import stepService from '@/service/StepService'
const model = ref([
    {
        label: 'HORA',
        items: [
            { label: 'Users', icon: 'pi pi-fw pi-user', to: '/users' },
            { label: 'Products', icon: 'pi pi-fw pi-list', to: '/products' },
            { label: 'Orders', icon: 'pi pi-fw pi-shopping-cart', to: '/orders' },
            { label: 'Deliveries', icon: 'pi pi-fw pi-car', to: '/deliveries' },
        ]
    },
    {
        label: 'Dashboards',
        items: [
            { label: 'Steps Dashboard', icon: 'pi pi-check-square', to: '/dashboards/steps' },
            { label: 'Planting Dashboard', icon: stepService.getIcon('planting'), to: '/dashboards/planting' },
            { label: 'Harvest Dashboard', icon: 'pi pi-fw pi-upload', to: '/dashboards/harvest' },
            { label: 'Delivery Dashboard', icon: stepService.getIcon('shipping'), to: '/dashboards/delivery' },
            { label: 'Payment Dashboard', icon: stepService.getIcon('payment'), to: '/dashboards/payment' },
        ]
    },
    {
        label: 'Operations',
        items: [
            { label: 'Planting', icon: stepService.getIcon('planting'), to: '/operations/planting' },
        ]
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
