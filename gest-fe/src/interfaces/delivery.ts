import type DeliveryProduct from "./deliveryProduct";
import type User from "./user"

export default interface Delivery {
    id?: number,
    weekDay: number,
    customer: User,
    activities: Array<any>,
    deliveryProducts: Array<DeliveryProduct>,
}
