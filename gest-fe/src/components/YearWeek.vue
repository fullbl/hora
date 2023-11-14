<script setup lang="ts">
import InputNumber from 'primevue/inputnumber';

const props = defineProps({
    year: {
        type: Number,
        required: true,
        validator: (y: number) => y > 2000 && y < 2100
    },
    week: {
        type: Number,
        required: true,
        validator: (w: number) => w > 0 && w < 54
    }    
})

const emit = defineEmits(['update:year', 'update:week'])

</script>

<template>
    <div class="flex flex-wrap gap-3 p-fluid">
        <div>
            <label for="year" class="font-bold block mb-2">Year</label>
            <InputNumber inputId="year" :modelValue="props.year" showButtons :useGrouping="false" placeholder="year" 
                @update:modelValue="emit('update:year', $event)"
            />
        </div>
        <div>
            <label for="week" class="font-bold block mb-2">Week</label>
            <InputNumber inputId="week" :modelValue="props.week" :min="1" :max="53" showButtons placeholder="week" 
                @update:modelValue="emit('update:week', $event)"
            />
        </div>
    </div>
</template>
