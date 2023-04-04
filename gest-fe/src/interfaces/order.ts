import type Product from "./product";
export default interface Order {
    id?: bigint,
    status: string,
    product: Product,
    quantity: number,
}
