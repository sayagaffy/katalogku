// Support either VITE_API_BASE_URL (preferred) or VITE_API_URL from .env.
// If the value already includes "/api", we strip it and set prefix to empty
// to avoid double "/api/api" when building URLs.
const RAW_API_BASE =
  import.meta.env.VITE_API_BASE_URL || import.meta.env.VITE_API_URL || 'http://localhost:8000'

const HAS_API_SUFFIX = /\/api\/?$/.test(RAW_API_BASE)
export const API_BASE_URL = RAW_API_BASE.replace(/\/api\/?$/, '')
export const API_PREFIX =
  // Allow explicit override via env
  import.meta.env.VITE_API_PREFIX ?? (HAS_API_SUFFIX ? '/api' : '/api')

console.log('API Configuration:', {
  RAW_API_BASE,
  HAS_API_SUFFIX,
  API_BASE_URL,
  API_PREFIX,
  FULL_URL: `${API_BASE_URL}${API_PREFIX}/auth/login`
})

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
    TRACK_CLICK: (id) => `/clicks/${id}`,
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
