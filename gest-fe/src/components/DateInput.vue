<script setup lang="ts">
import dayjs, { type Dayjs } from 'dayjs';
import Calendar from 'primevue/calendar';
import { computed, type PropType } from 'vue';

const props = defineProps({
    modelValue: {
        type: [String, Object] as PropType<Dayjs>,
        default: dayjs().format('YYYY-MM-DD'),
        required: true
    }
});
const emit = defineEmits(['update:modelValue']);
const date = computed({
    get: () => {
        return dayjs(props.modelValue).toDate();
    },
    set: (x: Date) => emit('update:modelValue', dayjs(x))
});
</script>

<template>
    <Calendar v-model="date" />
</template>
