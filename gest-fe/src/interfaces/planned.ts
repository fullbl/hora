import type Delivery from './delivery';
import type Product from './product';
import type Step from './step';

export default interface Planned {
    step: Step;
    qty: number;
    done: number;
    decigrams: number;
    product: Product;
    delivery: Delivery;
    minutesBeforeHarvest: number;
    date?: Date;
    harvestDate?: Date;
    deliveryDate?: Date;
}
