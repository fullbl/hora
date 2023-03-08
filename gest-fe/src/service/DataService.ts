
export default {
    async post<T>(url: string, postData: object): Promise<T> {
        const res: Response = await fetch(url, {
            method: 'POST',
            body: JSON.stringify(postData)
        })
        const data: object = await res.json()   
        if(!res.ok){
            throw data as object
        }

        return data as T;
    }
}
