import type Storage from "./storage";
export default interface Product {
    id?: number,
    name: string,
    storage: Storage,
    grams: number,
}
