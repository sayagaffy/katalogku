<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
      <div class="text-center">
        <h2 class="text-3xl font-extrabold text-gray-900">Reset PIN</h2>
        <p class="mt-2 text-sm text-gray-600">Verifikasi nomor dan setel PIN baru</p>
      </div>

      <div class="bg-white p-6 rounded-2xl shadow">
        <div v-if="step === 'request'" class="space-y-5">
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
          <BaseButton :loading="isLoading" full-width @click="sendCode">Kirim Sandi Masuk</BaseButton>
        </div>

        <div v-else class="space-y-5">
          <BaseInput v-model="form.otp" label="Sandi Masuk (6 digit)" placeholder="123456" maxlength="6" />
          <BaseInput v-model="form.pin" label="PIN Baru" placeholder="******" type="password" />
          <BaseInput v-model="form.pin_confirmation" label="Konfirmasi PIN" placeholder="******" type="password" />
          <BaseButton :loading="isLoading" full-width @click="resetPin">Reset PIN & Masuk</BaseButton>
          <BaseButton variant="ghost" full-width @click="step='request'">Kembali</BaseButton>
        </div>

        <p v-if="errorMessage" class="mt-3 text-sm text-red-600">{{ errorMessage }}</p>
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

const router = useRouter()
const authStore = useAuthStore()
const validation = useFormValidation()

const step = ref('request')
const isLoading = ref(false)
const errorMessage = ref('')

const form = reactive({ whatsapp: '', otp: '', pin: '', pin_confirmation: '' })

function validateWhatsapp() {
  const error = validation.validateWhatsapp(form.whatsapp)
  if (error) validation.setError('whatsapp', error)
  else validation.clearError('whatsapp')
}

async function sendCode() {
  validateWhatsapp()
  if (validation.hasErrors.value) return
  isLoading.value = true
  errorMessage.value = ''
  try {
    await authStore.sendOTP(form.whatsapp)
    step.value = 'verify'
  } catch (e) {
    errorMessage.value = typeof e === 'string' ? e : 'Gagal mengirim sandi masuk'
  } finally {
    isLoading.value = false
  }
}

async function resetPin() {
  if (!/^[0-9]{6}$/.test((form.pin || '').trim())) {
    errorMessage.value = 'PIN harus 6 digit angka'
    return
  }
  if (form.pin !== form.pin_confirmation) {
    errorMessage.value = 'Konfirmasi PIN tidak cocok'
    return
  }
  isLoading.value = true
  errorMessage.value = ''
  try {
    const res = await fetch(`${import.meta.env.VITE_API_BASE_URL}${import.meta.env.VITE_API_PREFIX}/auth/reset-pin`, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({
        whatsapp: form.whatsapp,
        otp: form.otp,
        pin: form.pin,
        pin_confirmation: form.pin_confirmation,
      }),
    })
    const data = await res.json()
    if (!res.ok || !data.success) {
      throw new Error(data.message || 'Reset PIN gagal')
    }
    // store token via authService helper
    authStore.$patch({ user: data.data.user, token: data.data.token })
    localStorage.setItem('kaitkan_token', data.data.token)
    localStorage.setItem('kaitkan_user', JSON.stringify(data.data.user))
    router.push('/dashboard')
  } catch (e) {
    errorMessage.value = e?.message || 'Reset PIN gagal'
  } finally {
    isLoading.value = false
  }
}
</script>

