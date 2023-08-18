<script setup lang="ts">
import { computed, ref, watch, type PropType } from 'vue';
import logService from '@/service/LogService';
import type Log from '@/interfaces/log'
const props = defineProps({
    entityName: {
        required: true,
        type: String,
    },
    entityId: {
        required: true,
        validator: (v: any) => typeof v === 'number' || v === null,
    },
})

const emit = defineEmits(['close'])
const logs = ref([] as Log[]);
const visible = computed(() =>
    props.entityId !== null
)

watch(() => props.entityId, async () => {
    if ('number' !== typeof props.entityId) {
        return [];
    }
    logs.value = await logService.getByEntity(props.entityName, props.entityId)
})
</script>

<template>
    <Dialog v-model:visible="visible" @update:visible="emit('close')" :style="{ width: '450px' }" h eader="Logger"
        :modal="true" class="p-fluid">
        <div v-for="log in logs" class="flex justify-content-between">
            {{ log.user.username }} {{ log.createdAt }}
            <span v-for="c, p of log.changes">
                {{ p }}: {{ c[0] }} in {{ c[1] }}
            </span>
        </div>
    </Dialog>
</template>