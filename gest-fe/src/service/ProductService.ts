import type Service from "@/interfaces/service";
import dataService from "./DataService";
import type Product from '@/interfaces/product'

const service: Service<Product> = {
    async delete(product) {
        await dataService.delete(import.meta.env.VITE_API_URL + 'products/' + product.id);
        return true;
    },
    async getAll() {
        return await dataService.get(import.meta.env.VITE_API_URL + 'products');
    },
    async save(product) {
        try {
            if (product.id) {
                await dataService.put(import.meta.env.VITE_API_URL + 'products/' + product.id, product);
            }
            else {
                await dataService.post(import.meta.env.VITE_API_URL + 'products', product);
            }

            return true
        }
        catch (e) {
            return false
        }
    },
    getNew() {
        return {
            name: '',
            type: '',
            grams: 0,
        }
    }
}

export default service