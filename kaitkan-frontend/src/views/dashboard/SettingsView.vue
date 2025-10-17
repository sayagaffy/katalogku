<template>
  <div class="space-y-6">
    <div>
      <h2 class="font-extrabold text-gray-900 text-2xl">Pengaturan</h2>
      <p class="text-gray-500">Kelola profil, preferensi, dan keamanan akun Anda</p>
    </div>

    <!-- Profile -->
  <section class="bg-white shadow p-6 rounded-2xl">
    <h3 class="mb-4 font-bold text-gray-900 text-lg">Profil</h3>

      <!-- Error Message -->
      <div
        v-if="errorMessage"
        class="bg-red-50 dark:bg-red-900/20 mb-4 p-4 border border-red-200 dark:border-red-800 rounded-xl"
      >
        <div class="flex">
          <div class="flex-shrink-0">
            <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20">
              <path
                fill-rule="evenodd"
                d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z"
                clip-rule="evenodd"
              />
            </svg>
          </div>
          <div class="ml-3">
            <p class="font-semibold text-red-800 dark:text-red-300 text-sm">{{ errorMessage }}</p>
            <div
              v-if="Object.keys(validation.errors.value).length > 0"
              class="mt-2 text-red-700 dark:text-red-400 text-xs"
            >
              <p class="mb-1 font-semibold">Detail error:</p>
              <ul class="space-y-1 list-disc list-inside">
                <li v-for="(error, field) in validation.errors.value" :key="field">
                  <strong>{{ field }}:</strong> {{ error }}
                </li>
              </ul>
            </div>
          </div>
        </div>
      </div>

      <div class="gap-6 grid grid-cols-1 lg:grid-cols-3">
        <!-- Avatar -->
        <div class="flex items-center gap-4">
          <div
            class="flex justify-center items-center bg-gradient-to-br from-primary-500 to-secondary-500 rounded-full w-20 h-20 overflow-hidden font-bold text-white text-2xl"
          >
            <img
              v-if="avatarPreview"
              :src="avatarPreview"
              alt="Avatar"
              class="w-full h-full object-cover"
            />
            <span v-else>{{ initials(form.name) }}</span>
          </div>
          <div>
            <p class="font-semibold text-gray-900">Foto Profil</p>
            <p class="text-gray-500 text-sm">Gambar akan tampil di katalog publik</p>
            <div class="flex items-center gap-2 mt-2">
              <BaseButton
                variant="secondary"
                size="sm"
                :disabled="isSaving"
                @click="openImagePicker"
                >Ubah Foto</BaseButton
              >
              <span v-if="avatarFileName" class="text-gray-500 text-xs">{{ avatarFileName }}</span>
            </div>
            <!-- Hidden ImageUpload, used only for picking file and creating preview -->
            <div class="hidden">
              <ImageUpload
                ref="imageUploadRef"
                v-model="form.avatar"
                label=""
                @change="handleAvatarChange"
              />
            </div>
          </div>
        </div>

        <!-- Fields -->
        <div class="gap-4 grid grid-cols-1 md:grid-cols-2 lg:col-span-2">
          <BaseInput
            v-model="form.name"
            label="Nama"
            placeholder="Nama lengkap"
            :error="validation.getError('name')"
            required
            @blur="validateName"
          />
          <BaseInput
            v-model="form.username"
            label="Username"
            placeholder="mis. tokofashion"
            hint="Digunakan untuk URL katalog"
            :error="validation.getError('username')"
            required
            @blur="validateUsername"
          />
          <BaseInput
            v-model="form.whatsapp"
            label="Nomor WhatsApp"
            type="tel"
            placeholder="08xxxxxxxxxx"
            :error="validation.getError('whatsapp')"
            @blur="validateWhatsapp"
          />
          <BaseInput
            v-model="form.email"
            label="Email"
            type="email"
            placeholder="Belum didukung"
            disabled
            hint="Pengaturan email belum tersedia"
          />
          <!-- Public Theme Selector -->
          <div class="col-span-1 md:col-span-2">
            <label class="block mb-1 text-sm font-semibold text-gray-700">Tema Halaman Publik</label>
            <div class="flex items-center gap-3">
              <select
                v-model="form.theme_id"
                class="bg-white px-3 py-2 border border-gray-200 rounded-lg text-sm"
                :disabled="isSaving || isLoadingThemes"
                title="Pilih tema kurasi"
              >
                <option :value="null">Default</option>
                <option v-for="t in themes" :key="t.id" :value="t.id">{{ t.name }}</option>
              </select>
              <div v-if="selectedThemePalette" class="flex items-center gap-1">
                <span
                  v-for="(val, key) in previewSwatches"
                  :key="key"
                  class="inline-block w-5 h-5 rounded"
                  :style="{ backgroundColor: val }"
                  :title="key"
                ></span>
              </div>
            </div>
            <p class="mt-1 text-xs text-gray-500">Tema ini digunakan untuk tampilan publik profil Anda.</p>
          </div>
        </div>
      </div>
      <div class="flex items-center gap-3 mt-6">
        <BaseButton :loading="isSaving" :disabled="isSaving" @click="handleProfileSave"
          >Simpan Perubahan</BaseButton
        >
        <BaseButton variant="ghost" :disabled="isSaving" @click="resetProfile">Reset</BaseButton>
      </div>
    </section>

    <!-- Preferences & Notifications -->
    <section class="gap-6 grid grid-cols-1 lg:grid-cols-2">
      <!-- Preferences -->
      <div class="bg-white shadow p-6 rounded-2xl">
        <h3 class="mb-4 font-bold text-gray-900 text-lg">Preferensi</h3>
        <div class="space-y-5">
          <div class="flex justify-between items-center">
            <div>
              <p class="font-semibold text-gray-900">Tema</p>
              <p class="text-gray-500 text-sm">Atur mode terang/gelap</p>
            </div>
            <ThemeToggle />
          </div>

          <div class="flex justify-between items-center">
            <div>
              <p class="font-semibold text-gray-900">Bahasa</p>
              <p class="text-gray-500 text-sm">Saat ini: Indonesia</p>
            </div>
            <select
              class="bg-white px-3 py-2 border border-gray-200 rounded-lg text-sm"
              disabled
              title="Ganti bahasa (segera)"
            >
              <option>Indonesia</option>
              <option>English</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Notifications -->
      <div class="bg-white shadow p-6 rounded-2xl">
        <h3 class="mb-4 font-bold text-gray-900 text-lg">Notifikasi</h3>
        <div class="space-y-5">
          <ToggleRow
            label="Ringkasan mingguan"
            description="Kirim laporan performa via email"
            v-model:checked="notify.weekly"
          />
          <ToggleRow
            label="Notifikasi klik produk"
            description="Dapatkan pemberitahuan saat ada klik baru"
            v-model:checked="notify.clicks"
          />
          <ToggleRow
            label="Pembaruan fitur"
            description="Info fitur baru Kaitkan"
            v-model:checked="notify.updates"
          />
        </div>
        <div class="mt-6">
          <BaseButton
            :loading="isSavingPrefs"
            :disabled="isSavingPrefs"
            full-width
            @click="savePreferences"
            >Simpan Preferensi</BaseButton
          >
        </div>
      </div>
    </section>

    

    <!-- Background (Public Page) -->
    <section class="bg-white shadow p-6 rounded-2xl">
      <h3 class="mb-4 font-bold text-gray-900 text-lg">Background Halaman Publik</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4 items-start">
        <div class="md:col-span-2">
          <ImageUpload
            v-model="backgroundFile"
            label="Background Image"
            hint="PNG, JPG, WebP maksimal 20MB"
            @change="handleBackgroundChange"
          />
        </div>
        <div class="space-y-2">
          <label class="block text-sm font-semibold text-gray-700">Overlay Gelap</label>
          <input type="range" min="0" max="1" step="0.05" v-model.number="backgroundOverlay" class="w-full" />
          <div class="text-sm text-gray-600">Opacity: {{ backgroundOverlay.toFixed(2) }}</div>
          <BaseButton :loading="isSavingBg" :disabled="isSavingBg" @click="saveBackground">Simpan Background</BaseButton>
        </div>
      </div>
      <div v-if="backgroundPreview" class="mt-4">
        <div class="rounded-xl overflow-hidden h-40 relative">
          <img :src="backgroundPreview" class="w-full h-full object-cover" alt="Preview" />
          <div class="absolute inset-0" :style="{ backgroundColor: `rgba(0,0,0,${backgroundOverlay})` }"></div>
        </div>
      </div>
    </section>

    <!-- Security -->
    <section class="bg-white shadow p-6 rounded-2xl">
      <h3 class="mb-4 font-bold text-gray-900 text-lg">Keamanan</h3>
      <div class="gap-4 grid grid-cols-1 md:grid-cols-3">
        <BaseInput
          v-model="security.pin"
          type="password"
          label="PIN baru (6 digit)"
          placeholder="******"
        />
        <BaseInput
          v-model="security.pinConfirm"
          type="password"
          label="Konfirmasi PIN"
          placeholder="******"
        />
      </div>
      <div class="mt-4">
        <BaseButton :loading="isSavingPin" :disabled="isSavingPin" @click="savePin">Simpan PIN</BaseButton>
      </div>
      <div
        class="bg-blue-50 mt-6 p-4 border border-blue-200 rounded-xl text-blue-800 text-sm"
      >
        Anda dapat masuk harian menggunakan PIN. Jika lupa PIN, lakukan verifikasi nomor untuk menyetel PIN baru.
      </div>
    </section>

    <!-- Danger Zone -->
    <section class="bg-red-50 p-6 border border-red-200 rounded-2xl">
      <h3 class="mb-2 font-bold text-red-700 text-lg">Zona Berbahaya</h3>
      <p class="text-red-700 text-sm">Hapus akun dan semua data Anda secara permanen.</p>
      <div class="mt-4">
        <BaseButton variant="danger" disabled title="Hapus akun belum tersedia"
          >Hapus Akun</BaseButton
        >
      </div>
    </section>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed, watch } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useProfileStore } from '@/stores/profile'
import { themeService } from '@/services/theme.service'
import { useFormValidation } from '@/composables/useFormValidation'
import BaseInput from '@/components/common/BaseInput.vue'
import BaseButton from '@/components/common/BaseButton.vue'
import ThemeToggle from '@/components/common/ThemeToggle.vue'
import ImageUpload from '@/components/common/ImageUpload.vue'
// SocialIconsEditor moved to Links tab

const authStore = useAuthStore()
const profileStore = useProfileStore()
const validation = useFormValidation()

const form = reactive({
  name: '',
  username: '',
  whatsapp: '',
  email: '', // not used yet
  avatar: null,
  theme_id: null,
})

const initialProfile = ref(null)
const imageUploadRef = ref(null)
const avatarPreview = ref('')
const backgroundFile = ref(null)
const backgroundPreview = ref('')
const backgroundOverlay = ref(0)
const isSavingBg = ref(false)
const socialIconsPosition = ref('top')

const isSaving = ref(false)
const errorMessage = ref('')
const isLoadingThemes = ref(false)
const themes = ref([])

const notify = reactive({ weekly: true, clicks: true, updates: true })
const isSavingPrefs = ref(false)
const PREF_KEY = 'kaitkan_notify_prefs'

const security = reactive({ pin: '', pinConfirm: '' })
const isSavingPin = ref(false)

function initials(name) {
  if (!name) return 'K'
  return name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .slice(0, 2)
    .toUpperCase()
}

function validateName() {
  const error = validation.validateName(form.name)
  if (error) validation.setError('name', error)
  else validation.clearError('name')
}

function validateUsername() {
  if (!form.username) return validation.setError('username', 'Username harus diisi')
  if (form.username.length < 3)
    return validation.setError('username', 'Username minimal 3 karakter')
  if (!/^[a-z0-9_-]+$/.test(form.username))
    return validation.setError('username', 'Hanya huruf kecil, angka, dash, underscore')
  validation.clearError('username')
}

function validateWhatsapp() {
  if (!form.whatsapp) return validation.clearError('whatsapp')
  const err = validation.validateWhatsapp(form.whatsapp)
  if (err) validation.setError('whatsapp', err)
  else validation.clearError('whatsapp')
}

function openImagePicker() {
  imageUploadRef.value?.click()
}

function handleAvatarChange({ file, error }) {
  if (error) {
    errorMessage.value = error
    return
  }
  errorMessage.value = ''
  if (typeof window !== 'undefined' && file instanceof window.File) {
    const reader = new FileReader()
    reader.onload = (e) => {
      avatarPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  } else if (typeof form.avatar === 'string') {
    avatarPreview.value = form.avatar
  } else {
    avatarPreview.value = ''
  }
}

function handleBackgroundChange({ file, error }) {
  if (error) {
    errorMessage.value = error
    return
  }
  errorMessage.value = ''
  if (typeof window !== 'undefined' && file instanceof window.File) {
    const reader = new FileReader()
    reader.onload = (e) => {
      backgroundPreview.value = e.target.result
    }
    reader.readAsDataURL(file)
  } else if (typeof backgroundFile.value === 'string') {
    backgroundPreview.value = backgroundFile.value
  } else {
    backgroundPreview.value = ''
  }
}

// helpers for template-safe File checks
const isFile = (val) => typeof window !== 'undefined' && val instanceof window.File
const avatarFileName = computed(() => (isFile(form.avatar) ? form.avatar.name : ''))

const selectedThemePalette = computed(() => {
  const t = themes.value.find((x) => x.id === form.theme_id)
  return t?.palette || null
})

const previewSwatches = computed(() => {
  if (!selectedThemePalette.value) return null
  const p = selectedThemePalette.value
  // Pick common keys in order
  const keys = ['primary', 'background', 'text', 'accent']
  const out = {}
  keys.forEach((k) => {
    if (p[k]) out[k] = p[k]
  })
  return out
})

function snapshotInitial() {
  initialProfile.value = {
    name: form.name,
    username: form.username,
    whatsapp: form.whatsapp,
    email: form.email,
    avatar: form.avatar,
    avatarPreview: avatarPreview.value,
  }
}

function resetProfile() {
  if (!initialProfile.value) return
  const snap = initialProfile.value
  form.name = snap.name
  form.username = snap.username
  form.whatsapp = snap.whatsapp
  form.email = snap.email
  form.avatar = snap.avatar
  avatarPreview.value = snap.avatarPreview || ''
  validation.clearAllErrors()
  errorMessage.value = ''
}

async function handleProfileSave() {
  // validate
  validateName()
  validateUsername()
  validateWhatsapp()
  if (validation.hasErrors.value) return

  isSaving.value = true
  errorMessage.value = ''
  try {
    // First, update text fields
    const res = await profileStore.updateProfile({
      name: form.name,
      username: form.username,
      whatsapp: form.whatsapp,
      theme_id: form.theme_id,
    })

    // Then, upload avatar if a new file is selected
    if (typeof window !== 'undefined' && form.avatar instanceof window.File) {
      const avatarRes = await profileStore.uploadAvatar(form.avatar)
      const avatarUrl = avatarRes.data?.avatar?.webp || avatarRes.data?.avatar?.jpg || ''
      if (avatarUrl) avatarPreview.value = avatarUrl
    } else {
      // Update preview from response if provided
      const avatarUrl = res.data?.avatar?.webp || res.data?.avatar?.jpg || ''
      if (avatarUrl) avatarPreview.value = avatarUrl
    }

    alert('Profil berhasil disimpan')
    snapshotInitial()
  } catch (err) {
    if (err && typeof err === 'object' && err.errors) {
      errorMessage.value = err.message || 'Validasi gagal'
      Object.keys(err.errors).forEach((field) => {
        const fieldErrors = err.errors[field]
        const errorMsg = Array.isArray(fieldErrors) ? fieldErrors[0] : fieldErrors
        validation.setError(field, errorMsg)
      })
    } else {
      errorMessage.value = typeof err === 'string' ? err : 'Gagal menyimpan profil'
    }
  } finally {
    isSaving.value = false
  }
}

function loadPreferences() {
  try {
    const raw = localStorage.getItem(PREF_KEY)
    if (raw) {
      const data = JSON.parse(raw)
      notify.weekly = !!data.weekly
      notify.clicks = !!data.clicks
      notify.updates = !!data.updates
    }
  } catch (e) {
    console.debug('loadPreferences failed', e)
  }
}

function savePreferences() {
  isSavingPrefs.value = true
  try {
    localStorage.setItem(
      PREF_KEY,
      JSON.stringify({ weekly: notify.weekly, clicks: notify.clicks, updates: notify.updates }),
    )
    alert('Preferensi notifikasi tersimpan')
  } finally {
    isSavingPrefs.value = false
  }
}

async function savePin() {
  // simple client validation
  const pin = (security.pin || '').trim()
  const pinConfirm = (security.pinConfirm || '').trim()
  if (!/^[0-9]{6}$/.test(pin)) {
    alert('PIN harus 6 digit angka')
    return
  }
  if (pin !== pinConfirm) {
    alert('Konfirmasi PIN tidak cocok')
    return
  }

  isSavingPin.value = true
  try {
    await authStore.setPin(pin, pinConfirm)
    alert('PIN berhasil disimpan')
    security.pin = ''
    security.pinConfirm = ''
  } catch (e) {
    const msg = typeof e === 'string' ? e : (e?.message || 'Gagal menyimpan PIN')
    alert(msg)
  } finally {
    isSavingPin.value = false
  }
}

// Inline subcomponent for toggles (local to this view)
const ToggleRow = {
  props: { label: String, description: String, checked: Boolean },
  emits: ['update:checked'],
  methods: {
    onToggle(e) {
      this.$emit('update:checked', e.target.checked)
    },
  },
  template: `
    <div class="flex justify-between items-start gap-4">
      <div class="min-w-0">
        <p class="font-semibold text-gray-900">{{ label }}</p>
        <p class="text-gray-500 text-sm">{{ description }}</p>
      </div>
      <label class="inline-flex relative items-center cursor-pointer select-none">
        <input type="checkbox" class="sr-only peer" :checked="checked" @change="onToggle" />
        <div class="bg-gray-200 peer-checked:bg-green-500 rounded-full w-11 h-6 transition-colors"></div>
        <div class="top-0.5 left-0.5 absolute bg-white shadow rounded-full w-5 h-5 transition-transform peer-checked:translate-x-5 transform"></div>
      </label>
    </div>
  `,
}

onMounted(async () => {
  try {
    // ensure auth and profile data
    if (!authStore.user) {
      try {
        await authStore.fetchUser()
      } catch (e) {
        console.debug('fetchUser failed (non-blocking)', e)
      }
    }
    await profileStore.fetchProfile()
  } catch (e) {
    console.debug('init settings failed', e)
  }

  const profile = profileStore.profile
  if (profile) {
    form.name = profile.name || ''
    form.username = profile.username || ''
    form.whatsapp = profile.whatsapp || authStore.userWhatsapp || ''
    form.theme_id = profile.theme_id ?? null
    if (profile.avatar?.webp) {
      form.avatar = profile.avatar.webp
      avatarPreview.value = profile.avatar.webp
    } else if (profile.avatar?.jpg) {
      form.avatar = profile.avatar.jpg
      avatarPreview.value = profile.avatar.jpg
    }
    if (profile.background?.image?.webp) {
      backgroundFile.value = profile.background.image.webp
      backgroundPreview.value = profile.background.image.webp
    } else if (profile.background?.image?.jpg) {
      backgroundFile.value = profile.background.image.jpg
      backgroundPreview.value = profile.background.image.jpg
    }
    backgroundOverlay.value = Number(profile.background?.overlay_opacity || 0)
    socialIconsPosition.value = profile.social_icons_position || 'top'
  } else {
    // prefill from user if catalog missing
    form.name = authStore.userName || ''
    form.whatsapp = authStore.userWhatsapp || ''
    form.username = authStore.userUsername || ''
  }

  snapshotInitial()
  loadPreferences()

  // Load curated themes
  try {
    isLoadingThemes.value = true
    const res = await themeService.list()
    if (res?.success && Array.isArray(res.data)) {
      themes.value = res.data
      // If no theme selected yet, pick default
      if (form.theme_id == null) {
        const def = themes.value.find((t) => t.is_default)
        if (def) form.theme_id = def.id
      }
    }
  } catch (e) {
    console.debug('load themes failed', e)
  } finally {
    isLoadingThemes.value = false
  }
})

async function saveBackground() {
  try {
    isSavingBg.value = true
    if (typeof window !== 'undefined' && backgroundFile.value instanceof window.File) {
      const res = await profileStore.uploadBackground(backgroundFile.value)
      const url = res.data?.background?.image?.webp || res.data?.background?.image?.jpg || ''
      if (url) backgroundPreview.value = url
    }
    await profileStore.updateProfile({ bg_overlay_opacity: backgroundOverlay.value })
    alert('Background tersimpan')
  } catch (e) {
    const msg = typeof e === 'string' ? e : (e?.message || 'Gagal menyimpan background')
    alert(msg)
  } finally {
    isSavingBg.value = false
  }
}

async function saveSocialPosition() {
  try {
    await profileStore.updateProfile({ social_icons_position: socialIconsPosition.value })
    alert('Posisi ikon sosial tersimpan')
  } catch (e) {
    const msg = typeof e === 'string' ? e : (e?.message || 'Gagal menyimpan posisi ikon sosial')
    alert(msg)
  }
}
</script>
