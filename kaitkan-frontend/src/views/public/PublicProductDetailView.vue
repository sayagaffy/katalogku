<template>
  <div class="min-h-screen bg-gray-50 dark:bg-gray-900">
    <div v-if="isLoading" class="flex items-center justify-center min-h-screen">
      <div class="text-center">
        <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-primary-600 dark:border-primary-400"></div>
        <p class="mt-4 text-gray-600 dark:text-gray-400">Memuat produk...</p>
      </div>
    </div>

    <div v-else-if="!product || !catalog" class="flex items-center justify-center min-h-screen p-4">
      <div class="text-center max-w-md">
        <h1 class="text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">Produk tidak ditemukan</h1>
        <router-link :to="{ name: 'public-catalog', params: { username } }" class="text-primary-600 dark:text-primary-400 hover:underline">Kembali ke katalog</router-link>
      </div>
    </div>

    <div v-else>
      <!-- Header -->
      <div class="bg-white dark:bg-gray-800 border-b border-gray-200 dark:border-gray-700">
        <div class="max-w-4xl mx-auto px-4 py-3 flex items-center gap-3">
          <button @click="goBack" class="p-2 rounded-lg hover:bg-gray-100 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200" aria-label="Kembali">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
          </button>
          <div>
            <h1 class="text-lg font-semibold text-gray-900 dark:text-gray-100">{{ catalog.name }}</h1>
            <p class="text-xs text-gray-500 dark:text-gray-400">@{{ username }}</p>
          </div>
        </div>
      </div>

      <!-- Content -->
      <div class="max-w-4xl mx-auto px-4 py-6">
        <div class="bg-white dark:bg-gray-800 rounded-2xl overflow-hidden shadow-sm">
          <div class="grid grid-cols-1 md:grid-cols-2 gap-0">
            <!-- Image -->
            <div class="bg-gray-100 dark:bg-gray-700">
              <img :src="product.image?.webp || product.image?.jpg" :alt="product.name" class="w-full h-full object-cover" />
            </div>

            <!-- Detail -->
            <div class="p-5 md:p-6">
              <h2 class="text-xl md:text-2xl font-bold text-gray-900 dark:text-gray-100 mb-2">{{ product.name }}</h2>
              <p class="text-lg md:text-2xl font-extrabold text-primary-600 dark:text-primary-400 mb-3">{{ formatPrice(product.price) }}</p>
              <p v-if="product.category" class="inline-block mb-4 px-2 py-1 rounded-full text-xs bg-primary-100 dark:bg-primary-900 text-primary-700 dark:text-primary-300">{{ product.category }}</p>
              <p v-if="product.description" class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed mb-6">{{ product.description }}</p>

              <div class="flex flex-col sm:flex-row gap-3">
                <a :href="whatsappLink" target="_blank" rel="noopener noreferrer" @click="trackProductClick" class="flex-1 inline-flex items-center justify-center gap-2 px-5 py-3 rounded-xl bg-green-500 hover:bg-green-600 text-white font-medium">
                  <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413Z"/></svg>
                  Hubungi via WhatsApp
                </a>

                <router-link :to="{ name: 'public-catalog', params: { username } }" class="sm:w-40 inline-flex items-center justify-center px-5 py-3 rounded-xl bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-600 text-gray-800 dark:text-gray-100 font-medium">Kembali</router-link>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="fixed bottom-0 left-0 right-0 bg-white dark:bg-gray-800 border-t border-gray-200 dark:border-gray-700 py-3">
        <div class="max-w-4xl mx-auto px-4 text-center text-sm text-gray-600 dark:text-gray-400">
          Dibuat dengan <a :href="siteUrl" class="text-primary-600 dark:text-primary-400 hover:underline font-medium">Kaitkan</a>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useRoute, useRouter } from 'vue-router'
import { useCatalogStore } from '@/stores/catalog'
import { formatPrice, formatWhatsappNumber, generateWhatsAppUrl } from '@/utils/helpers'
import { useWhatsApp } from '@/composables/useWhatsApp'

const siteUrl = import.meta.env.VITE_PUBLIC_URL || window.location.origin

const route = useRoute()
const router = useRouter()
const catalogStore = useCatalogStore()
const { trackClick } = useWhatsApp()

const username = computed(() => route.params.username)
const productId = computed(() => Number(route.params.productId))

const isLoading = ref(true)
const catalog = ref(null)

// Allow navigation state hydration for faster transition
const navState = window.history.state || {}
if (navState?.catalog && navState?.product) {
  catalog.value = navState.catalog
}

const product = computed(() => {
  const list = catalog.value?.products || []
  return list.find(p => Number(p.id) === productId.value)
})

const whatsappLink = computed(() => {
  if (!catalog.value?.whatsapp || !product.value) return ''
  const phone = formatWhatsappNumber(catalog.value.whatsapp)
  const img = product.value.image?.webp || product.value.image?.jpg || ''
  const lines = [
    `Halo ${catalog.value.name}, saya tertarik dengan produk berikut:`,
    '',
    `${product.value.name} - ${formatPrice(product.value.price)}`,
    product.value.category ? `Kategori: ${product.value.category}` : '',
    img ? `Foto: ${img}` : '',
    '',
    'Mohon info ketersediaan dan cara order.'
  ].filter(Boolean)
  return generateWhatsAppUrl(phone, lines.join('\n'))
})

function goBack() {
  router.back()
}

async function loadCatalog() {
  try {
    isLoading.value = true
    const res = await catalogStore.getPublicCatalog(username.value)
    if (res?.success) {
      catalog.value = res.data
    }
  } catch (e) {
    // no-op
  } finally {
    isLoading.value = false
  }
}

function trackProductClick() {
  if (product.value?.id) trackClick(product.value.id)
}

onMounted(() => {
  if (!catalog.value) {
    loadCatalog()
  } else {
    isLoading.value = false
  }
})
</script>

<style scoped>
</style>

