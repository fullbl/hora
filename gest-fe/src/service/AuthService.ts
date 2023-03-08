import dataService from './DataService'

interface User {
    id: bigint;
}

const service: { user: null|User, login: (userrname: string, password: string) => Promise<boolean> } = {
    user: null,
    async login(username: string, password: string): Promise<boolean> {
        let data: User;
        try {
            data = await dataService.post<User>(
                import.meta.env.BASE_URL + 'login',
                { username, password }
            )
        }
        catch (e) {
            return false
        }

        this.user = data;
        return true
    }
}

export default service