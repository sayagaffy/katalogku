export const API_BASE_URL = import.meta.env.VITE_API_BASE_URL || 'http://localhost:8000'
export const API_PREFIX = '/api'

export const API_ENDPOINTS = {
  // Authentication
  AUTH: {
    SEND_OTP: '/auth/send-otp',
    VERIFY_OTP: '/auth/verify-otp',
    LOGIN: '/auth/login',
    LOGOUT: '/auth/logout',
    GET_USER: '/auth/user',
  },
  // Catalog
  CATALOG: {
    GET: '/catalog',
    UPDATE: '/catalog',
    PUBLIC: (username) => `/c/${username}`,
  },
  // Products
  PRODUCTS: {
    LIST: '/products',
    CREATE: '/products',
    GET: (id) => `/products/${id}`,
    UPDATE: (id) => `/products/${id}`,
    DELETE: (id) => `/products/${id}`,
    REORDER: '/products/reorder',
  },
  // Analytics
  ANALYTICS: {
    OVERVIEW: '/analytics',
    CLICKS: (productId) => `/clicks/${productId}`,
  },
}

export const STORAGE_KEYS = {
  AUTH_TOKEN: 'katalogku_token',
  USER_DATA: 'katalogku_user',
}
