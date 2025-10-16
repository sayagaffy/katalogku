import apiClient from './api.service'
import { API_ENDPOINTS } from '@/config/api'

export const productService = {
  /**
   * Get all products for current user
   * @returns {Promise}
   */
  async getProducts() {
    return await apiClient.get(API_ENDPOINTS.PRODUCTS.LIST)
  },

  /**
   * Get single product by ID
   * @param {number} id - Product ID
   * @returns {Promise}
   */
  async getProduct(id) {
    return await apiClient.get(API_ENDPOINTS.PRODUCTS.GET(id))
  },

  /**
   * Create new product
   * @param {FormData} formData - Product data with image
   * @returns {Promise}
   */
  async createProduct(formData) {
    return await apiClient.post(API_ENDPOINTS.PRODUCTS.CREATE, formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
  },

  /**
   * Update existing product
   * @param {number} id - Product ID
   * @param {FormData} formData - Product data with optional image
   * @returns {Promise}
   */
  async updateProduct(id, formData) {
    return await apiClient.post(API_ENDPOINTS.PRODUCTS.UPDATE(id), formData, {
      headers: {
        'Content-Type': 'multipart/form-data',
      },
    })
  },

  /**
   * Delete product
   * @param {number} id - Product ID
   * @returns {Promise}
   */
  async deleteProduct(id) {
    return await apiClient.delete(API_ENDPOINTS.PRODUCTS.DELETE(id))
  },

  /**
   * Reorder products
   * @param {Array} products - Array of {id, sort_order}
   * @returns {Promise}
   */
  async reorderProducts(products) {
    return await apiClient.post(API_ENDPOINTS.PRODUCTS.REORDER, { products })
  },

  /**
   * Track product click (for analytics)
   * @param {number} id - Product ID
   * @returns {Promise}
   */
  async trackClick(id) {
    return await apiClient.post(API_ENDPOINTS.PRODUCTS.TRACK_CLICK(id))
  },
}
