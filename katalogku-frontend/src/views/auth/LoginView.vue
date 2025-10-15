<template>
  <div
    class="min-h-screen flex items-center justify-center px-4 sm:px-6 lg:px-8 bg-gradient-to-br from-primary-50 via-white to-secondary-50 dark:from-gray-900 dark:via-gray-900 dark:to-gray-800 transition-all duration-500"
  >
    <!-- Theme Toggle (Top Right) -->
    <div class="fixed top-6 right-6 z-50">
      <ThemeToggle />
    </div>

    <div class="max-w-md w-full space-y-8 animate-slide-up">
      <!-- Logo & Header -->
      <div class="text-center">
        <div
          class="mx-auto w-16 h-16 bg-gradient-to-br from-primary-500 to-secondary-500 rounded-2xl flex items-center justify-center mb-4 shadow-glow animate-scale-in"
        >
          <svg
            class="w-10 h-10 text-white"
            fill="none"
            stroke="currentColor"
            viewBox="0 0 24 24"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"
            />
          </svg>
        </div>
        <h1
          class="text-4xl font-bold bg-gradient-to-r from-primary-600 to-secondary-600 dark:from-primary-400 dark:to-secondary-400 bg-clip-text text-transparent"
        >
          Kaitkan
        </h1>
        <p class="mt-3 text-base text-gray-600 dark:text-gray-400">
          Masuk ke akun Anda
        </p>
      </div>

      <!-- Card Container -->
      <div
        class="bg-white dark:bg-gray-800 rounded-2xl shadow-xl dark:shadow-2xl p-8 backdrop-blur-sm bg-opacity-80 dark:bg-opacity-90 border border-gray-100 dark:border-gray-700 transition-all duration-300"
      >
        <!-- Test Credentials (Development Only) -->
        <div
          class="bg-gradient-to-r from-blue-50 to-primary-50 dark:from-blue-900/20 dark:to-primary-900/20 border border-blue-200 dark:border-blue-800 rounded-xl p-4 mb-6"
        >
          <p class="text-sm font-semibold text-blue-900 dark:text-blue-300 mb-2 flex items-center">
            <svg class="w-4 h-4 mr-2" fill="currentColor" viewBox="0 0 20 20">
              <path
                fill-rule="evenodd"
                d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                clip-rule="evenodd"
              />
            </svg>
            Test Credentials
          </p>
          <p class="text-xs text-blue-700 dark:text-blue-400">
            WhatsApp: <strong class="font-mono">081234567890</strong>
          </p>
          <p class="text-xs text-blue-700 dark:text-blue-400">
            Password: <strong class="font-mono">password</strong>
          </p>
        </div>

        <!-- Login Form -->
        <form class="space-y-5" @submit.prevent="handleLogin">
          <BaseInput
            v-model="form.whatsapp"
            label="Nomor WhatsApp"
            type="tel"
            placeholder="081234567890"
            :error="validation.getError('whatsapp')"
            :disabled="isLoading"
            required
            @blur="validateWhatsapp"
          />

          <BaseInput
            v-model="form.password"
            label="Password"
            type="password"
            placeholder="Masukkan password"
            :error="validation.getError('password')"
            :disabled="isLoading"
            required
            @blur="validatePassword"
          />

          <!-- Error message -->
          <div
            v-if="errorMessage"
            class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 animate-slide-down"
          >
            <div class="flex items-start">
              <svg
                class="h-5 w-5 text-red-400 dark:text-red-500 mt-0.5"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                  clip-rule="evenodd"
                />
              </svg>
              <p class="ml-3 text-sm text-red-800 dark:text-red-300">{{ errorMessage }}</p>
            </div>
          </div>

          <!-- Submit button -->
          <BaseButton
            type="submit"
            variant="primary"
            size="lg"
            full-width
            :loading="isLoading"
            :disabled="isLoading"
            class="mt-6"
          >
            <span class="flex items-center justify-center">
              <svg
                v-if="!isLoading"
                class="w-5 h-5 mr-2"
                fill="none"
                stroke="currentColor"
                viewBox="0 0 24 24"
              >
                <path
                  stroke-linecap="round"
                  stroke-linejoin="round"
                  stroke-width="2"
                  d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"
                />
              </svg>
              Masuk
            </span>
          </BaseButton>

          <!-- Register link -->
          <div class="text-center pt-4">
            <p class="text-sm text-gray-600 dark:text-gray-400">
              Belum punya akun?
              <router-link
                to="/register"
                class="font-semibold text-primary-600 hover:text-primary-500 dark:text-primary-400 dark:hover:text-primary-300 transition-colors"
              >
                Daftar sekarang
              </router-link>
            </p>
          </div>
        </form>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive } from 'vue'
import { useRouter } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useFormValidation } from '@/composables/useFormValidation'
import BaseInput from '@/components/common/BaseInput.vue'
import BaseButton from '@/components/common/BaseButton.vue'
import ThemeToggle from '@/components/common/ThemeToggle.vue'

const router = useRouter()
const authStore = useAuthStore()
const validation = useFormValidation()

const form = reactive({
  whatsapp: '',
  password: '',
})

const isLoading = ref(false)
const errorMessage = ref('')

function validateWhatsapp() {
  const error = validation.validateWhatsapp(form.whatsapp)
  if (error) {
    validation.setError('whatsapp', error)
  } else {
    validation.clearError('whatsapp')
  }
}

function validatePassword() {
  const error = validation.validatePassword(form.password)
  if (error) {
    validation.setError('password', error)
  } else {
    validation.clearError('password')
  }
}

async function handleLogin() {
  console.log('=== Login Started ===')
  console.log('Form data:', { whatsapp: form.whatsapp, password: '***' })

  // Clear previous errors
  validation.clearAllErrors()

  // Validate all fields
  validateWhatsapp()
  validatePassword()

  console.log('Validation errors:', validation.errors.value)
  console.log('Has errors:', validation.hasErrors.value)

  if (validation.hasErrors.value) {
    console.log('Validation failed, stopping login')
    return
  }

  console.log('Validation passed, proceeding with login...')

  isLoading.value = true
  errorMessage.value = ''

  try {
    console.log('Calling authStore.login...')
    const response = await authStore.login(form.whatsapp, form.password)
    console.log('Login response:', response)

    console.log('Login successful, redirecting to dashboard...')
    router.push('/dashboard')
  } catch (error) {
    console.error('Login error:', error)
    console.error('Error type:', typeof error)
    console.error('Error details:', {
      message: error?.message,
      response: error?.response,
      toString: error?.toString?.()
    })

    // Handle different error types
    if (typeof error === 'string') {
      errorMessage.value = error
    } else if (error?.message) {
      errorMessage.value = error.message
    } else if (error?.response?.data?.message) {
      errorMessage.value = error.response.data.message
    } else {
      errorMessage.value = 'Login gagal. Silakan coba lagi.'
    }

    console.log('Final error message:', errorMessage.value)
  } finally {
    isLoading.value = false
    console.log('=== Login Ended ===')
  }
}
</script>
