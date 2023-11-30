import type Service from '@/interfaces/service';
import dataService from './DataService';
import userService from './UserService';
import type {Delivery} from '@/interfaces/delivery';
import dayjs from 'dayjs';

const service: Service<Delivery> = {
    async delete(delivery) {
        return await dataService.delete(import.meta.env.VITE_API_URL + 'deliveries/' + delivery.id);
    },
    async getAll() {
        return (await dataService.get<Delivery[]>(import.meta.env.VITE_API_URL + 'deliveries'))
            .map((delivery: Delivery) => {
                delivery.harvestDate = dayjs(delivery.harvestDate);
                delivery.deliveryDate = dayjs(delivery.deliveryDate);
                return delivery;
            });
    },
    async save(delivery) {
        const data = {
            ...delivery,
            deliveryDate: dayjs(delivery.deliveryDate).format('YYYY-MM-DD'),
            harvestDate: dayjs(delivery.harvestDate).format('YYYY-MM-DD'),
            deliveryDates: delivery.deliveryDates?.map((date) => dayjs(date).format('YYYY-MM-DD')),
            harvestDates: delivery.harvestDates?.map((date) => dayjs(date).format('YYYY-MM-DD')),
        }
        if (delivery.id) {
            return await dataService.put(import.meta.env.VITE_API_URL + 'deliveries/' + delivery.id, data);
        } else {
            return await dataService.post(import.meta.env.VITE_API_URL + 'deliveries', data);
        }
    },
    getNew() {
        return {
            harvestWeekDay: 0,
            deliveryWeekDay: 0,
            customer: userService.getNew(),
            deliveryProducts: [],
            harvestDate: dayjs(),
            deliveryDate: dayjs(),
            notes: '',
        };
    },
};

export default service;
