import type DeliveryProduct from "./deliveryProduct";
import type User from "./user"
import type {weekDay, weekNumber} from "@/interfaces/dates";

interface Delivery {
    id?: number,
    harvestWeekDay: weekDay,
    deliveryWeekDay: weekDay,
    weeks: Array<weekNumber>,
    customer?: User,
    deliveryProducts: Array<DeliveryProduct>,
    notes: string,
    paymentMethod?: string
}
interface DeliveryWithoutProducts {
    id?: number,
    harvestWeekDay: weekDay,
    deliveryWeekDay: weekDay,
    weeks: Array<weekNumber>,
    customer?: User,
    notes: string,
    paymentMethod?: string
}

export type {Delivery, DeliveryWithoutProducts}