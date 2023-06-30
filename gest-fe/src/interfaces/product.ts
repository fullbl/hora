import type Step from "./step";

export default interface Product {
    id?: number,
    name: string,
    type: string,
    decigrams: number,
    days: number,
    price?: number,
    steps?: Step[]
}
