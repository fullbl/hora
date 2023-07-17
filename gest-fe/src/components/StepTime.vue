<script setup lang="ts">
import InputNumber from 'primevue/inputnumber';
import { computed } from 'vue';

const props = defineProps({
    modelValue: {
        type: Number,
        default: null,
        required: true
    },
    divide: {
        type: String,
        default: 'minutes',
        required: true,
        validator: (v: string) => ['days', 'hours', 'minutes'].includes(v)
    }
    
})

const emit = defineEmits(['update:modelValue'])
const divide = {
    days: (x:number) => x / 60 / 24,
    hours: (x: number) => x / 60,
    minutes: (x: number) => x,
}
const multiply = {
    days: (x:number) => x * 60 * 24,
    hours: (x: number) => x * 60,
    minutes: (x: number) => x,
}

const minutes = computed({
    get: () => divide[props.divide](props.modelValue),
    set: (x:number) => emit('update:modelValue', multiply[props.divide](x))
})
</script>

<template>
    <div class="p-inputgroup flex-1">
    <InputNumber v-model="minutes" required autofocus />
    <span class="p-inputgroup-addon">{{ props.divide }}</span>
</div>
</template>