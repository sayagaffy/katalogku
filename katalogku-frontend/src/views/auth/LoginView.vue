<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div>
        <h2 class="text-center text-3xl font-extrabold text-gray-900">KatalogKu</h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Masuk ke akun Anda
        </p>
      </div>

      <!-- Test Credentials (Development Only) -->
      <div class="bg-blue-50 border border-blue-200 rounded-lg p-4 mb-4">
        <p class="text-sm font-semibold text-blue-900 mb-2">🔧 Test Credentials:</p>
        <p class="text-xs text-blue-700">WhatsApp: <strong>081234567890</strong></p>
        <p class="text-xs text-blue-700">Password: <strong>password</strong></p>
      </div>

      <!-- Login Form -->
      <form class="mt-8 space-y-6" @submit.prevent="handleLogin">
        <div class="rounded-md shadow-sm space-y-4">
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
        </div>

        <!-- Error message -->
        <div v-if="errorMessage" class="rounded-md bg-red-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg
                class="h-5 w-5 text-red-400"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-red-800">{{ errorMessage }}</p>
            </div>
          </div>
        </div>

        <!-- Submit button -->
        <BaseButton
          type="submit"
          variant="primary"
          size="lg"
          full-width
          :loading="isLoading"
          :disabled="validation.hasErrors || isLoading"
        >
          Masuk
        </BaseButton>

        <!-- Register link -->
        <div class="text-center">
          <p class="text-sm text-gray-600">
            Belum punya akun?
            <router-link
              to="/register"
              class="font-medium text-blue-600 hover:text-blue-500"
            >
              Daftar sekarang
            </router-link>
          </p>
        </div>
      </form>
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
  // Validate all fields
  validateWhatsapp()
  validatePassword()

  if (validation.hasErrors) {
    return
  }

  isLoading.value = true
  errorMessage.value = ''

  try {
    await authStore.login(form.whatsapp, form.password)
    router.push('/dashboard')
  } catch (error) {
    errorMessage.value = error || 'Login gagal. Silakan coba lagi.'
  } finally {
    isLoading.value = false
  }
}
</script>
