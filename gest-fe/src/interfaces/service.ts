export default interface Service<T> {
    getAll(): Promise<Array<T>>,
    getNew(): T,
    save(entity: T): Promise<T>,
    delete(entity: T, reason?: string): Promise<T>,
}