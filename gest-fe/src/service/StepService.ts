import dataService from './DataService';
import type Step from '@/interfaces/step';
import type Service from '@/interfaces/service';

interface StepService extends Service<Step> {
    getTypes(): Array<{ label: string; value: string }>;
}

const service: StepService = {
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
            name: 'soaking',
            minutes: 0,
            params: {},
            sort: 1
        };
    },
    getTypes() {
        return [
            { label: 'Soaking', value: 'soaking' },
            { label: 'Hot Soaking', value: 'hot_soaking' },
            { label: 'Preactivation', value: 'preactivation' },
            { label: 'Blackout', value: 'blackout' },
            { label: 'Light', value: 'light' },
        ];
    }
};

export default service;
