import dataService from "./DataService";
import userService from "./UserService";
import type Delivery from '@/interfaces/delivery'

interface DeliveryService {
    getDeliverys(): Promise<Array<Delivery>>,
    getNewDelivery(): Delivery,
    save(delivery: Delivery): Promise<boolean>
}

const service: DeliveryService = {
    async getDeliverys() {
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
    getNewDelivery() {
        return {
            weekDay: 1,
            customer: userService.getNewUser(),
            deliveryProducts: [],
        }
    }
}

export default service