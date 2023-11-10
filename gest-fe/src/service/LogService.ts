import dataService from './DataService';
import type Log from '@/interfaces/log';

const service = {
    async getByEntity(entityName: string, entityId: number) {
        return await dataService.get<Log[]>(import.meta.env.VITE_API_URL + 'logs/' + encodeURIComponent(entityName) + '/' + entityId);
    }
};

export default service;
