import type Zone from "./zone";

export default interface User {
    id?: number,
    roles: Array<string>,
    token?: string,
    password?: string,
    username: string,
    userIdentifier?: string,
    status: string,
    fullName: string,
    vatNumber: string,
    email?: string,
    address?: string,
    deliveries: Array<unknown>,
    suspensions: Array<unknown>,
    sdi?: string,
    zone?: string,
    subZone?: string,
    discount: number,
    zones: Array<Zone>
}
