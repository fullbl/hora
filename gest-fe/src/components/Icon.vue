<script setup lang="ts">
import { computed } from 'vue';

const props = defineProps({
    type: {
        type: String,
        required: true
    }
});
const htmlClass = computed(() => {
    switch (props.type) {
        case 'soaking':
        case 'hot_soaking':
            return '';
        case 'preactivation':
            return 'pi pi-compass';
        case 'light':
            return 'pi pi-sun';
        case 'blackout':
            return 'pi pi-moon';
        }
    if(props.type.startsWith('pi')) {
        return props.type;
    }
    if(props.type.startsWith('fa')) {
        return '';
    }
    return 'pi pi-question';
});

const icon = computed(() => {
    switch (props.type) {
        case 'soaking':
            return 'fa fa-water';
        case 'hot_soaking':
            return 'fa fa-fire';
        case 'preactivation':
        case 'light':
        case 'blackout':
            return '';
    }
    if(props.type.startsWith('fa ')) {
        return props.type;
    }
    if(props.type.startsWith('pi ')) {
        return '';
    }
    return '';
})

const element = computed(() => {
    switch (props.type) {
        case 'soaking':
            return 'font-awesome-icon';
        case 'hot_soaking':
            return 'font-awesome-icon';
        case 'preactivation':
            return 'i';
        case 'light':
            return 'i';
        case 'blackout':
            return 'i';
    }
    if(props.type.startsWith('fa ')) {
        return 'font-awesome-icon';
    }
    if(props.type.startsWith('pi ')) {
        return 'i';
    }
    return 'i';
});

</script>

<template>
    <component :is="element" :class="htmlClass" :icon="icon" />
</template>
