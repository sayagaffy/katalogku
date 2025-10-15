<template>
  <div class="min-h-screen bg-gray-100">
    <!-- Top Navigation -->
    <nav class="bg-white shadow-sm">
      <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
          <div class="flex">
            <div class="flex-shrink-0 flex items-center">
              <h1 class="text-xl font-bold text-gray-900">Kaitkan</h1>
            </div>
          </div>
          <div class="flex items-center space-x-4">
            <span class="text-sm text-gray-700">{{ userName }}</span>
            <BaseButton
              variant="ghost"
              size="sm"
              @click="handleLogout"
            >
              Keluar
            </BaseButton>
          </div>
        </div>
      </div>
    </nav>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
      <!-- Navigation Tabs -->
      <div class="px-4 sm:px-0">
        <div class="border-b border-gray-200">
          <nav class="-mb-px flex space-x-8">
            <router-link
              v-for="tab in tabs"
              :key="tab.name"
              :to="tab.to"
              :class="[
                isActiveTab(tab.to)
                  ? 'border-blue-500 text-blue-600'
                  : 'border-transparent text-gray-500 hover:text-gray-700 hover:border-gray-300',
                'whitespace-nowrap py-4 px-1 border-b-2 font-medium text-sm',
              ]"
            >
              {{ tab.label }}
            </router-link>
          </nav>
        </div>
      </div>

      <!-- Tab Content -->
      <div class="mt-6 px-4 sm:px-0">
        <router-view />
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRouter, useRoute } from 'vue-router'
import { useAuthStore } from '@/stores/auth'
import BaseButton from '@/components/common/BaseButton.vue'

const router = useRouter()
const route = useRoute()
const authStore = useAuthStore()

const userName = computed(() => authStore.userName || 'Pengguna')

const tabs = [
  { name: 'home', label: 'Beranda', to: '/dashboard' },
  { name: 'products', label: 'Produk', to: '/dashboard/products' },
  { name: 'catalog', label: 'Katalog', to: '/dashboard/catalog' },
  { name: 'analytics', label: 'Analitik', to: '/dashboard/analytics' },
  { name: 'settings', label: 'Pengaturan', to: '/dashboard/settings' },
]

function isActiveTab(path) {
  if (path === '/dashboard') {
    return route.path === '/dashboard'
  }
  return route.path.startsWith(path)
}

async function handleLogout() {
  try {
    await authStore.logout()
    router.push('/login')
  } catch (error) {
    console.error('Logout error:', error)
    // Force logout even if API call fails
    router.push('/login')
  }
}
</script>
