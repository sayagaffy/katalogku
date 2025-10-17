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
   * Record page view (public)
   */
  async visit(catalogId, utm = {}) {
    return await apiClient.post('/analytics/visit', { catalog_id: catalogId, ...utm })
  },

  /**
   * Get summary (views, clicks, ctr) with daily series
   */
  async getSummary(range = '7d') {
    return await apiClient.get(`/analytics/summary?range=${encodeURIComponent(range)}`)
  },

  async getTopLinks(range = '7d') {
    return await apiClient.get(`/analytics/top-links?range=${encodeURIComponent(range)}`)
  },

  async getTopProducts(range = '7d') {
    return await apiClient.get(`/analytics/top-products?range=${encodeURIComponent(range)}`)
  },

  async exportSummary(range = '7d') {
    const res = await apiClient.get(`/analytics/export/summary?range=${encodeURIComponent(range)}`, { responseType: 'blob' })
    return res
  },
  async exportTopLinks(range = '7d') {
    return await apiClient.get(`/analytics/export/top-links?range=${encodeURIComponent(range)}`, { responseType: 'blob' })
  },
  async exportTopProducts(range = '7d') {
    return await apiClient.get(`/analytics/export/top-products?range=${encodeURIComponent(range)}`, { responseType: 'blob' })
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
