<script setup lang="ts">
import productService from '@/service/ProductService';
import type { Mix, Seed } from '@/interfaces/product';
import { onMounted, ref, type PropType } from 'vue';
import MultiSelect from 'primevue/multiselect';
import InputNumber from 'primevue/inputnumber';

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
            <label for="days">Days</label>
            <InputNumber id="days" type="number" v-model="mix.days" :class="{'p-invalid': isInvalid('days')}" />
        </div>
        <div class="field col">
            <label for="products">Products</label>
            <MultiSelect id="products" filter v-model="mix.products" :options="products" display="chip" optionLabel="name" :class="{'p-invalid': isInvalid('products')}" />
        </div>
    </div>
</template>
