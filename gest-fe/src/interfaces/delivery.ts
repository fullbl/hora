import type DeliveryProduct from "./deliveryProduct";
import type User from "./user"

export default interface Delivery {
    id?: number,
    harvestWeekDay: number,
    deliveryWeekDay: number,
    weeks: Array<number>,
    customer?: User,
    deliveryProducts: Array<DeliveryProduct>,
    notes: string,
    paymentMethod: string,
    price: number
}
