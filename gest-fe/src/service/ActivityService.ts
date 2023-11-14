import dayjs from 'dayjs';
import dataService from './DataService';
import stepService from './StepService';
import type Activity from '@/interfaces/activity';
import type Service from '@/interfaces/service';

interface ActivityService extends Service<Activity>{
    saveBatch(activities: Activity[]): Promise<Activity[]>;
}
const service: ActivityService = {
    async getAll() {
        return await dataService.get(import.meta.env.VITE_API_URL + 'activities');
    },
    async delete(activity) {
        return await dataService.delete(import.meta.env.VITE_API_URL + 'activities/' + activity.id);
    },
    async save(activity) {
        if (activity.id) {
            return await dataService.put(import.meta.env.VITE_API_URL + 'activities/' + activity.id, activity);
        } else {
            return await dataService.post(import.meta.env.VITE_API_URL + 'activities', activity);
        }
        
    },
    async saveBatch(activities: Activity[]) {
        try{
            const promise = activities.map((a: Activity) => dataService.post<Activity>(import.meta.env.VITE_API_URL + 'activities', a));
            return await Promise.all(promise);
        }
        catch(e){
            throw e;
        }
    },
    getNew() {
        const today = dayjs();
        return {
            year: today.year(),
            week: today.week(),
            status: 'planned',
            step: stepService.getNew(),
            qty: 0,
        };
    }
};

export default service;
