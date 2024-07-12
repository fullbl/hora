<script setup lang="ts">
import productService from '@/service/ProductService';
import type { Mix, Seed } from '@/interfaces/product';
import { onMounted, ref, type PropType } from 'vue';
import MultiSelect from 'primevue/multiselect';

defineProps({
    mix: {
        type: Object as PropType<Mix>,
        required: true
    },
    isInvalid: {
        type: Function,
        required: true
    }
});
const products = ref([] as {id: number, name: string}[]);

onMounted(async () => {
    products.value = (await productService.getSeeds()).map((seed: Seed) => ({ id: seed.id ?? 0, name: seed.name }));
});
</script>
<template>
    <div class="formgrid grid">
        <div class="field col">
            <label for="weight">Products</label>
            <MultiSelect filter v-model="mix.products" :options="products" display="chip" optionLabel="name" />
        </div>
    </div>
</template>
