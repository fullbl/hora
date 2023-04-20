import type Service from "@/interfaces/service";
import dataService from "./DataService";
import userService from "./UserService";
import type Delivery from '@/interfaces/delivery'

const service: Service<Delivery> = {
    async delete(delivery) {
        await dataService.delete(import.meta.env.VITE_API_URL + 'deliveries/' + delivery.id, delivery);
        return true
    },
    async getAll() {
        return await dataService.get(import.meta.env.VITE_API_URL + 'deliveries');
    },
    async save(delivery) {
        try {
            if (delivery.id) {
                await dataService.put(import.meta.env.VITE_API_URL + 'deliveries/' + delivery.id, delivery);
            }
            else {
                await dataService.post(import.meta.env.VITE_API_URL + 'deliveries', delivery);
            }

            return true
        }
        catch (e) {
            return false
        }
    },
    getNew() {
        return {
            weekDay: 1,
            customer: userService.getNew(),
            deliveryProducts: [],
            weeks: [],
        }
    }
}

export default service