import dataService from './DataService';
import type User from '@/interfaces/user';
import type Service from '@/interfaces/service';

const service: Service<User> = {
    async getAll() {
        return await dataService.get(import.meta.env.VITE_API_URL + 'users');
    },
    async delete(user) {
        return await dataService.delete(import.meta.env.VITE_API_URL + 'users/' + user.id);
    },
    async save(user) {
        if (user.id) {
            return await dataService.put(import.meta.env.VITE_API_URL + 'users/' + user.id, user);
        } else {
            return await dataService.post(import.meta.env.VITE_API_URL + 'users', user);
        }
    },
    getNew() {
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
        };
    }
};

export default service;
