import apiClient from './api.service'
import { API_ENDPOINTS } from '@/config/api'

export const linkService = {
  async list() {
    return await apiClient.get(API_ENDPOINTS.LINKS.LIST)
  },

  async create(data) {
    // Support optional thumbnail file
    if (typeof window !== 'undefined' && data.thumbnail instanceof window.File) {
      const form = new FormData()
      Object.entries(data).forEach(([k, v]) => {
        if (k === 'thumbnail') return
        if (v !== undefined && v !== null) form.append(k, v)
      })
      form.append('thumbnail', data.thumbnail)
      return await apiClient.post(API_ENDPOINTS.LINKS.CREATE, form, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    }
    return await apiClient.post(API_ENDPOINTS.LINKS.CREATE, data)
  },

  async update(id, data) {
    if (typeof window !== 'undefined' && data.thumbnail instanceof window.File) {
      const form = new FormData()
      Object.entries(data).forEach(([k, v]) => {
        if (k === 'thumbnail') return
        if (v !== undefined && v !== null) form.append(k, v)
      })
      form.append('thumbnail', data.thumbnail)
      return await apiClient.patch(API_ENDPOINTS.LINKS.UPDATE(id), form, {
        headers: { 'Content-Type': 'multipart/form-data' },
      })
    }
    return await apiClient.patch(API_ENDPOINTS.LINKS.UPDATE(id), data)
  },

  async reorder(ids) {
    return await apiClient.patch(API_ENDPOINTS.LINKS.REORDER, { ids })
  },

  async remove(id) {
    return await apiClient.delete(API_ENDPOINTS.LINKS.DELETE(id))
  },
}

