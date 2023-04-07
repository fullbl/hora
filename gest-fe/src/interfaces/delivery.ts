import type DeliveryProduct from "./deliveryProduct";
import type User from "./user"

export default interface Delivery {
    id?: number,
    weekDay: number,
    weeks: Array<number>,
    customer: User,
    deliveryProducts: Array<DeliveryProduct>,
}
