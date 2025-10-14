<template>
  <div>
    <h2 class="text-2xl font-bold text-gray-900 mb-6">Dashboard</h2>

    <!-- Welcome Card -->
    <div class="bg-white rounded-lg shadow p-6 mb-6">
      <h3 class="text-lg font-semibold text-gray-900 mb-2">
        Selamat datang, {{ userName }}!
      </h3>
      <p class="text-gray-600 mb-4">
        Kelola katalog dan produk Anda dengan mudah
      </p>

      <!-- Quick Actions -->
      <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        <router-link
          to="/dashboard/catalog"
          class="flex items-center p-4 bg-blue-50 rounded-lg hover:bg-blue-100 transition-colors"
        >
          <div class="flex-shrink-0">
            <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4" />
            </svg>
          </div>
          <div class="ml-4">
            <p class="font-semibold text-gray-900">Setup Katalog</p>
            <p class="text-sm text-gray-600">Atur nama, username, dan deskripsi</p>
          </div>
        </router-link>

        <router-link
          to="/dashboard/products"
          class="flex items-center p-4 bg-green-50 rounded-lg hover:bg-green-100 transition-colors"
        >
          <div class="flex-shrink-0">
            <svg class="w-8 h-8 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
          </div>
          <div class="ml-4">
            <p class="font-semibold text-gray-900">Kelola Produk</p>
            <p class="text-sm text-gray-600">Tambah, edit, atau hapus produk</p>
          </div>
        </router-link>
      </div>
    </div>

    <!-- Stats Cards -->
    <div v-if="catalog" class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Total Produk</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.totalProducts }}</p>
          </div>
          <div class="p-3 bg-blue-100 rounded-full">
            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Produk Tersedia</p>
            <p class="text-2xl font-bold text-gray-900">{{ stats.inStock }}</p>
          </div>
          <div class="p-3 bg-green-100 rounded-full">
            <svg class="w-6 h-6 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
            </svg>
          </div>
        </div>
      </div>

      <div class="bg-white rounded-lg shadow p-6">
        <div class="flex items-center justify-between">
          <div>
            <p class="text-sm text-gray-600">Link Katalog</p>
            <p class="text-sm font-semibold text-blue-600 truncate">{{ catalogUrl }}</p>
          </div>
          <button
            @click="copyCatalogUrl"
            class="p-3 bg-purple-100 rounded-full hover:bg-purple-200 transition-colors"
          >
            <svg class="w-6 h-6 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z" />
            </svg>
          </button>
        </div>
      </div>
    </div>

    <!-- No Catalog Warning -->
    <div v-else class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-yellow-800">Katalog belum dibuat</h3>
          <div class="mt-2 text-sm text-yellow-700">
            <p>Anda perlu membuat katalog terlebih dahulu sebelum menambahkan produk.</p>
          </div>
          <div class="mt-4">
            <router-link
              to="/dashboard/catalog"
              class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700 transition-colors"
            >
              Buat Katalog Sekarang
            </router-link>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useAuthStore } from '@/stores/auth'
import { useCatalogStore } from '@/stores/catalog'
import { useProductStore } from '@/stores/product'
import { copyToClipboard } from '@/utils/helpers'

const authStore = useAuthStore()
const catalogStore = useCatalogStore()
const productStore = useProductStore()

const catalog = computed(() => catalogStore.catalog)
const userName = computed(() => authStore.userName)
const catalogUrl = computed(() => {
  if (catalog.value?.username) {
    return `${window.location.origin}/c/${catalog.value.username}`
  }
  return ''
})

const stats = computed(() => {
  if (!catalog.value) {
    return { totalProducts: 0, inStock: 0 }
  }
  return {
    totalProducts: catalog.value.products?.length || 0,
    inStock: catalog.value.products?.filter(p => p.in_stock)?.length || 0,
  }
})

async function copyCatalogUrl() {
  if (catalogUrl.value) {
    const success = await copyToClipboard(catalogUrl.value)
    if (success) {
      alert('Link katalog berhasil disalin!')
    }
  }
}

onMounted(async () => {
  try {
    await catalogStore.fetchCatalog()
    if (catalog.value) {
      await productStore.fetchProducts()
    }
  } catch (error) {
    // Catalog doesn't exist yet, that's okay
    console.log('No catalog yet')
  }
})
</script>
