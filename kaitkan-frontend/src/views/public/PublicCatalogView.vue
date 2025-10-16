<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900" :class="themeClass">
    <!-- Loading State -->
    <div v-if="isLoading" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 dark:border-primary-400"></div>
        <p class="mt-4 text-gray-600 dark:text-gray-400">Memuat katalog...</p>
      </div>
    </div>

    <!-- Error State -->
    <div v-else-if="error" class="flex items-center justify-center min-h-screen p-4">
      <div class="text-center max-w-md">
        <svg class="w-16 h-16 mx-auto text-red-500 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
        </svg>
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Katalog Tidak Ditemukan</h1>
        <p class="text-gray-600 dark:text-gray-400 mb-6">{{ error }}</p>
        <a :href="siteUrl" class="inline-flex items-center px-6 py-3 bg-primary-600 text-white rounded-lg hover:bg-primary-700">
          Buat Katalog Anda
        </a>
      </div>
    </div>

    <!-- Catalog Content -->
    <div v-else-if="catalog" class="pb-20">
      <!-- Header -->
      <div class="bg-white dark:bg-gray-800 shadow-sm">
        <div class="max-w-4xl mx-auto px-4 py-8">
          <div class="flex flex-col md:flex-row items-center md:items-start gap-6">
            <!-- Avatar -->
            <div class="flex-shrink-0">
              <img
                v-if="catalog.avatar?.webp"
                :src="catalog.avatar.webp"
                :alt="catalog.name"
                class="w-24 h-24 md:w-32 md:h-32 rounded-full object-cover border-4 border-primary-500 dark:border-primary-400"
              />
              <div v-else class="w-24 h-24 md:w-32 md:h-32 rounded-full bg-gradient-to-br from-primary-500 to-secondary-500 flex items-center justify-center text-white text-4xl font-bold">
                {{ catalog.name.charAt(0).toUpperCase() }}
              </div>
            </div>

            <!-- Info -->
            <div class="flex-1 text-center md:text-left">
              <h1 class="text-3xl font-bold text-gray-900 dark:text-gray-100 mb-2">
                {{ catalog.name }}
              </h1>
              <p v-if="catalog.category" class="inline-block px-3 py-1 bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300 rounded-full text-sm font-medium mb-3">
                {{ catalog.category }}
              </p>
              <p v-if="catalog.description" class="text-gray-600 dark:text-gray-400 mb-4">
                {{ catalog.description }}
              </p>

              <!-- WhatsApp Button -->
              <a
                v-if="catalog.whatsapp"
                :href="whatsappUrl"
                target="_blank"
                rel="noopener noreferrer"
                class="inline-flex items-center gap-2 px-6 py-3 bg-green-500 hover:bg-green-600 text-white rounded-lg font-medium transition-colors"
              >
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                  <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/>
                </svg>
                Hubungi via WhatsApp
              </a>
            </div>
          </div>
        </div>
      </div>

      <!-- Products Grid -->
      <div class="max-w-6xl mx-auto px-4 py-8">
        <!-- Empty State -->
        <div v-if="!catalog.products || catalog.products.length === 0" class="text-center py-12">
          <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
          </svg>
          <h3 class="text-lg font-semibold text-gray-900 dark:text-gray-100 mb-2">Belum ada produk</h3>
          <p class="text-gray-600 dark:text-gray-400">Produk akan segera ditambahkan</p>
        </div>

        <!-- Products List -->
        <div v-else class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6">
          <div
            v-for="product in catalog.products"
            :key="product.id"
            @click="handleProductClick(product)"
            class="bg-white dark:bg-gray-800 rounded-xl shadow-sm hover:shadow-lg transition-shadow cursor-pointer overflow-hidden group"
          >
            <!-- Product Image -->
            <div class="aspect-square bg-gray-100 dark:bg-gray-700 relative overflow-hidden">
              <img
                :src="product.image?.webp || product.image?.jpg"
                :alt="product.name"
                class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300"
              />
              <div v-if="!product.in_stock" class="absolute top-3 right-3 bg-red-500 text-white text-xs px-3 py-1 rounded-full font-medium">
                Stok Habis
              </div>
            </div>

            <!-- Product Info -->
            <div class="p-4">
              <h3 class="font-semibold text-gray-900 dark:text-gray-100 mb-2 line-clamp-2">
                {{ product.name }}
              </h3>
              <p class="text-2xl font-bold text-primary-600 dark:text-primary-400 mb-2">
                {{ formatPrice(product.price) }}
              </p>
              <p v-if="product.description" class="text-sm text-gray-600 dark:text-gray-400 line-clamp-2 mb-3">
                {{ product.description }}
              </p>
              <div class="flex items-center justify-between text-xs text-gray-500 dark:text-gray-500">
                <span>{{ product.view_count || 0 }} views</span>
                <span>{{ product.click_count || 0 }} clicks</span>
              </div>
            </div>
          </div>
        </div>
      </div>

      <!-- Footer -->
      <div class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-3">
        <div class="max-w-6xl mx-auto px-4 text-center text-sm text-gray-600 dark:text-gray-400">
          Dibuat dengan <a :href="siteUrl" class="text-primary-600 dark:text-primary-400 hover:underline font-medium">Kaitkan</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute } from 'vue-router'
import { useCatalogStore } from '@/stores/catalog'
import { productService } from '@/services/product.service'
import { formatPrice } from '@/utils/helpers'
import { useWhatsApp } from '@/composables/useWhatsApp'

// Public site URL for branding links
const siteUrl = import.meta.env.VITE_PUBLIC_URL || window.location.origin

const route = useRoute()
const catalogStore = useCatalogStore()
const { generateOrderLink, trackClick } = useWhatsApp()

const catalog = ref(null)
const isLoading = ref(true)
const error = ref(null)

const username = computed(() => route.params.username)

const themeClass = computed(() => {
  if (!catalog.value?.theme || catalog.value.theme === 'default') {
    return ''
  }
  return `theme-${catalog.value.theme}`
})

const whatsappUrl = computed(() => {
  if (!catalog.value?.whatsapp) return ''
  const phone = catalog.value.whatsapp.replace(/\D/g, '')
  const message = encodeURIComponent(`Halo ${catalog.value.name}, saya tertarik dengan produk Anda`)
  return `https://wa.me/${phone}?text=${message}`
})

async function loadCatalog() {
  try {
    isLoading.value = true
    error.value = null

    const response = await catalogStore.getPublicCatalog(username.value)
    if (response.success) {
      catalog.value = response.data
    }
  } catch (err) {
    error.value = err || 'Katalog tidak ditemukan'
    console.error('Error loading catalog:', err)
  } finally {
    isLoading.value = false
  }
}

async function handleProductClick(product) {
  try {
    // Track click
    await trackClick(product.id)

    // Redirect to external link or WhatsApp
    if (product.external_link) {
      window.open(product.external_link, '_blank')
    } else if (catalog.value.whatsapp) {
      const url = generateOrderLink(product, catalog.value.whatsapp, catalog.value.name)
      window.open(url, '_blank')
    }
  } catch (err) {
    console.error('Error tracking click:', err)
  }
}

onMounted(() => {
  loadCatalog()
})
</script>

<style scoped>
.line-clamp-2 {
  display: -webkit-box;
  -webkit-line-clamp: 2;
  -webkit-box-orient: vertical;
  overflow: hidden;
}

/* Theme variations */
.theme-blue {
  --primary-50: #eff6ff;
  --primary-600: #2563eb;
  --primary-700: #1d4ed8;
}

.theme-green {
  --primary-50: #f0fdf4;
  --primary-600: #16a34a;
  --primary-700: #15803d;
}

.theme-purple {
  --primary-50: #faf5ff;
  --primary-600: #9333ea;
  --primary-700: #7e22ce;
}

.theme-pink {
  --primary-50: #fdf2f8;
  --primary-600: #db2777;
  --primary-700: #be185d;
}
</style>
