import { defineStore } from 'pinia'
import { ref } from 'vue'
import { profileService } from '@/services/profile.service'

export const useProfileStore = defineStore('profile', () => {
  const profile = ref(null)
  const isLoading = ref(false)
  const error = ref(null)

  async function fetchProfile() {
    isLoading.value = true
    error.value = null
    try {
      const response = await profileService.getProfile()
      if (response.success) {
        profile.value = response.data
      }
      return response
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function updateProfile(data) {
    isLoading.value = true
    error.value = null
    try {
      const response = await profileService.updateProfile(data)
      if (response.success) {
        profile.value = response.data
      }
      return response
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function uploadAvatar(file) {
    isLoading.value = true
    error.value = null
    try {
      const response = await profileService.uploadAvatar(file)
      if (response.success) {
        profile.value = response.data
      }
      return response
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function uploadBackground(file) {
    isLoading.value = true
    error.value = null
    try {
      const response = await profileService.uploadBackground(file)
      if (response.success) {
        profile.value = response.data
      }
      return response
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    profile,
    isLoading,
    error,
    fetchProfile,
    updateProfile,
    uploadAvatar,
    uploadBackground,
  }
})
