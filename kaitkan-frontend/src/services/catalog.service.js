import apiClient from './api.service'
import { API_ENDPOINTS } from '@/config/api'

export const catalogService = {
  /**
   * Get current user's catalog
   * @returns {Promise}
   */
  async getCatalog() {
    return await apiClient.get(API_ENDPOINTS.CATALOG.GET)
  },

  /**
   * Create or update catalog
   * @param {FormData} formData - Catalog data with optional avatar image
   * @returns {Promise}
   */
  async saveCatalog(formData) {
    return await apiClient.post(API_ENDPOINTS.CATALOG.GET, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
  },

  /**
   * Get public catalog by username
   * @param {string} username - Catalog username
   * @returns {Promise}
   */
  async getPublicCatalog(username) {
    return await apiClient.get(API_ENDPOINTS.CATALOG.PUBLIC(username))
  },
}
