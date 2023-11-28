import type { Dayjs } from "dayjs";
import type DeliveryProduct from "./deliveryProduct";
import type User from "./user"

interface Delivery {
    id?: number,
    harvestWeekDay: number,
    deliveryWeekDay: number,
    deliveryDate: Dayjs,
    harvestDate: Dayjs,
    customer?: User,
    deliveryProducts: Array<DeliveryProduct>,
    notes: string,
    paymentMethod?: string,
    nextDelivery?: number
}
interface DeliveryWithoutProducts {
    id?: number,
    harvestWeekDay: number,
    deliveryWeekDay: number,
    deliveryDate: Dayjs,
    harvestDate: Dayjs,
    customer?: User,
    notes: string,
    paymentMethod?: string
}

export type {Delivery, DeliveryWithoutProducts}