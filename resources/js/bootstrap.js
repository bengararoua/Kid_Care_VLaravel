import axios from 'axios'

window.axios = axios
window.axios.defaults.headers.common['X-Requested-With'] = 'XMLHttpRequest'
window.axios.defaults.baseURL = 'http://127.0.0.1:8000'

const token = localStorage.getItem('token')
if (token) {
    window.axios.defaults.headers.common['Authorization'] = `Bearer ${token}`
}

const user = localStorage.getItem('user')
if (user) {
    console.log('User loaded in bootstrap:', JSON.parse(user))
}

// Désactiver Echo complètement
window.Echo = null

window.axios.interceptors.response.use(
    response => response,
    error => {
        if (error.response?.status === 401) {
            localStorage.removeItem('token')
            localStorage.removeItem('user')
            delete window.axios.defaults.headers.common['Authorization']
            window.location.href = '/'
        }
        return Promise.reject(error)
    }
)