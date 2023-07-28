<script setup lang="ts">
import type Step from "@/interfaces/step";
import stepService from '@/service/StepService';
import { computed } from "vue";
import StepTime from "./StepTime.vue";


const props = defineProps(['modelValue'])
const emit = defineEmits(['update:modelValue'])

const addNew = function () {
    steps.value = [...(steps.value ?? []), stepService.getNew()]
}
const remove = function (value: Step) {
    steps.value = steps.value.filter((v) => v !== value);
}

const types = stepService.getTypes();

const steps = computed({
    get(): Step[] {
        return props.modelValue
    },
    set(value: Step[]) {
        emit('update:modelValue', value)
    }
})

const divideMap = {
    soaking: 'hours',
    planting: 'hours',
    light: 'days',
    blackout: 'days',
}
</script>

<template>
    <div>
        <h3>Steps</h3>
        <Button label="New" icon="pi pi-plus" class="p-button-success mr-2" @click="addNew" />
        <OrderList v-model="steps" dataKey="sort">
            <template #item="slotProps">
                <div class="flex justify-content-between">
                    <Dropdown v-model="slotProps.item.name" :options="types" optionLabel="label" optionValue="value"
                        placeholder="Select a Type">
                    </Dropdown>
                    <StepTime v-model="slotProps.item.minutes" :divide="divideMap[slotProps.item.name] ?? 'minutes'" />
                    <Button icon="pi pi-trash" severity="danger" @click="remove(slotProps.item)" />
                </div>
            </template>
        </OrderList>
    </div>
</template>