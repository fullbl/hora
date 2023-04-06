import type Product from "./product"

export default interface DeliveryProduct {
    id?: number,
    product: Product,
    qty: number,
}
