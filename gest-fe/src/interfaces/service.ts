export default interface Service<T> {
    getAll(): Promise<Array<T>>,
    getNew(): T,
    save(entity: T): Promise<boolean>,
    delete(entity: T): Promise<boolean>,
}