import apiClient from './api.service'
import { API_ENDPOINTS, STORAGE_KEYS } from '@/config/api'

export const authService = {
  /**
   * Send OTP to WhatsApp number
   * @param {string} whatsapp - WhatsApp number (08xxx or 628xxx)
   * @returns {Promise}
   */
  async sendOTP(whatsapp) {
    return await apiClient.post(API_ENDPOINTS.AUTH.SEND_OTP, { whatsapp })
  },

  /**
   * Verify OTP and register new user
   * @param {Object} data - Registration data
   * @param {string} data.whatsapp - WhatsApp number
   * @param {string} data.otp - 6-digit OTP code
   * @param {string} data.name - User's full name
   * @param {string} data.password - User's password
   * @param {string} data.password_confirmation - Password confirmation
   * @returns {Promise}
   */
  async verifyOTP(data) {
    const response = await apiClient.post(API_ENDPOINTS.AUTH.VERIFY_OTP, data)
    if (response.success && response.data.token) {
      this.setAuthData(response.data.token, response.data.user)
    }
    return response
  },

  /**
   * Login with WhatsApp and password
   * @param {string} whatsapp - WhatsApp number
   * @param {string} password - User's password
   * @returns {Promise}
   */
  async login(whatsapp, password) {
    const response = await apiClient.post(API_ENDPOINTS.AUTH.LOGIN, {
      whatsapp,
      password,
    })
    if (response.success && response.data.token) {
      this.setAuthData(response.data.token, response.data.user)
    }
    return response
  },

  /**
   * Logout current user
   * @returns {Promise}
   */
  async logout() {
    try {
      await apiClient.post(API_ENDPOINTS.AUTH.LOGOUT)
    } finally {
      this.clearAuthData()
    }
  },

  /**
   * Get current authenticated user
   * @returns {Promise}
   */
  async getUser() {
    return await apiClient.get(API_ENDPOINTS.AUTH.GET_USER)
  },

  /**
   * Set authentication data in localStorage
   * @param {string} token - JWT token
   * @param {Object} user - User data
   */
  setAuthData(token, user) {
    localStorage.setItem(STORAGE_KEYS.AUTH_TOKEN, token)
    localStorage.setItem(STORAGE_KEYS.USER_DATA, JSON.stringify(user))
  },

  /**
   * Clear authentication data from localStorage
   */
  clearAuthData() {
    localStorage.removeItem(STORAGE_KEYS.AUTH_TOKEN)
    localStorage.removeItem(STORAGE_KEYS.USER_DATA)
  },

  /**
   * Check if user is authenticated
   * @returns {boolean}
   */
  isAuthenticated() {
    return !!localStorage.getItem(STORAGE_KEYS.AUTH_TOKEN)
  },

  /**
   * Get stored user data
   * @returns {Object|null}
   */
  getStoredUser() {
    const userData = localStorage.getItem(STORAGE_KEYS.USER_DATA)
    return userData ? JSON.parse(userData) : null
  },
}
