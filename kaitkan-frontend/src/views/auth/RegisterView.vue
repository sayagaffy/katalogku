<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <!-- Header -->
      <div>
        <h2 class="text-center text-3xl font-extrabold text-gray-900">Kaitkan</h2>
        <p class="mt-2 text-center text-sm text-gray-600">
          Daftar akun baru
        </p>
      </div>

      <!-- Register Form -->
      <form class="mt-8 space-y-6" @submit.prevent="handleSendOTP">
        <div class="rounded-md shadow-sm space-y-4">
          <BaseInput
            v-model="form.whatsapp"
            label="Nomor WhatsApp"
            type="tel"
            placeholder="081234567890"
            hint="Nomor WhatsApp akan digunakan untuk verifikasi OTP"
            :error="validation.getError('whatsapp')"
            :disabled="isLoading || otpSent"
            required
            @blur="validateWhatsapp"
          />
        </div>

        <!-- Success message -->
        <div v-if="otpSent" class="rounded-md bg-green-50 p-4">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg
                class="h-5 w-5 text-green-400"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z"
                  clip-rule="evenodd"
                />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm text-green-800">
                Kode OTP telah dikirim ke WhatsApp Anda.
                <br />
                {{ countdown > 0 ? `Kirim ulang dalam ${countdown} detik` : 'Anda bisa kirim ulang sekarang' }}
              </p>
            </div>
          </div>
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
        <div class="flex space-x-3">
          <BaseButton
            v-if="otpSent"
            type="button"
            variant="secondary"
            size="lg"
            :disabled="countdown > 0 || isLoading"
            @click="handleSendOTP"
          >
            Kirim Ulang OTP
          </BaseButton>

          <BaseButton
            v-if="otpSent"
            type="button"
            variant="primary"
            size="lg"
            full-width
            @click="goToVerification"
          >
            Lanjut ke Verifikasi
          </BaseButton>

          <BaseButton
            v-else
            type="submit"
            variant="primary"
            size="lg"
            full-width
            :loading="isLoading"
            :disabled="validation.hasErrors || isLoading"
          >
            Kirim Kode OTP
          </BaseButton>
        </div>

        <!-- Login link -->
        <div class="text-center">
          <p class="text-sm text-gray-600">
            Sudah punya akun?
            <router-link
              to="/login"
              class="font-medium text-blue-600 hover:text-blue-500"
            >
              Masuk di sini
            </router-link>
          </p>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, onUnmounted } from 'vue'
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
})

const isLoading = ref(false)
const errorMessage = ref('')
const otpSent = ref(false)
const countdown = ref(0)
let countdownInterval = null

function validateWhatsapp() {
  const error = validation.validateWhatsapp(form.whatsapp)
  if (error) {
    validation.setError('whatsapp', error)
  } else {
    validation.clearError('whatsapp')
  }
}

function startCountdown() {
  countdown.value = 60
  countdownInterval = setInterval(() => {
    countdown.value--
    if (countdown.value <= 0) {
      clearInterval(countdownInterval)
    }
  }, 1000)
}

async function handleSendOTP() {
  validateWhatsapp()

  if (validation.hasErrors) {
    return
  }

  isLoading.value = true
  errorMessage.value = ''

  try {
    await authStore.sendOTP(form.whatsapp)
    otpSent.value = true
    startCountdown()
  } catch (error) {
    errorMessage.value = error || 'Gagal mengirim OTP. Silakan coba lagi.'
  } finally {
    isLoading.value = false
  }
}

function goToVerification() {
  router.push({
    name: 'verify-otp',
    query: { whatsapp: form.whatsapp },
  })
}

onUnmounted(() => {
  if (countdownInterval) {
    clearInterval(countdownInterval)
  }
})
</script>
