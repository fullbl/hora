import type { Dayjs } from "dayjs";
import type DeliveryProduct from "./deliveryProduct";
import type User from "./user"

interface Delivery {
    id?: number,
    deliveryDate: Dayjs,
    deliveryDates?: Date[],
    harvestDate: Dayjs,
    harvestDates?: Date[],
    customer?: User,
    warning?: boolean,
    lastWarning?: boolean,
    deliveryProducts: Array<DeliveryProduct>,
    notes: string,
    paymentMethod?: string,
    nextDelivery?: number,
    deletedAt?: Dayjs,
    deletedReason?: string
}
interface DeliveryWithoutProducts {
    id?: number,
    deliveryDate: Dayjs,
    harvestDate: Dayjs,
    customer?: User,
    notes: string,
    paymentMethod?: string
}

export type {Delivery, DeliveryWithoutProducts}