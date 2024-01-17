<script setup lang="ts">
import Button from 'primevue/button';
import Menu from 'primevue/menu';
import { ref } from 'vue';

const props = defineProps({
    change: {
        required: true,
        type: Function
    },
    empty: {
        required: true,
        type: Function
    },
    remove: {
        required: true,
        type: Function
    }
});

const open = ref();
const items = ref([
    {
        label: 'Change',
        icon: 'pi pi-cog',
        command: () => props.change()
    },
    {
        label: 'Empty',
        icon: 'pi pi-cloud-upload',
        command: () => props.empty()
    },
    {
        label: 'Remove',
        icon: 'pi pi-trash',
        command: () => props.remove()
    }
]);
const toggle = (event: MouseEvent) => open.value.toggle(event);
</script>

<template>
    <Button type="button" icon="pi pi-ellipsis-v" @click="toggle" />
    <Menu ref="open" :model="items" :popup="true" />
</template>
