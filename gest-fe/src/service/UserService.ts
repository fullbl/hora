import dataService from "./DataService";
import type User from '@/interfaces/user'

interface UserService {
    getUsers(): Promise<Array<User>>,
    getNewUser(): User,
    save(user: User): Promise<boolean>,
    delete(user: User): Promise<boolean>,
}

const service: UserService = {
    async getUsers() {
        return await dataService.get(import.meta.env.VITE_API_URL + 'users');
    },
    async delete(user) {
        return await dataService.delete(import.meta.env.VITE_API_URL + 'users/' + user.id);
    },
    async save(user) {
        try {
            if (user.id) {
                await dataService.put(import.meta.env.VITE_API_URL + 'users/' + user.id, user);
            }
            else {
                await dataService.post(import.meta.env.VITE_API_URL + 'users', user);
            }

            return true
        }
        catch (e) {
            return false
        }
    },
    getNewUser() {
        return {
            roles: [],
            username: '',
            status: 'inactive',
            fullName: '',
            vatNumber: '',
            email: '',
            address: '',
            deliveries: [],
            suspensions: []
        }
    }
}

export default service