import axios from 'axios'
import { API_BASE_URL, API_PREFIX, STORAGE_KEYS } from '@/config/api'
import router from '@/router'

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

      // Log full error response for debugging
      console.error('API Error Response:', {
        status,
        data,
        url: error.config?.url,
        method: error.config?.method,
      })

      // Handle 401 Unauthorized - redirect to login (SPA-safe)
      if (status === 401) {
        localStorage.removeItem(STORAGE_KEYS.AUTH_TOKEN)
        localStorage.removeItem(STORAGE_KEYS.USER_DATA)

        // Avoid redirect loops when already on auth pages or when the 401
        // came from an auth endpoint (e.g., invalid login showing 401)
        const currentPath = window.location?.pathname || ''
        const reqUrl = (error.config?.url || '').toString()
        const isAuthEndpoint = /^(\/)?auth\//.test(reqUrl.replace(/^\//, ''))
        const isOnAuthPage = currentPath === '/login' || currentPath === '/register' || currentPath === '/verify-otp'

        if (!isAuthEndpoint && !isOnAuthPage) {
          try {
            const to = '/login'
            // Prefer SPA navigation to avoid full reload flicker
            if (router && router.currentRoute?.value?.path !== to) {
              router.push(to)
            } else if (currentPath !== to) {
              window.location.href = to
            }
          } catch (_) {
            // Fallback to hard redirect if router not available
            if (currentPath !== '/login') {
              window.location.href = '/login'
            }
          }
        }
      }

      // For validation errors (422), return full error object
      if (status === 422 && data?.errors) {
        const errorObj = {
          message: data.message || 'Validasi gagal',
          errors: data.errors,
        }
        return Promise.reject(errorObj)
      }

      // For 500 errors with debug info, include error details
      if (status === 500 && data?.error) {
        return Promise.reject(data.message + ': ' + data.error)
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
