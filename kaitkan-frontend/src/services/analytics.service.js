import apiClient from './api.service'
import { API_ENDPOINTS } from '@/config/api'

export const analyticsService = {
  /**
   * Get analytics overview
   * @returns {Promise}
   */
  async getOverview() {
    return await apiClient.get(API_ENDPOINTS.ANALYTICS.OVERVIEW)
  },

  /**
   * Track product click
   * @param {number} productId - Product ID
   * @returns {Promise}
   */
  async trackClick(productId) {
    return await apiClient.post(`/clicks/${productId}`)
  },
}
