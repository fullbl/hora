<script setup lang="ts">
import { ref } from 'vue';
import type { Ref } from 'vue'
import authService from '@/service/AuthService';
import { useRouter } from 'vue-router'

let
    loading: boolean = false
    ;

const
    username: Ref<string> = ref(''),
    password: Ref<string> = ref(''),
    router = useRouter(),
    login: () => void = async function () {
        loading = true
        if (!await authService.login(username.value, password.value)) {
            alert('Error, retry.');
            return;
        }
        loading = false
        router.push({ name: 'daily-dashboard' })
    }
    ;
</script>

<template>
    <div class="surface-ground flex align-items-center justify-content-center min-h-screen min-w-screen overflow-hidden">
        <div class="flex flex-column align-items-center justify-content-center">
            <img src="/layout/images/logo-white.svg" alt="Hora logo" class="mb-5 w-6rem flex-shrink-0" />
            <div
                style="border-radius: 56px; padding: 0.3rem; background: linear-gradient(180deg, var(--primary-color) 10%, rgba(33, 150, 243, 0) 30%)">
                <div class="w-full surface-card py-8 px-5 sm:px-8" style="border-radius: 53px">
                    <div class="text-center mb-5">
                        <div class="text-900 text-3xl font-medium mb-3">Hora control panel</div>
                        <span class="text-600 font-medium">Sign in to continue</span>
                    </div>

                    <form @submit.prevent="login">
                        <fieldset :disable="loading">
                            <label for="username" class="block text-900 text-xl font-medium mb-2">Username</label>
                            <InputText id="username" type="text" placeholder="Username" class="w-full md:w-30rem mb-5"
                                style="padding: 1rem" v-model="username" />

                            <label for="password" class="block text-900 font-medium text-xl mb-2">Password</label>
                            <Password id="password" v-model="password" placeholder="Password" class="w-full mb-3"
                                inputClass="w-full" :inputStyle="{ padding: '1rem' }"></Password>

                            <Button type="submit" label="Sign In" class="w-full p-3 text-xl"></Button>
                        </fieldset>
                    </form>
                </div>
            </div>
        </div>
    </div>
</template>

<style scoped></style>
