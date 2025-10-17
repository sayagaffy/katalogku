import apiClient from './api.service'
import { API_ENDPOINTS } from '@/config/api'

export const themeService = {
  /**
   * Get curated themes list
   */
  async list() {
    return await apiClient.get(API_ENDPOINTS.THEMES.LIST)
  },
}

