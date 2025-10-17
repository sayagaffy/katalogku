import apiClient from './api.service'
import { API_ENDPOINTS } from '@/config/api'

export const profileService = {
  /**
   * Get current user's profile (catalog)
   */
  async getProfile() {
    return await apiClient.get(API_ENDPOINTS.PROFILE.SHOW)
  },

  /**
   * Update profile fields (JSON PATCH)
   * @param {Object} data
   */
  async updateProfile(data) {
    return await apiClient.patch(API_ENDPOINTS.PROFILE.UPDATE, data)
  },

  /**
   * Upload or replace avatar (multipart)
   * @param {File} file
   */
  async uploadAvatar(file) {
    const formData = new FormData()
    formData.append('avatar', file)
    return await apiClient.post(API_ENDPOINTS.PROFILE.AVATAR, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
  },

  /**
   * Onboarding minimal fields
   * @param {{name?:string,username?:string}} data
   */
  async onboarding(data) {
    return await apiClient.post(API_ENDPOINTS.PROFILE.ONBOARDING, data)
  },

  /**
   * Upload background image (multipart)
   * @param {File} file
   */
  async uploadBackground(file) {
    const formData = new FormData()
    formData.append('background', file)
    return await apiClient.post(API_ENDPOINTS.PROFILE.BACKGROUND, formData, {
      headers: { 'Content-Type': 'multipart/form-data' },
    })
  },
}
