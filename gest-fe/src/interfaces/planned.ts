import type Activity from './activity';
import type {Delivery} from './delivery';
import type Product from './product';
import type {Step} from './step';
import type { Dayjs } from 'dayjs';

export default interface Planned {
    step: Step;
    qty: number;
    done: number;
    decigrams: number;
    product: Product;
    delivery: Delivery;
    date: Dayjs;
    activities?: Activity[];
}
