import dataService from "./DataService";
import type User from '@/interfaces/user'

interface UserService {
    getUsers(): Promise<Array<User>>
}

const service: UserService = {
    async getUsers() {
        return await dataService.get(import.meta.env.VITE_API_URL + 'users');
    }
}

export default service