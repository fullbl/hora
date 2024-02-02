import dataService from './DataService';
import VueJwtDecode from 'vue-jwt-decode';
import type User from '../interfaces/user';

interface AuthService {
    user: null | User;
    load: () => boolean;
    login: (username: string, password: string) => Promise<boolean>;
    refresh: () => Promise<boolean>;
    isGranted: (role: string) => boolean;
    logout(): void;
}

const service: AuthService = {
    user: null,
    load() {
        const token = localStorage.getItem('token');
        if (null === token) {
            return false;
        }
        try {
            this.user = VueJwtDecode.decode(token);
        } catch (e) {
            return false;
        }

        dataService.token = token;
        return true;
    },
    logout() {
        localStorage.removeItem('token');
        this.user = null;
        window.location.href = '';
    },
    async login(username, password) {
        try {
            const data = await dataService.post<{ token: string; refresh_token: string }>(import.meta.env.VITE_API_URL + 'login', { username, password });
            if (data.token && data.refresh_token) {
                this.user = VueJwtDecode.decode(data.token);
                localStorage.setItem('token', data.token);
                localStorage.setItem('refresh_token', data.refresh_token);
                return true;
            } else {
                return false;
            }
        } catch (e) {
            return false;
        }
    },
    async refresh() {
        localStorage.removeItem('token');
        const data = await dataService.post<{ token: string; refresh_token: string }>(import.meta.env.VITE_API_URL + 'refresh_token', { refresh_token: localStorage.getItem('refresh_token') });
        if (data.token && data.refresh_token) {
            localStorage.setItem('token', data.token);
            localStorage.setItem('refresh_token', data.refresh_token);
            return true;
        } else {
            return false;
        }
    },
    isGranted(role) {
        if (null === this.user) {
            return false;
        }

        return this.user.roles.includes('ROLE_SUPER_ADMIN') || (this.user.roles.includes('ROLE_ADMIN') && 'ROLE_OPERATOR' === role) || this.user.roles.includes(role);
    }
};

export default service;
