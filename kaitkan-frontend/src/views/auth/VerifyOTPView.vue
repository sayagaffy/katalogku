<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div>
        <h2 class="text-center text-3xl font-extrabold text-gray-900">Verifikasi OTP</h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Masukkan kode OTP yang dikirim ke WhatsApp
          <br />
          <span class="font-semibold">{{ whatsappNumber }}</span>
        </p>
      </div>

      <!-- Verification Form -->
      <form class="mt-8 space-y-6" @submit.prevent="handleVerifyOTP">
        <div class="rounded-md shadow-sm space-y-4">
          <BaseInput
            v-model="form.otp"
            label="Kode OTP"
            type="text"
            placeholder="123456"
            maxlength="6"
            :error="validation.getError('otp')"
            :disabled="isLoading"
            required
            @blur="validateOTP"
          />

          <BaseInput
            v-model="form.name"
            label="Nama Lengkap"
            type="text"
            placeholder="Nama Anda"
            :error="validation.getError('name')"
            :disabled="isLoading"
            required
            @blur="validateName"
          />

          <BaseInput
            v-model="form.password"
            label="Password"
            type="password"
            placeholder="Minimal 8 karakter"
            :error="validation.getError('password')"
            :disabled="isLoading"
            required
            @blur="validatePassword"
          />

          <BaseInput
            v-model="form.password_confirmation"
            label="Konfirmasi Password"
            type="password"
            placeholder="Ulangi password"
            :error="validation.getError('password_confirmation')"
            :disabled="isLoading"
            required
            @blur="validatePasswordConfirmation"
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

        <!-- Info -->
        <div class="rounded-md bg-blue-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg
                class="h-5 w-5 text-blue-400"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-blue-800">
                Kode OTP berlaku selama 5 menit. Pastikan data Anda sudah benar sebelum melanjutkan.
              </p>
            </div>
          </div>
        </div>

        <!-- Submit button -->
        <div class="space-y-3">
          <BaseButton
            type="submit"
            variant="primary"
            size="lg"
            full-width
            :loading="isLoading"
            :disabled="validation.hasErrors || isLoading"
          >
            Verifikasi & Daftar
          </BaseButton>

          <BaseButton
            type="button"
            variant="ghost"
            size="md"
            full-width
            @click="goBack"
          >
            Kembali
          </BaseButton>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import { useFormValidation } from '@/composables/useFormValidation'
import BaseInput from '@/components/common/BaseInput.vue'
import BaseButton from '@/components/common/BaseButton.vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()
const validation = useFormValidation()

const form = reactive({
  whatsapp: '',
  otp: '',
  name: '',
  password: '',
  password_confirmation: '',
})

const isLoading = ref(false)
const errorMessage = ref('')

const whatsappNumber = computed(() => form.whatsapp || 'WhatsApp Anda')

function validateOTP() {
  const error = validation.validateOTP(form.otp)
  if (error) {
    validation.setError('otp', error)
  } else {
    validation.clearError('otp')
  }
}

function validateName() {
  const error = validation.validateName(form.name)
  if (error) {
    validation.setError('name', error)
  } else {
    validation.clearError('name')
  }
}

function validatePassword() {
  const error = validation.validatePassword(form.password)
  if (error) {
    validation.setError('password', error)
  } else {
    validation.clearError('password')
  }
  // Revalidate confirmation if it's already filled
  if (form.password_confirmation) {
    validatePasswordConfirmation()
  }
}

function validatePasswordConfirmation() {
  const error = validation.validatePasswordConfirmation(
    form.password,
    form.password_confirmation,
  )
  if (error) {
    validation.setError('password_confirmation', error)
  } else {
    validation.clearError('password_confirmation')
  }
}

async function handleVerifyOTP() {
  // Validate all fields
  validateOTP()
  validateName()
  validatePassword()
  validatePasswordConfirmation()

  if (validation.hasErrors) {
    return
  }

  isLoading.value = true
  errorMessage.value = ''

  try {
    await authStore.verifyOTP({
      whatsapp: form.whatsapp,
      otp: form.otp,
      name: form.name,
      password: form.password,
      password_confirmation: form.password_confirmation,
    })
    router.push('/dashboard')
  } catch (error) {
    errorMessage.value = error || 'Verifikasi gagal. Silakan coba lagi.'
  } finally {
    isLoading.value = false
  }
}

function goBack() {
  router.push('/register')
}

onMounted(() => {
  // Get WhatsApp number from query parameter
  form.whatsapp = route.query.whatsapp || ''
  if (!form.whatsapp) {
    router.push('/register')
  }
})
</script>
