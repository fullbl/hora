<script setup lang="ts">
import { onMounted, ref } from 'vue';
import productService from '@/service/ProductService';
import type Product from '@/interfaces/product';

const productGroups = ref<{[key:string]: Array<Product>}>({});

onMounted(async () => {
    productGroups.value = (await productService.getAll())
        .reduce(function (x, p) {
            if(!x.hasOwnProperty(p.type)){
                x[p.type] = [];
            }
            x[p.type].push(p);

            return x;
        }, {});
});
</script>

<template>
    <div class="grid">
        <div class="col-12 xl:col-6" v-for="products, group of productGroups">
            <div class="card">
                <div class="flex justify-content-between align-items-center mb-5">
                    <h5>{{ group }}</h5>
                </div>
                <ul class="list-none p-0 m-0">
                    <li class="flex flex-column md:flex-row md:align-items-center md:justify-content-between mb-4" v-for="product of products">
                        <div>
                            <span class="text-900 font-medium mr-2 mb-1 md:mb-0">{{product.name}}</span>
                        </div>
                        <div class="mt-2 md:mt-0 flex align-items-center">
                            <div class="surface-300 border-round overflow-hidden w-10rem lg:w-6rem" style="height: 8px">
                                <div class="bg-orange-500 h-full" style="width: 50%"></div>
                            </div>
                            <span class="text-orange-500 ml-3 font-medium">{{ product.grams }}</span>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>
</template>
