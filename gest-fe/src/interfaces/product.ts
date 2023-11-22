import type Step from "./step";

export interface Product {
    id?: number,
    name: string,
    type: string,
    decigrams: number,
    days: number,
    price?: number,
    steps?: Step[],
    weight: boolean
}

export interface WaterBox extends Product {
    type: 'water-box',
}

export interface Seed extends Product {
    type: 'seed'
}

export interface Extra extends Product {
    type: 'extra'
}

export interface Sellable extends Product {
    type: 'seed' | 'extra'
}

export default Product