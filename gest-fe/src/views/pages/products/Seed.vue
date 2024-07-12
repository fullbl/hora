<script setup lang="ts">
import Steps from '@/components/Steps.vue';
import type { Seed } from '@/interfaces/product';
import { computed, type PropType } from 'vue';

const props = defineProps({
    seed: {
        type: Object as PropType<Seed>,
        required: true
    },
    isInvalid: {
        type: Function,
        required: true
    }
});

const price = computed({
    get(): number {
        return (props.seed.price ?? 0) / 100;
    },
    set(price: number) {
        if (props.seed) {
            props.seed.price = Math.round(price * 100);
        }
    }
});
</script>
<template>
    <div class="formgrid grid">
        <div class="field col-2">
            <label for="weight">Weight</label>
            <Checkbox id="weight" v-model="seed.weight" :binary="true" :class="{ 'p-invalid': isInvalid('weight') }" style="display: block" />
        </div>

        <div class="field col-3">
            <label for="decigrams">Decigrams</label>
            <InputNumber type="number" id="decigrams" v-model="seed.decigrams" buttonLayout="horizontal" required autofocus showButtons :class="{ 'p-invalid': isInvalid('decigrams') }" />
        </div>

        <div class="field col-3">
            <label for="days">Days</label>
            <InputNumber type="number" id="days" v-model="seed.days" buttonLayout="horizontal" required autofocus showButtons :class="{ 'p-invalid': isInvalid('days') }" />
        </div>

        <div class="field col-4">
            <label for="price">Price</label>
            <InputNumber type="number" id="price" mode="currency" currency="EUR" v-model="price" buttonLayout="horizontal" autofocus showButtons :class="{ 'p-invalid': isInvalid('price') }" />
        </div>

        <div class="field col-12">
            <Steps v-model="seed.steps" />
        </div>
    </div>
</template>
