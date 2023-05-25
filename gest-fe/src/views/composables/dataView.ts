import { ref, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import type Service from '@/interfaces/service';
import type { DataTableFilterMeta } from 'primevue/datatable';
import type { DataTableFilterMetaData } from 'primevue/datatable';

interface HoraMeta extends DataTableFilterMeta
{
    global: DataTableFilterMetaData
}
export function useDataView<T>(service: Service<T>) {
    const data = ref<Array<T>>();
    const single = ref<T>();
    const toast = useToast();
    const dialog = ref(false);
    const deleteDialog = ref(false);
    const filters = ref({
        global: { value: '', matchMode: 'contains' }
    } as HoraMeta);

    onMounted(async () => {
        data.value = await service.getAll();
    });
    single.value = service.getNew();
    data.value = [];

    return {
        data,
        single,
        filters,
        dialog,
        deleteDialog,
        async save() {
            if ('undefined' === typeof single.value || null === single.value) {
                toast.add({ severity: 'error', summary: 'Error', detail: 'No data Provided', life: 3000 });
                return
            }
            if (await service.save(single.value)) {
                toast.add({ severity: 'success', summary: 'Successful', detail: 'User Updated/Created', life: 3000 });
                single.value = service.getNew();
                data.value = await service.getAll();
                dialog.value = false;
            } else {
                toast.add({ severity: 'error', summary: 'Error', detail: 'Error updating/creating', life: 3000 });
            }
        },
        openNew() {
            single.value = service.getNew();
            dialog.value = true;
        },
        hideDialog() {
            dialog.value = false;
        },
        editData(data: T) {
            single.value = { ...data };
            dialog.value = true;
        },
        confirmDelete(data: T) {
            single.value = data;
            deleteDialog.value = true;
        },
        async deleteData() {
            if ('undefined' === typeof single.value || null === single.value) {
                toast.add({ severity: 'error', summary: 'Error', detail: 'No data Provided', life: 3000 });
                return
            }
            if (await service.delete(single.value)) {
                toast.add({ severity: 'success', summary: 'Successful', detail: 'User Deleted', life: 3000 });
                data.value = await service.getAll()
            }
            else {
                toast.add({ severity: 'error', summary: 'Error', detail: 'Error deleting user', life: 3000 });
            }
            deleteDialog.value = false;
            single.value = service.getNew();
        }
    }
}