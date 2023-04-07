<script setup lang="ts">
import { useLayout } from '@/layout/composables/layout';

const { onMenuToggle, contextPath } = useLayout();


const logoUrl = `${contextPath}layout/images/logo-dark.svg`;

</script>

<template>
    <div class="layout-topbar">
        <router-link to="/" class="layout-topbar-logo">
            <img :src="logoUrl" alt="logo" />
            <span>Hora</span>
        </router-link>

        <button class="p-link layout-menu-button layout-topbar-button" @click="onMenuToggle()">
            <i class="pi pi-bars"></i>
        </button>
    </div>
</template>

<style lang="scss" scoped></style>
