import { defineStore } from 'pinia'
import { ref } from 'vue'
import { productService } from '@/services/product.service'

export const useProductStore = defineStore('product', () => {
  // State
  const products = ref([])
  const currentProduct = ref(null)
  const isLoading = ref(false)
  const error = ref(null)

  // Actions
  async function fetchProducts() {
    isLoading.value = true
    error.value = null
    try {
      const response = await productService.getProducts()
      if (response.success) {
        products.value = response.data
      }
      return response
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function fetchProduct(id) {
    isLoading.value = true
    error.value = null
    try {
      const response = await productService.getProduct(id)
      if (response.success) {
        currentProduct.value = response.data
      }
      return response
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function createProduct(data) {
    isLoading.value = true
    error.value = null
    try {
      const formData = new FormData()

      // Append text fields
      Object.keys(data).forEach((key) => {
        if (data[key] !== null && data[key] !== undefined && key !== 'image') {
          formData.append(key, data[key])
        }
      })

      // Append image file (required)
      if (data.image instanceof File) {
        formData.append('image', data.image)
      }

      const response = await productService.createProduct(formData)
      if (response.success) {
        // Add to products list
        products.value.push(response.data)
      }
      return response
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function updateProduct(id, data) {
    isLoading.value = true
    error.value = null
    try {
      const formData = new FormData()

      // Append text fields
      Object.keys(data).forEach((key) => {
        if (data[key] !== null && data[key] !== undefined && key !== 'image') {
          formData.append(key, data[key])
        }
      })

      // Append image file if exists (optional for update)
      if (data.image instanceof File) {
        formData.append('image', data.image)
      }

      const response = await productService.updateProduct(id, formData)
      if (response.success) {
        // Update in products list
        const index = products.value.findIndex((p) => p.id === id)
        if (index !== -1) {
          products.value[index] = response.data
        }
      }
      return response
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function deleteProduct(id) {
    isLoading.value = true
    error.value = null
    try {
      const response = await productService.deleteProduct(id)
      if (response.success) {
        // Remove from products list
        products.value = products.value.filter((p) => p.id !== id)
      }
      return response
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function reorderProducts(reorderedProducts) {
    isLoading.value = true
    error.value = null
    try {
      const response = await productService.reorderProducts(reorderedProducts)
      if (response.success) {
        // Update local products order
        await fetchProducts()
      }
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

  function clearCurrentProduct() {
    currentProduct.value = null
  }

  return {
    // State
    products,
    currentProduct,
    isLoading,
    error,
    // Actions
    fetchProducts,
    fetchProduct,
    createProduct,
    updateProduct,
    deleteProduct,
    reorderProducts,
    clearError,
    clearCurrentProduct,
  }
})
