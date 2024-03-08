<script setup lang="ts">
import Button from 'primevue/button';
import type { Delivery } from '@/interfaces/delivery';
import { type PropType, ref, onMounted } from 'vue';
import type DeliveryProduct from '@/interfaces/deliveryProduct';
import deliveryService from '@/service/DeliveryService';
type DeliveryProductMoveable = DeliveryProduct & {
    moveQty: number;
};

const emits = defineEmits(['move']);

const props = defineProps({
    single: {
        type: Object as PropType<Delivery>,
        required: true
    },
    freeDeliveries: {
        type: Array as PropType<Array<Delivery>>,
        required: true
    }
});

const freeDPs = ref([] as Array<DeliveryProductMoveable>);
const singleDPs = ref([] as Array<DeliveryProductMoveable>);
onMounted(() => {
    freeDPs.value = props.freeDeliveries.reduce((x: Array<DeliveryProductMoveable>, d) => x.concat(d.deliveryProducts.map((dp) => ({ ...dp, moveQty: 0, delivery: d }))), []);
    singleDPs.value = props.single.deliveryProducts.map((dp) => ({ ...dp, moveQty: 0, delivery: props.single }));
});

const move = (deliveryProduct: DeliveryProductMoveable, destination: DeliveryProductMoveable[], delivery: Delivery | null = null) => {
    const destinationDP = destination.filter((dp) => dp.product.id === deliveryProduct.product.id);
    if (destinationDP.length > 0) {
        destinationDP[0].qty += deliveryProduct.moveQty;
    } else {
        const dp = { ...deliveryProduct, delivery: delivery, qty: deliveryProduct.moveQty };
        destination.push(dp);
    }

    deliveryProduct.qty -= deliveryProduct.moveQty;
    deliveryProduct.moveQty = 0;

};

const moveRight = (deliveryProduct: DeliveryProductMoveable) => {
    move(deliveryProduct, singleDPs.value, props.single);
};

const moveLeft = (deliveryProduct: DeliveryProductMoveable) => {
    let delivery = props.freeDeliveries.find((d) => d.deliveryProducts.some((dp) => dp.id === deliveryProduct.id));
    if (undefined === delivery) {
        delivery = props.freeDeliveries[0];
    };
    move(deliveryProduct, freeDPs.value, delivery);
};

const save = () => {
    const deliveries = new Map();
    for (const dp of [...freeDPs.value, ...singleDPs.value]) {
        const key = dp.delivery?.id ?? 0;
        if (!deliveries.has(key)) deliveries.set(key, { delivery: key, deliveryProducts: [] });
        const delivery = deliveries.get(key);
        if(dp.qty > 0){
            delivery.deliveryProducts.push({
                product: {id: dp.product.id},
                qty: dp.qty
            });
        }
    }
    deliveryService.move(props.single, Array.from(deliveries.values()));
    
    emits('move');
};

defineExpose({
    save
});
</script>

<template>
    <div class="flex overflow-hidden delivery-change">
        <div class="overflow-y-auto">
            <label for="free_products">Free Products</label>
            <DataTable :value="freeDPs" sortField="product.name" :sortOrder="1">
                <Column field="product.name" header="Product" sortable> </Column>
                <Column field="product.type" header="Type" sortable />
                <Column field="qty" header="Quantity" sortable />
                <Column header="Qt to">
                    <template #body="slotProps">
                        <InputNumber showButtons v-model="slotProps.data.moveQty" :min="1" :max="slotProps.data.qty" />
                    </template>
                </Column>
                <Column header="move">
                    <template #body="slotProps">
                        <Button icon="pi pi-arrow-right" class="p-button-rounded p-button-warning mt-2" @click="moveRight(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>

        <div class="overflow-y-auto">
            <label for="products">Products</label>
            <DataTable :value="singleDPs" sortField="product.name" :sortOrder="1">
                <Column field="product.name" header="Product" sortable />
                <Column field="product.type" header="Type" sortable />
                <Column field="qty" header="Quantity" sortable />
                <Column header="Qt to">
                    <template #body="slotProps">
                        <InputNumber v-model="slotProps.data.moveQty" :min="1" :max="slotProps.data.qty" showButtons />
                    </template>
                </Column>
                <Column header="move">
                    <template #body="slotProps">
                        <Button icon="pi pi-arrow-left" class="p-button-rounded p-button-warning mt-2" @click="moveLeft(slotProps.data)" />
                    </template>
                </Column>
            </DataTable>
        </div>
    </div>
</template>

<style scoped lang="scss">
.delivery-change {
    height: 65vh;
}
</style>