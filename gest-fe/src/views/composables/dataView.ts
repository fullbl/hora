import { ref, onMounted } from 'vue';
import { useToast } from 'primevue/usetoast';
import type Service from '@/interfaces/service';
import type { DataTableFilterMeta } from 'primevue/datatable';
import type { DataTableFilterMetaData } from 'primevue/datatable';
import { useDialog } from './dialog';

interface HoraMeta extends DataTableFilterMeta {
    global: DataTableFilterMetaData;
}
export function useDataView<T>(service: Service<T>) {
    const data = ref<Array<T>>();
    const single = ref<T>();
    const toast = useToast();
    const {dialog, deleteDialog, hideDialog} = useDialog();
    const violations = ref([]);
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
                return;
            }
            try {
                const res = await service.save(single.value);
                toast.add({ severity: 'success', summary: 'Successful', detail: 'Object Updated/Created', life: 3000 });
                single.value = service.getNew();
                data.value = await service.getAll();
                dialog.value = false;
            } catch (e) {
                violations.value = e.violations;
                toast.add({ severity: 'error', summary: 'Error', detail: e.message ?? e.detail ?? 'Generic error', life: 3000 });
            }
        },
        openNew() {
            single.value = service.getNew();
            dialog.value = true;
        },
        hideDialog,
        editData(data: T) {
            single.value = { ...data };
            dialog.value = true;
        },
        cloneData(data: T) {
            single.value = { ...data };
            delete single.value.id
            dialog.value = true;
        },
        confirmDelete(data: T) {
            single.value = data;
            deleteDialog.value = true;
        },
        isInvalid(field: string) {
            for (const violation of violations.value ?? []) {
                if (violation.propertyPath === field) {
                    return true;
                }
            }
            return false;
        },
        async deleteData() {
            if (undefined === single.value || null === single.value) {
                toast.add({ severity: 'error', summary: 'Error', detail: 'No data Provided', life: 3000 });
                return;
            }

            try {
                const res = await service.delete(single.value);
                toast.add({ severity: 'success', summary: 'Successful', detail: 'Object Deleted', life: 3000 });
                data.value = await service.getAll();
            } catch (e) {
                violations.value = e.violations;
                toast.add({ severity: 'error', summary: 'Error', detail: e.message ?? e.detail ?? 'Generic error', life: 3000 });
            }

            deleteDialog.value = false;
            single.value = service.getNew();
        }
    };
}
