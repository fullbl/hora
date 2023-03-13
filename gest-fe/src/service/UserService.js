import dataService from "./DataService";

export default class UserService {
    getUsers() {
        return dataService.get(import.meta.env.VITE_API_URL + 'users');
    }
}
