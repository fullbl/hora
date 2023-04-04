import dataService from "./DataService";
import type Order from '@/interfaces/order'

interface OrderService {
    getOrders(): Promise<Array<Order>>,
    getNewOrder(): Order,
    save(order: Order): Promise<boolean>
}

const service: OrderService = {
    async getOrders() {
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
    getNewOrder() {
        return {
            status: 'draft',
            product: {
                name: '',
                type: '',
                grams: 0
            },
            quantity: 0,
        }
    }
}

export default service