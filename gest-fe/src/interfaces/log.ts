import type User from "./user";

export default interface Log {
    id?: number,
    user: User,
    entityClass: string,
    entityId: number,
    changes: {string: string[]},
    createdAt?: Date,
}
