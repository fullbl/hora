import authService from "./AuthService";

interface DataService {
    post<T>(url: string, postData: object): Promise<T>
    put<T>(url: string, postData: object): Promise<T>
    get<T>(url: string): Promise<T>
    delete<T>(url: string): Promise<T>
    token: string | null
}

const helper = {
    getHeaders() {
        const headers: HeadersInit = {
            "Content-Type": "application/json",
            "Accept": "application/json"
        }
        if (null !== localStorage.getItem('token')) {
            headers.Authorization = 'Bearer ' + localStorage.getItem('token')
        }
        return headers
    },
    async call<T>(url: string, method: string, postData?: object): Promise<T> {
        const options = {
            method: method,
            headers: this.getHeaders(),
            body: null as string | null
        };
        if (postData) {
            options.body = JSON.stringify(postData)
        }
        try {
            const res: Response = await fetch(url, options)
            const data = await res.json()
            if (!res.ok) {
                if (data.hasOwnProperty('message') && 'Expired JWT Token' === data.message) {
                    authService.logout()
                }
                throw data as object
            }
            return data as T;
        }
        catch (e) {
            if (e.hasOwnProperty('message') && 'NetworkError when attempting to fetch resource.' === e.message) {
                authService.logout()
            }
            throw e
        }
    }
}


const service: DataService = {
    async post<T>(url: string, postData: object): Promise<T> {
        return helper.call(url, 'POST', postData);
    },
    async put<T>(url: string, postData: object): Promise<T> {
        return helper.call(url, 'PUT', postData);
    },
    async get<T>(url: string): Promise<T> {
        return helper.call(url, 'GET');
    },
    async delete<T>(url: string): Promise<T> {
        return await helper.call(url, 'DELETE');
    },
    token: null
}

export default service