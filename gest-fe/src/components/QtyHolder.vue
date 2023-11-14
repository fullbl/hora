<script setup lang="ts">
defineProps({
    qty: {
        required: true,
        type: Number
    }
});
</script>

<template>
    <Chip class="pl-0 pr-3 mb-2">
        <span class="bg-primary border-circle w-2rem h-2rem flex align-items-center justify-content-center" v-text="qty" />
        <span class="ml-2 font-medium">
            <slot></slot>
        </span>
    </Chip>
</template>
