import { defineStore } from 'pinia'
import { ref, computed } from 'vue'
import { authService } from '@/services/auth.service'

export const useAuthStore = defineStore('auth', () => {
  // State
  const user = ref(null)
  const token = ref(null)
  const isLoading = ref(false)
  const error = ref(null)

  // Getters
  const isAuthenticated = computed(() => !!token.value)
  const userName = computed(() => user.value?.name || '')
  const userWhatsapp = computed(() => user.value?.whatsapp || '')
  const userUsername = computed(() => user.value?.username || '')

  // Actions
  async function sendOTP(whatsapp) {
    isLoading.value = true
    error.value = null
    try {
      const response = await authService.sendOTP(whatsapp)
      return response
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function verifyOTP(data) {
    isLoading.value = true
    error.value = null
    try {
      const response = await authService.verifyOTP(data)
      if (response.success) {
        user.value = response.data.user
        token.value = response.data.token
      }
      return response
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function login(whatsapp, password) {
    isLoading.value = true
    error.value = null
    try {
      const response = await authService.login(whatsapp, password)
      if (response.success) {
        user.value = response.data.user
        token.value = response.data.token
      }
      return response
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function loginWithPin(whatsapp, pin) {
    isLoading.value = true
    error.value = null
    try {
      const response = await authService.loginWithPin(whatsapp, pin)
      if (response.success) {
        user.value = response.data.user
        token.value = response.data.token
      }
      return response
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function logout() {
    isLoading.value = true
    error.value = null
    try {
      await authService.logout()
      user.value = null
      token.value = null
    } catch (err) {
      error.value = err
      // Still clear local data even if API call fails
      user.value = null
      token.value = null
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function fetchUser() {
    isLoading.value = true
    error.value = null
    try {
      const response = await authService.getUser()
      if (response.success) {
        user.value = response.data
      }
      return response
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  function initializeAuth() {
    // Migrate old localStorage keys if present
    try {
      const oldToken = localStorage.getItem('katalogku_token')
      const oldUser = localStorage.getItem('katalogku_user')
      const hasNew = !!localStorage.getItem('kaitkan_token')
      if (oldToken && oldUser && !hasNew) {
        localStorage.setItem('kaitkan_token', oldToken)
        localStorage.setItem('kaitkan_user', oldUser)
      }
    } catch (e) {
      console.debug('migrate legacy auth keys failed', e)
    }

    // Load auth data from localStorage on app start
    if (authService.isAuthenticated()) {
      const storedUser = authService.getStoredUser()
      if (storedUser) {
        user.value = storedUser
        token.value = localStorage.getItem('kaitkan_token')
      }
    }
  }

  async function setPin(pin, pin_confirmation) {
    isLoading.value = true
    error.value = null
    try {
      const response = await authService.setPin(pin, pin_confirmation)
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
    user,
    token,
    isLoading,
    error,
    // Getters
    isAuthenticated,
    userName,
    userWhatsapp,
    userUsername,
    // Actions
    sendOTP,
    verifyOTP,
    login,
    loginWithPin,
    logout,
    fetchUser,
    initializeAuth,
    setPin,
    clearError,
  }
})
