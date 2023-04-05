import dataService from "./DataService";
import type Storage from '@/interfaces/storage'

interface StorageService {
    getStorages(): Promise<Array<Storage>>,
    getNewStorage(): Storage,
    save(storage: Storage): Promise<boolean>
}

const service: StorageService = {
    async getStorages() {
        return await dataService.get(import.meta.env.VITE_API_URL + 'storages');
    },
    async save(storage) {
        try {
            if (storage.id) {
                await dataService.put(import.meta.env.VITE_API_URL + 'storages/' + storage.id, storage);
            }
            else {
                await dataService.post(import.meta.env.VITE_API_URL + 'storages', storage);
            }

            return true
        }
        catch (e) {
            return false
        }
    },
    getNewStorage() {
        return {
            type: '',
            grams: 0,
        }
    }
}

export default service