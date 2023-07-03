<script setup lang="ts">
import type Activity from '@/interfaces/activity';
import type Product from '@/interfaces/product';
import productService from '@/service/ProductService';
import activityService from '@/service/ActivityService';
import stepService from '@/service/StepService';
import type Dropdown from 'primevue/dropdown';
import { computed, onMounted, ref, type PropType } from "vue";
import { useDialog } from '@/views/composables/dialog';
import { useToast } from 'primevue/usetoast';
import type Delivery from '@/interfaces/delivery';

const toast = useToast();
const {dialog, hideDialog} = useDialog();


const props = defineProps({
    type: {
        required: true,
        type: String,
        validator(value: string) {
            return stepService.getTypes().map((t: { label: string, value: string }) => t.value).includes(value)
        }
    },
    baseProducts: {
        required: false,
        default: [],
        type: Array<Product>,
    },
    year: {
        required: true,
        type: Number,
    },
    week: {
        required: true,
        type: Number,
    },
    delivery: {
        required: true,
        type: Object as PropType<Delivery>,
    },
})

const icon = computed(() => stepService.getIcon(props.type))

const activities = ref([] as Activity[]);
const allProducts = ref([] as Product[]);
const products = ref([] as Product[]);
onMounted(async () => {
    allProducts.value = await productService.getAll()
    products.value = props.baseProducts
});

const onClick = function() {
    activities.value = products.value.map(function (product: Product): Activity {
        const fullProducts = allProducts.value.filter((p: Product) => p.id === product.id);
        if(!fullProducts.length){
            toast.add({severity: 'error', summary: 'Error', detail: 'Product not found'});
            throw new Error('product not found');
        }

        product.steps = fullProducts[0].steps;
        const step = fullProducts[0].steps?.filter(s => props.type === s.name)[0];
        if (undefined === step) {
            toast.add({severity: 'error', summary: 'Error', detail: 'Undefined step'});
            throw Error('undefined step');
        }

        step.product = {...fullProducts[0]};
        delete step.product.steps;

        return {
            delivery: props.delivery,
            year: props.year,
            week: props.week,
            status: 'done',
            step,
            qty: 0,
        }
    });
    dialog.value = true
}

const add = function () {
    activities.value.push(activityService.getNew());
}

const remove = function (activity: Activity) {
    activities.value = activities.value.filter((a) => a !== activity )
}

const save = async function () {
    try {
        await activityService.saveBatch(activities.value);
        toast.add({ severity: 'success', summary: 'Successful', detail: 'Activity created', life: 3000 });
        hideDialog();
    } catch (e) {
        toast.add({ severity: 'error', summary: 'Error', detail: e.message ?? e.detail ?? 'Generic error', life: 3000 });
    }
}

</script>

<template>
    <Button :icon="icon" @click="onClick" />
    <Dialog v-model:visible="dialog" :style="{ width: '450px' }" :header="type" :modal="true" class="p-fluid">
        <Button label="Add" icon="pi pi-plus" @click="add" />
        <div v-for="activity in activities" class="flex justify-content-between">
            <div class="field">
                <label for="product">Product</label>
                <Dropdown id="name" v-model="activity.step.product" required="true" autofocus :options="allProducts"
                    dataKey="id" optionLabel="name" />
            </div>

            <div class="field">
                <label for="qty">Qty</label>
                <InputNumber type="number" id="decigrams" v-model="activity.qty" required autofocus showButtons />
            </div>

            <Button icon="pi pi-trash" severity="danger" @click="remove(activity)" />
        </div>
        <template #footer>
            <Button label="Cancel" icon="pi pi-times" class="p-button-text" @click="hideDialog" />
            <Button label="Save" icon="pi pi-check" class="p-button-text" @click="save" />
        </template>
    </Dialog>
</template>