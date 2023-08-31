import { ref } from 'vue';

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
