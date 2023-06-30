import type Service from '@/interfaces/service';
import dataService from './DataService';
import type Product from '@/interfaces/product';

const service: Service<Product> = {
    async delete(product) {
        return await dataService.delete(import.meta.env.VITE_API_URL + 'products/' + product.id);
    },
    async getAll() {
        return await dataService.get(import.meta.env.VITE_API_URL + 'products');
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
            days: 0
        };
    }
};

export default service;
