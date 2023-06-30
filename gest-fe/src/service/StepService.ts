import dataService from './DataService';
import type Step from '@/interfaces/step';
import type Service from '@/interfaces/service';

interface StepService extends Service<Step> {
    getTypes(): Array<{ label: string; value: string }>;
    getIcon(type: string): string;
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
            name: '',
            minutes: 0,
            params: {},
            sort: 1
        };
    },
    getTypes() {
        return [
            { label: 'Soaking', value: 'soaking' },
            { label: 'Planting', value: 'planting' },
            { label: 'Blackout', value: 'blackout' },
            { label: 'Light', value: 'light' },
            { label: 'Shipping', value: 'shipping' },
            { label: 'Payment', value: 'payment' }
        ];
    },
    getIcon(type) {
        switch (type) {
            case 'soaking':
                return 'pi pi-arrow-circle-down';
            case 'planting':
                return 'pi pi-download';
            case 'light':
                return 'pi pi-sun';
            case 'dark':
                return 'pi pi-moon';
            case 'shipping':
                return 'pi pi-car';
            case 'payment':
                return 'pi pi-money-bill';
        }

        return '';
    }
};

export default service;
