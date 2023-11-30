import type { DeliveryWithoutProducts } from "./delivery"
import type Product from "./product"

export default interface DeliveryProduct {
    id?: number,
    delivery?: DeliveryWithoutProducts,
    product: Product,
    qty: number,
}
