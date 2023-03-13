import dataService from './DataService'
import VueJwtDecode from 'vue-jwt-decode'


interface User {
    id: bigint,
    roles: Array<string>,
    token: string
}
interface AuthService {
    user: null | User
    load: () => boolean
    login: (username: string, password: string) => Promise<boolean>
    isGranted: (role: string) => boolean
}

const service: AuthService = {
    user: null,
    load() {
        const user = localStorage.getItem('user')
        if (null === user) {
            return false
        }
        try {
            this.user = JSON.parse(user)
        }
        catch (e) {
            return false
        }

        return true
    },
    async login(username, password) {
        let user: User;
        try {
            const data = await dataService.post<{ token: string }>(
                import.meta.env.VITE_API_URL + 'login',
                { username, password }
            )

            user = VueJwtDecode.decode(data.token)
        }
        catch (e) {
            return false
        }

        this.user = user;
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