import { ref, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';

export function useDialog() {
    const dialog = ref(false);
    const deleteDialog = ref(false);

    return {
        dialog,
        deleteDialog,
        hideDialog() {
            dialog.value = false;
        },
    };
}
