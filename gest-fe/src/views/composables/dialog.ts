import { computed, ref } from 'vue'

export function useDialog() {
    const dialog = ref('')
    const deleteDialog = ref(false)

    return {
        dialog,
        deleteDialog,
        hideDialog() {
            dialog.value = ''
        },
        showDialog: computed({
            get: () => '' !== dialog.value,
            set: (value) => {  
                dialog.value = false === value ? '' : 'Dialog'
            }
        }),
    }
}
