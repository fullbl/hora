import type Delivery from "./delivery";
import type Product from "./product";
import type Step from "./step";
import type User from "./user";

export default interface Activity {
    id?: number,
    delivery?: Delivery,
    year: number,
    week: number,
    step: Step,
    status: string,
    qty: number,
    executer?: User,
    executionTime?: Date
}
