import dataService from "./DataService";
import type Product from '@/interfaces/product'

interface ProductService {
    getProducts(): Promise<Array<Product>>,
    getNewProduct(): Product,
    save(product: Product): Promise<boolean>
}

const service: ProductService = {
    async getProducts() {
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
    getNewProduct() {
        return {
            name: '',
            type: '',
            grams: 0,
        }
    }
}

export default service