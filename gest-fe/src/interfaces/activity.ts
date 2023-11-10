import type { weekNumber, year } from "./dates";
import type {Delivery} from "./delivery";
import type Step from "./step";
import type User from "./user";

export default interface Activity {
    id?: number,
    delivery?: Delivery,
    year: year,
    week: weekNumber,
    step: Step,
    status: string,
    data?: {
        box?: number,
        script?: number,
    },
    qty: number,
    executer?: User,
    executionTime?: Date
}
