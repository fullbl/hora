import dataService from './DataService';
import type Step from '@/interfaces/step';
import type Service from '@/interfaces/service';

const service: Service<Step> = {
    async getAll() {
        return await dataService.get(import.meta.env.VITE_API_URL + 'steps');
    },
    async delete(step) {
        return await dataService.delete(import.meta.env.VITE_API_URL + 'steps/' + step.id);
    },
    async save(step) {
        if (step.id) {
            return await dataService.put(import.meta.env.VITE_API_URL + 'steps/' + step.id, step);
        } else {
            return await dataService.post(import.meta.env.VITE_API_URL + 'steps', step);
        }
    },
    getNew() {
        return {
            name: '',
            minutes: 0,
            params: {},
            sort: 1,
        };
    }
};

export default service;
