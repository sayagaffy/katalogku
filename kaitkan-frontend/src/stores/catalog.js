import { defineStore } from 'pinia'
import { ref } from 'vue'
import { catalogService } from '@/services/catalog.service'

export const useCatalogStore = defineStore('catalog', () => {
  // State
  const catalog = ref(null)
  const isLoading = ref(false)
  const error = ref(null)

  // Actions
  async function fetchCatalog() {
    isLoading.value = true
    error.value = null
    try {
      const response = await catalogService.getCatalog()
      if (response.success) {
        catalog.value = response.data
      }
      return response
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function saveCatalog(data) {
    isLoading.value = true
    error.value = null
    try {
      const formData = new FormData()

      // Append text fields
      Object.keys(data).forEach((key) => {
        if (data[key] !== null && data[key] !== undefined && key !== 'avatar') {
          // Convert boolean to 1/0 for FormData
          const value = typeof data[key] === 'boolean' ? (data[key] ? 1 : 0) : data[key]
          formData.append(key, value)
        }
      })

      // Append avatar file if exists (guard for SSR/build-time environments)
      if (typeof window !== 'undefined' && data.avatar instanceof window.File) {
        formData.append('avatar', data.avatar)
      }

      const response = await catalogService.saveCatalog(formData)
      if (response.success) {
        catalog.value = response.data
      }
      return response
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function getPublicCatalog(username) {
    isLoading.value = true
    error.value = null
    try {
      const response = await catalogService.getPublicCatalog(username)
      return response
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  function clearError() {
    error.value = null
  }

  return {
    // State
    catalog,
    isLoading,
    error,
    // Actions
    fetchCatalog,
    saveCatalog,
    getPublicCatalog,
    clearError,
  }
})
