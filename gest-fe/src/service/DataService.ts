import authService from "./AuthService";

interface DataService {
    post<T>(url: string, postData: object): Promise<T>
    put<T>(url: string, postData: object): Promise<T>
    get<T>(url: string): Promise<T>
    getHeaders(): HeadersInit
    token: string|null
}



const service: DataService = {
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
    async post<T>(url: string, postData: object): Promise<T> {
        const res: Response = await fetch(url, {
            method: 'POST',
            body: JSON.stringify(postData),
            headers: this.getHeaders()
        })
        const data: object = await res.json()
        if (!res.ok) {
            throw data as object
        }

        return data as T;
    },
    async put<T>(url: string, postData: object): Promise<T> {
        const res: Response = await fetch(url, {
            method: 'PUT',
            body: JSON.stringify(postData),
            headers: this.getHeaders()
        })
        const data: object = await res.json()
        if (!res.ok) {
            throw data as object
        }

        return data as T;
    },
    async get<T>(url: string): Promise<T> {
        const res: Response = await fetch(url, {
            method: 'GET',
            headers: this.getHeaders()
        })
        const data: object = await res.json()
        if (!res.ok) {
            console.log(data)
            if(data.hasOwnProperty('message') && 'Expired JWT Token' === data.message ){
                authService.logout()
            }
            throw data as object
        }

        return data as T;
    },
    token: null
}

export default service