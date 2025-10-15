<template>
  <div>
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Setup Katalog</h2>

    <div class="bg-white rounded-lg shadow p-6">
      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Name -->
        <BaseInput
          v-model="form.name"
          label="Nama Katalog"
          placeholder="Contoh: Toko Baju Sarah"
          :error="validation.getError('name')"
          required
          @blur="validateName"
        />

        <!-- Username -->
        <BaseInput
          v-model="form.username"
          label="Username"
          placeholder="contoh: bajusarah (huruf kecil, angka, dash, underscore)"
          hint="Link katalog Anda akan menjadi: kaitkan.id/c/username"
          :error="validation.getError('username')"
          required
          @blur="validateUsername"
        />

        <!-- Description -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Deskripsi
          </label>
          <textarea
            v-model="form.description"
            rows="4"
            class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
            placeholder="Ceritakan tentang toko Anda..."
            maxlength="500"
          ></textarea>
          <p class="mt-1 text-sm text-gray-500">
            {{ form.description?.length || 0 }}/500 karakter
          </p>
        </div>

        <!-- Category -->
        <div>
          <label class="block text-sm font-medium text-gray-700 mb-1">
            Kategori
          </label>
          <select
            v-model="form.category"
            class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
          >
            <option value="">Pilih kategori...</option>
            <option value="fashion">Fashion & Pakaian</option>
            <option value="elektronik">Elektronik</option>
            <option value="makanan">Makanan & Minuman</option>
            <option value="kecantikan">Kecantikan & Kesehatan</option>
            <option value="rumah-tangga">Rumah Tangga</option>
            <option value="olahraga">Olahraga</option>
            <option value="hobi">Hobi & Koleksi</option>
            <option value="lainnya">Lainnya</option>
          </select>
        </div>

        <!-- WhatsApp -->
        <BaseInput
          v-model="form.whatsapp"
          label="Nomor WhatsApp (Opsional)"
          type="tel"
          placeholder="081234567890"
          hint="Jika tidak diisi, akan menggunakan nomor WhatsApp akun Anda"
          :error="validation.getError('whatsapp')"
          @blur="validateWhatsapp"
        />

        <!-- Avatar -->
        <ImageUpload
          v-model="form.avatar"
          label="Logo/Avatar Katalog (Opsional)"
          hint="Rekomendasi: 500x500px, maksimal 10MB"
          @change="handleAvatarChange"
        />

        <!-- Theme (future feature, for now just default) -->
        <input type="hidden" v-model="form.theme" />

        <!-- Is Published -->
        <div class="flex items-center">
          <input
            id="is_published"
            v-model="form.is_published"
            type="checkbox"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          />
          <label for="is_published" class="ml-2 block text-sm text-gray-900">
            Publikasikan katalog (katalog dapat diakses publik)
          </label>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="rounded-xl bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 p-4 animate-slide-down">
          <div class="flex">
            <div class="flex-shrink-0">
              <svg class="h-5 w-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
              </svg>
            </div>
            <div class="ml-3">
              <p class="text-sm font-semibold text-red-800 dark:text-red-300">{{ errorMessage }}</p>
              <div v-if="Object.keys(validation.errors.value).length > 0" class="mt-2 text-xs text-red-700 dark:text-red-400">
                <p class="font-semibold mb-1">Detail error:</p>
                <ul class="list-disc list-inside space-y-1">
                  <li v-for="(error, field) in validation.errors.value" :key="field">
                    <strong>{{ field }}:</strong> {{ error }}
                  </li>
                </ul>
              </div>
            </div>
          </div>
        </div>

        <!-- Submit Button -->
        <div class="flex justify-end space-x-3">
          <router-link
            to="/dashboard"
            class="px-4 py-2 border border-gray-300 rounded-lg text-gray-700 hover:bg-gray-50 transition-colors"
          >
            Batal
          </router-link>
          <BaseButton
            type="submit"
            variant="primary"
            size="lg"
            :loading="isLoading"
            :disabled="isLoading"
          >
            {{ isEdit ? 'Perbarui Katalog' : 'Buat Katalog' }}
          </BaseButton>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useRouter } from 'vue-router'
import { useCatalogStore } from '@/stores/catalog'
import { useFormValidation } from '@/composables/useFormValidation'
import BaseInput from '@/components/common/BaseInput.vue'
import BaseButton from '@/components/common/BaseButton.vue'
import ImageUpload from '@/components/common/ImageUpload.vue'

const router = useRouter()
const catalogStore = useCatalogStore()
const validation = useFormValidation()

const form = reactive({
  name: '',
  username: '',
  description: '',
  category: '',
  whatsapp: '',
  avatar: null,
  theme: 'default',
  is_published: true,
})

const isLoading = ref(false)
const errorMessage = ref('')
const isEdit = computed(() => !!catalogStore.catalog)

function validateName() {
  const error = validation.validateName(form.name)
  if (error) {
    validation.setError('name', error)
  } else {
    validation.clearError('name')
  }
}

function validateUsername() {
  if (!form.username) {
    validation.setError('username', 'Username harus diisi')
    return
  }
  if (form.username.length < 3) {
    validation.setError('username', 'Username minimal 3 karakter')
    return
  }
  if (!/^[a-z0-9_-]+$/.test(form.username)) {
    validation.setError('username', 'Username hanya boleh huruf kecil, angka, dash, dan underscore')
    return
  }
  validation.clearError('username')
}

function validateWhatsapp() {
  if (form.whatsapp) {
    const error = validation.validateWhatsapp(form.whatsapp)
    if (error) {
      validation.setError('whatsapp', error)
    } else {
      validation.clearError('whatsapp')
    }
  } else {
    validation.clearError('whatsapp')
  }
}

function handleAvatarChange({ file, error }) {
  if (error) {
    errorMessage.value = error
  } else {
    errorMessage.value = ''
  }
}

async function handleSubmit() {
  console.log('=== Catalog Submit Started ===')
  console.log('Form data:', { ...form, avatar: form.avatar ? 'FILE' : null })

  // Validate all fields
  validateName()
  validateUsername()
  validateWhatsapp()

  console.log('Validation errors:', validation.errors.value)
  console.log('Has errors:', validation.hasErrors.value)

  if (validation.hasErrors.value) {
    console.log('Validation failed, stopping submit')
    return
  }

  isLoading.value = true
  errorMessage.value = ''
  validation.clearAllErrors()

  try {
    console.log('Calling catalogStore.saveCatalog...')
    const response = await catalogStore.saveCatalog(form)
    console.log('Catalog saved successfully:', response)
    router.push('/dashboard')
  } catch (error) {
    console.error('Catalog save error:', error)
    console.error('Error type:', typeof error)
    console.error('Error keys:', error && typeof error === 'object' ? Object.keys(error) : 'N/A')

    // Handle structured validation errors from backend
    if (error && typeof error === 'object' && error.errors) {
      console.log('Backend validation errors:', error.errors)
      console.log('Backend validation errors (stringified):', JSON.stringify(error.errors, null, 2))
      errorMessage.value = error.message || 'Validasi gagal'

      // Set individual field errors
      Object.keys(error.errors).forEach((field) => {
        const fieldErrors = error.errors[field]
        const errorMsg = Array.isArray(fieldErrors) ? fieldErrors[0] : fieldErrors
        console.log(`Field "${field}" error:`, errorMsg)
        validation.setError(field, errorMsg)
      })
    } else {
      // Simple string error
      console.log('Simple error, no structured errors object')
      errorMessage.value = typeof error === 'string' ? error : 'Gagal menyimpan katalog. Silakan coba lagi.'
    }
  } finally {
    isLoading.value = false
  }
}

onMounted(async () => {
  try {
    await catalogStore.fetchCatalog()
    const catalog = catalogStore.catalog

    if (catalog) {
      // Populate form with existing data
      form.name = catalog.name
      form.username = catalog.username
      form.description = catalog.description || ''
      form.category = catalog.category || ''
      form.whatsapp = catalog.whatsapp || ''
      form.theme = catalog.theme || 'default'
      form.is_published = catalog.is_published

      // Set avatar preview if exists
      if (catalog.avatar?.webp) {
        form.avatar = catalog.avatar.webp
      }
    }
  } catch (error) {
    // No catalog yet, that's fine
  }
})
</script>
