import type Service from "@/interfaces/service";
import dataService from "./DataService";
import type Order from '@/interfaces/order'
import type Product from "@/interfaces/product";

const service: Service<Order> = {
    async delete(order) {
        await dataService.delete(import.meta.env.VITE_API_URL + 'orders/' + order.id);
        return true
    },
    async getAll() {
        return await dataService.get(import.meta.env.VITE_API_URL + 'orders');
    },
    async save(order) {
        try {
            if (order.id) {
                await dataService.put(import.meta.env.VITE_API_URL + 'orders/' + order.id, order);
            }
            else {
                await dataService.post(import.meta.env.VITE_API_URL + 'orders', order);
            }

            return true
        }
        catch (e) {
            return false
        }
    },
    getNew() {
        return {
            status: 'draft',
            product: {
                name: '',
                type: '',
                decigrams: 0
            } as Product,
            quantity: 0,
        }
    }
}

export default service