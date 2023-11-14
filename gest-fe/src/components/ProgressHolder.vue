<script setup lang="ts">
defineProps({
    dp: {
        required: true,
        type: Object
    }
});
</script>

<template>
    <ProgressBar v-if="dp.done > 0" class="m-1" :value="(dp.done / dp.qty) * 100"> {{ dp.done }} / {{ dp.qty }} </ProgressBar>
</template>
