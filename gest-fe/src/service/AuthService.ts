import dataService from './DataService'
import VueJwtDecode from 'vue-jwt-decode'
import type User from '../interfaces/user'


interface AuthService {
    user: null | User
    load: () => boolean
    login: (username: string, password: string) => Promise<boolean>
    isGranted: (role: string) => boolean,
    logout(): void
}

const service: AuthService = {
    user: null,
    load() {
        const token = localStorage.getItem('token')
        if (null === token) {
            return false
        }
        try {
            this.user = VueJwtDecode.decode(token)
        }
        catch (e) {
            return false
        }

        dataService.token = token
        return true
    },
    logout() {
        localStorage.removeItem('token')
        this.user = null

    },
    async login(username, password) {
        try {
            const data = await dataService.post<{ token: string }>(
                import.meta.env.VITE_API_URL + 'login',
                { username, password }
            )

            this.user = VueJwtDecode.decode(data.token)
            localStorage.setItem('token', data.token)
        }
        catch (e) {
            return false
        }

        return true
    },
    isGranted(role) {
        if (null === this.user) {
            return false
        }

        return this.user.roles.includes('ROLE_ADMIN') || this.user.roles.includes(role)
    }
}

export default service