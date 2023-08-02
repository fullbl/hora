import type Product from "./product";

export default interface Step {
    id?: number,
    product?: Product,
    name: 'soaking'|'preactivation'|'light'|'blackout',
    minutes: number,
    params: object,
    sort: number,
}
