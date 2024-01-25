<script setup lang="ts">
import { useLayout } from '@/layout/composables/layout';
import authService from '@/service/AuthService';

const { onMenuToggle, contextPath } = useLayout();
const logout = () => {
    authService.logout();
};

const logoUrl = `${contextPath}layout/images/logo-dark.svg`;
</script>

<template>
    <div class="layout-topbar">
        <router-link to="/" class="layout-topbar-logo">
            <img :src="logoUrl" alt="logo" />
            <span>Hora</span>
        </router-link>

        <div class="flex justify-content-between w-full">
            <button class="p-link layout-menu-button layout-topbar-button" @click="onMenuToggle()">
                <i class="pi pi-bars"></i>
            </button>

            <button class="p-link layout-menu-button layout-topbar-button" @click="logout()">
                <i class="pi pi-sign-out"></i>
            </button>
        </div>
    </div>
</template>

<style lang="scss" scoped></style>
