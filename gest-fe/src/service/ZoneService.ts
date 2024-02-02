import dataService from './DataService';
import type Zone from '@/interfaces/zone';
import type Service from '@/interfaces/service';

const service: Service<Zone> = {
    async getAll() {
        return await dataService.get(import.meta.env.VITE_API_URL + 'zones');
    },
    async delete(user) {
        return await dataService.delete(import.meta.env.VITE_API_URL + 'zones/' + user.id);
    },
    async save(user) {
        if (user.id) {
            return await dataService.put(import.meta.env.VITE_API_URL + 'zones/' + user.id, user);
        } else {
            return await dataService.post(import.meta.env.VITE_API_URL + 'zones', user);
        }
    },
    getNew() {
        return {
            name: '',
        };
    }
};

export default service;
