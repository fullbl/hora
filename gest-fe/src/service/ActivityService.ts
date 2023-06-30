import dataService from './DataService';
import stepService from './StepService';
import type Activity from '@/interfaces/activity';
import type Service from '@/interfaces/service';
import { useDates } from '@/views/composables/dates';

const { getWeekNumber } = useDates();

const service: Service<Activity> = {
    async getAll() {
        return await dataService.get(import.meta.env.VITE_API_URL + 'activitys');
    },
    async delete(activity) {
        return await dataService.delete(import.meta.env.VITE_API_URL + 'activitys/' + activity.id);
    },
    async save(activity) {
        if (activity.id) {
            return await dataService.put(import.meta.env.VITE_API_URL + 'activitys/' + activity.id, activity);
        } else {
            return await dataService.post(import.meta.env.VITE_API_URL + 'activitys', activity);
        }
    },
    getNew() {
        const today = new Date();
        return {
            year: today.getFullYear(),
            week: getWeekNumber(today),
            status: 'planned',
            step: stepService.getNew(),
            data: {}
        };
    }
};

export default service;
