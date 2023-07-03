import type Delivery from "./delivery"
import type Product from "./product"

export default interface DeliveryProduct {
    id?: number,
    delivery: Delivery,
    product: Product,
    qty: number,
}
