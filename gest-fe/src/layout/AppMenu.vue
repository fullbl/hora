<script setup lang="ts">
import { ref } from 'vue';

import AppMenuItem from './AppMenuItem.vue';

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
            { label: 'Storage Dashboard', icon: 'pi pi-fw pi-chart-bar', to: '/dashboards/storage' },
            { label: 'Delivery Dashboard', icon: 'pi pi-fw pi-chart-bar', to: '/dashboards/delivery' },
            { label: 'Daily Dashboard', icon: 'pi pi-fw pi-chart-bar', to: '/dashboards/daily' },
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
