import type Product from "./product";

type StepName = 'soaking' | 'hot_soaking' | 'preactivation' | 'light' | 'blackout'

interface Step {
    id?: number,
    product?: Product,
    name: StepName,
    minutes: number,
    params: object,
    sort: number,
}

export type { StepName, Step };