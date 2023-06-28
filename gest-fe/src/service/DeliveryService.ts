import type Service from '@/interfaces/service';
import dataService from './DataService';
import userService from './UserService';
import type Delivery from '@/interfaces/delivery';

const service: Service<Delivery> = {
    async delete(delivery) {
        return await dataService.delete(import.meta.env.VITE_API_URL + 'deliveries/' + delivery.id);
    },
    async getAll() {
        return await dataService.get(import.meta.env.VITE_API_URL + 'deliveries');
    },
    async save(delivery) {
        return await dataService.put(import.meta.env.VITE_API_URL + 'deliveries/' + delivery.id, delivery);
    },
    getNew() {
        return {
            harvestWeekDay: 1,
            deliveryWeekDay: 1,
            customer: userService.getNew(),
            deliveryProducts: [],
            weeks: [],
            notes: '',
            paymentMethod: 'monthly',
            price: 1
        };
    }
};

export default service;
