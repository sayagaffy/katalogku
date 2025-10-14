import axios from 'axios'
import { API_BASE_URL, API_PREFIX, STORAGE_KEYS } from '@/config/api'

// Create axios instance
const apiClient = axios.create({
  baseURL: `${API_BASE_URL}${API_PREFIX}`,
  headers: {
    'Content-Type': 'application/json',
    Accept: 'application/json',
  },
  timeout: 30000,
})

// Request interceptor - add auth token
apiClient.interceptors.request.use(
  (config) => {
    const token = localStorage.getItem(STORAGE_KEYS.AUTH_TOKEN)
    if (token) {
      config.headers.Authorization = `Bearer ${token}`
    }
    return config
  },
  (error) => {
    return Promise.reject(error)
  },
)

// Response interceptor - handle errors
apiClient.interceptors.response.use(
  (response) => {
    return response.data
  },
  (error) => {
    if (error.response) {
      // Server responded with error
      const { status, data } = error.response

      // Handle 401 Unauthorized - redirect to login
      if (status === 401) {
        localStorage.removeItem(STORAGE_KEYS.AUTH_TOKEN)
        localStorage.removeItem(STORAGE_KEYS.USER_DATA)
        window.location.href = '/login'
      }

      // Return error message from server
      return Promise.reject(data?.message || 'Terjadi kesalahan pada server')
    } else if (error.request) {
      // Request made but no response
      return Promise.reject('Tidak dapat terhubung ke server')
    } else {
      // Something else happened
      return Promise.reject(error.message || 'Terjadi kesalahan')
    }
  },
)

export default apiClient
