<template>
  <div class="space-y-6">
    <div>
      <h2 class="text-2xl font-extrabold text-gray-900">Pengaturan</h2>
      <p class="text-gray-500">Kelola profil, preferensi, dan keamanan akun Anda</p>
    </div>

    <!-- Profile -->
    <section class="bg-white rounded-2xl shadow p-6">
      <h3 class="text-lg font-bold text-gray-900 mb-4">Profil</h3>
      <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Avatar -->
        <div class="flex items-center gap-4">
          <div class="w-20 h-20 rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 text-white flex items-center justify-center text-2xl font-bold">
            {{ initials(form.name) }}
          </div>
          <div>
            <p class="font-semibold text-gray-900">Foto Profil</p>
            <p class="text-sm text-gray-500">Gambar akan tampil di katalog publik</p>
            <div class="mt-2">
              <BaseButton variant="secondary" size="sm" disabled title="Ubah foto (segera)">Pilih Gambar</BaseButton>
            </div>
          </div>
        </div>

        <!-- Fields -->
        <div class="lg:col-span-2 grid grid-cols-1 md:grid-cols-2 gap-4">
          <BaseInput v-model="form.name" label="Nama" placeholder="Nama lengkap" />
          <BaseInput v-model="form.username" label="Username" placeholder="mis. tokofashion" hint="Digunakan untuk URL katalog" />
          <BaseInput v-model="form.whatsapp" label="Nomor WhatsApp" type="tel" placeholder="08xxxxxxxxxx" />
          <BaseInput v-model="form.email" label="Email" type="email" placeholder="nama@domain.com" />
        </div>
      </div>
      <div class="mt-6 flex items-center gap-3">
        <BaseButton disabled title="Simpan (segera)">Simpan Perubahan</BaseButton>
        <BaseButton variant="ghost" @click="resetProfile">Reset</BaseButton>
      </div>
    </section>

    <!-- Preferences & Notifications -->
    <section class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Preferences -->
      <div class="bg-white rounded-2xl shadow p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Preferensi</h3>
        <div class="space-y-5">
          <div class="flex items-center justify-between">
            <div>
              <p class="font-semibold text-gray-900">Tema</p>
              <p class="text-sm text-gray-500">Atur mode terang/gelap</p>
            </div>
            <ThemeToggle />
          </div>

          <div class="flex items-center justify-between">
            <div>
              <p class="font-semibold text-gray-900">Bahasa</p>
              <p class="text-sm text-gray-500">Saat ini: Indonesia</p>
            </div>
            <select class="px-3 py-2 rounded-lg border border-gray-200 text-sm bg-white" disabled title="Ganti bahasa (segera)">
              <option>Indonesia</option>
              <option>English</option>
            </select>
          </div>
        </div>
      </div>

      <!-- Notifications -->
      <div class="bg-white rounded-2xl shadow p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Notifikasi</h3>
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
          <BaseButton disabled full-width title="Simpan (segera)">Simpan Preferensi</BaseButton>
        </div>
      </div>
    </section>

    <!-- Security -->
    <section class="bg-white rounded-2xl shadow p-6">
      <h3 class="text-lg font-bold text-gray-900 mb-4">Keamanan</h3>
      <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <BaseInput v-model="security.current" type="password" label="Password saat ini" placeholder="••••••••" />
        <BaseInput v-model="security.newPass" type="password" label="Password baru" placeholder="••••••••" />
        <BaseInput v-model="security.confirm" type="password" label="Konfirmasi password" placeholder="••••••••" />
      </div>
      <div class="mt-4">
        <BaseButton disabled title="Ganti password (segera)">Ganti Password</BaseButton>
      </div>
      <div class="mt-6 p-4 rounded-xl bg-yellow-50 border border-yellow-200 text-sm text-yellow-800">
        Disarankan gunakan password kuat dan unik. Otentikasi 2-langkah akan tersedia segera.
      </div>
    </section>

    <!-- Danger Zone -->
    <section class="rounded-2xl border border-red-200 bg-red-50 p-6">
      <h3 class="text-lg font-bold text-red-700 mb-2">Zona Berbahaya</h3>
      <p class="text-sm text-red-700">Hapus akun dan semua data Anda secara permanen.</p>
      <div class="mt-4">
        <BaseButton variant="danger" disabled title="Hapus akun (segera)">Hapus Akun</BaseButton>
      </div>
    </section>
  </div>
</template>

<script setup>
import { reactive } from 'vue'
import BaseInput from '@/components/common/BaseInput.vue'
import BaseButton from '@/components/common/BaseButton.vue'
import ThemeToggle from '@/components/common/ThemeToggle.vue'

const form = reactive({
  name: 'Test User',
  username: 'tokofashion',
  whatsapp: '081234567890',
  email: 'user@example.com',
})

function resetProfile() {
  form.name = 'Test User'
  form.username = 'tokofashion'
  form.whatsapp = '081234567890'
  form.email = 'user@example.com'
}

const notify = reactive({ weekly: true, clicks: true, updates: true })

const security = reactive({ current: '', newPass: '', confirm: '' })

function initials(name) {
  if (!name) return 'K'
  return name
    .split(' ')
    .map((n) => n[0])
    .join('')
    .slice(0, 2)
    .toUpperCase()
}
</script>

<script>
export default {
  components: {
    ToggleRow: {
      props: { label: String, description: String, checked: Boolean },
      emits: ['update:checked'],
      methods: {
        onToggle(e) { this.$emit('update:checked', e.target.checked) },
      },
      template: `
        <div class="flex items-start justify-between gap-4">
          <div class="min-w-0">
            <p class="font-semibold text-gray-900">{{ label }}</p>
            <p class="text-sm text-gray-500">{{ description }}</p>
          </div>
          <label class="relative inline-flex items-center cursor-pointer select-none">
            <input type="checkbox" class="sr-only peer" :checked="checked" @change="onToggle" />
            <div class="w-11 h-6 bg-gray-200 rounded-full peer-checked:bg-green-500 transition-colors"></div>
            <div class="absolute left-0.5 top-0.5 w-5 h-5 bg-white rounded-full shadow transform transition-transform peer-checked:translate-x-5"></div>
          </label>
        </div>
      `,
    },
  },
}
</script>
