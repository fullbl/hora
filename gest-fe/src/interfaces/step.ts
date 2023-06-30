import type Product from "./product";

export default interface Step {
    id?: number,
    product?: Product,
    name: string,
    minutes: number,
    params: object,
    sort: number,
}
