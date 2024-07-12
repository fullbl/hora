<script setup lang="ts">
import type { Extra } from '@/interfaces/product';
import { computed, type PropType } from 'vue';

const props = defineProps({
    extra: {
        type: Object as PropType<Extra>,
        required: true
    },
    isInvalid: {
        type: Function,
        required: true
    }
});

const price = computed({
    get(): number {
        return (props.extra.price ?? 0) / 100;
    },
    set(price: number) {
        props.extra.price = Math.round(price * 100);
    }
});
</script>
<template>
    <div class="formgrid grid">
        <div class="field col-6">
            <label for="price">Price</label>
            <InputNumber type="number" id="price" mode="currency" currency="EUR" buttonLayout="horizontal" v-model="price" autofocus showButtons :class="{ 'p-invalid': isInvalid('price') }" />
        </div>
    </div>
</template>
