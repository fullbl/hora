import type Service from '@/interfaces/service';
import dataService from './DataService';
import userService from './UserService';
import type { Delivery } from '@/interfaces/delivery';
import dayjs from 'dayjs';

interface DeliveryService extends Service<Delivery> {
    getFrom(from: string): Promise<Array<Delivery>>;
    move(delivery: Delivery, deliveries: Array<{ delivery: number; deliveryProducts: Array<{ product: { id: number }, qty: number }> }>): Promise<Array<Delivery>>;
}

const service: DeliveryService = {
    async delete(delivery, reason) {
        return await dataService.delete(import.meta.env.VITE_API_URL + 'deliveries/' + delivery.id, {reason});
    },
    async getFrom(from: string) {
        return (await dataService.get<Delivery[]>(import.meta.env.VITE_API_URL + 'deliveries/' + from)).map((delivery: Delivery) => {
            delivery.harvestDate = dayjs(delivery.harvestDate);
            delivery.deliveryDate = dayjs(delivery.deliveryDate);
            return delivery;
        });
    },
    async getAll() {
        return (await dataService.get<Delivery[]>(import.meta.env.VITE_API_URL + 'deliveries')).map((delivery: Delivery) => {
            delivery.harvestDate = dayjs(delivery.harvestDate);
            delivery.deliveryDate = dayjs(delivery.deliveryDate);
            if(delivery.deletedAt){
                delivery.deletedAt = dayjs(delivery.deletedAt);
            }
            return delivery;
        });
    },
    async save(delivery) {
        const data = {
            ...delivery,
            deliveryDate: dayjs(delivery.deliveryDate).format('YYYY-MM-DD'),
            harvestDate: dayjs(delivery.harvestDate).format('YYYY-MM-DD'),
            deletedAt: delivery.deletedAt ? dayjs(delivery.deletedAt).format('YYYY-MM-DD') : null,
            deliveryDates: delivery.deliveryDates?.map((date) => dayjs(date).format('YYYY-MM-DD')),
            harvestDates: delivery.harvestDates?.map((date) => dayjs(date).format('YYYY-MM-DD'))
        };
        if (delivery.id) {
            return await dataService.put(import.meta.env.VITE_API_URL + 'deliveries/' + delivery.id, data);
        } else {
            return await dataService.post(import.meta.env.VITE_API_URL + 'deliveries', data);
        }
    },
    async move(delivery, deliveries) {
        return await dataService.put(import.meta.env.VITE_API_URL + 'deliveries/move', {delivery: delivery.id, deliveries});
    },
    getNew() {
        return {
            customer: userService.getNew(),
            deliveryProducts: [],
            harvestDate: dayjs(),
            deliveryDate: dayjs(),
            notes: ''
        };
    }
};

export default service;
