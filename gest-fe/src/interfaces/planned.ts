import type Delivery from './delivery';
import type Product from './product';

export default interface Planned {
    stepName: string;
    qty: number;
    done: number;
    decigrams: number;
    product: Product;
    delivery: Delivery;
    minutesBeforeHarvest: number;
    date?: Date;
}
