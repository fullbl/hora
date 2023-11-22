import type Service from '@/interfaces/service';
import dataService from './DataService';
import type Product from '@/interfaces/product';
import type { Seed, Extra, WaterBox, Sellable } from '@/interfaces/product';


interface ProductService extends Service<Product> {
    getSeeds(): Promise<Seed[]>,
    getSellable(): Promise<Sellable[]>,
    getWaterBoxes(): Promise<WaterBox[]>
}
const service: ProductService = {
    async delete(product) {
        return await dataService.delete(import.meta.env.VITE_API_URL + 'products/' + product.id);
    },
    async getAll() {
        return await dataService.get(import.meta.env.VITE_API_URL + 'products');
    },
    async getSeeds() {
        return await dataService.get<Seed[]>(import.meta.env.VITE_API_URL + 'seeds');
    },
    async getSellable() {
        return await dataService.get<Sellable[]>(import.meta.env.VITE_API_URL + 'sellable');
    },
    async getWaterBoxes() {
        return await dataService.get<WaterBox[]>(import.meta.env.VITE_API_URL + 'water_boxes');
    },
    async save(product) {
        if(product.steps){
            product.steps = product.steps.map(function(s,i){
                s.sort = i;
                return s;
            });
        }
        if (product.id) {
            return await dataService.put(import.meta.env.VITE_API_URL + 'products/' + product.id, product);
        } else {
            return await dataService.post(import.meta.env.VITE_API_URL + 'products', product);
        }
    },
    getNew() {
        return {
            name: '',
            type: '',
            decigrams: 0,
            days: 0,
            weight: false
        };
    }
};

export default service;
