export default interface User {
    id: bigint,
    roles: Array<string>,
    token: string,
    username: string,
    userIdentifier: string,
    status: string,
    fullName: string,
    vatNumber: string,
    email: string,
    address: string,
    deliveries: Array<unknown>,
    suspensions: Array<unknown>
}
