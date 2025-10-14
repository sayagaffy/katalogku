<template>
  <div>
    <div class="flex justify-between items-center mb-6">
      <h2 class="text-2xl font-bold text-gray-900">Kelola Produk</h2>
      <BaseButton
        variant="primary"
        @click="showAddModal = true"
        :disabled="!hasCatalog"
      >
        + Tambah Produk
      </BaseButton>
    </div>

    <!-- No Catalog Warning -->
    <div v-if="!hasCatalog" class="bg-yellow-50 border border-yellow-200 rounded-lg p-6">
      <div class="flex">
        <div class="flex-shrink-0">
          <svg class="h-5 w-5 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
            <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
          </svg>
        </div>
        <div class="ml-3">
          <h3 class="text-sm font-medium text-yellow-800">Buat katalog terlebih dahulu</h3>
          <div class="mt-2 text-sm text-yellow-700">
            <p>Anda perlu membuat katalog sebelum menambahkan produk.</p>
          </div>
          <div class="mt-4">
            <router-link
              to="/dashboard/catalog"
              class="inline-flex items-center px-4 py-2 bg-yellow-600 text-white rounded-lg hover:bg-yellow-700"
            >
              Buat Katalog
            </router-link>
          </div>
        </div>
      </div>
    </div>

    <!-- Products List -->
    <div v-else-if="!isLoading && products.length > 0" class="bg-white rounded-lg shadow">
      <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4 p-4">
        <div
          v-for="product in products"
          :key="product.id"
          class="border rounded-lg overflow-hidden hover:shadow-lg transition-shadow"
        >
          <!-- Product Image -->
          <div class="aspect-square bg-gray-100 relative">
            <img
              :src="product.image?.webp || product.image?.jpg"
              :alt="product.name"
              class="w-full h-full object-cover"
            />
            <div
              v-if="!product.in_stock"
              class="absolute top-2 right-2 bg-red-500 text-white text-xs px-2 py-1 rounded"
            >
              Stok Habis
            </div>
          </div>

          <!-- Product Info -->
          <div class="p-4">
            <h3 class="font-semibold text-gray-900 mb-1 truncate">{{ product.name }}</h3>
            <p class="text-lg font-bold text-blue-600 mb-2">{{ formatPrice(product.price) }}</p>
            <p class="text-xs text-gray-500 mb-3">
              {{ product.category }} • {{ product.view_count }} views • {{ product.click_count }} clicks
            </p>

            <!-- Actions -->
            <div class="flex space-x-2">
              <button
                @click="editProduct(product)"
                class="flex-1 px-3 py-2 bg-blue-50 text-blue-600 rounded-lg hover:bg-blue-100 text-sm font-medium"
              >
                Edit
              </button>
              <button
                @click="confirmDelete(product)"
                class="flex-1 px-3 py-2 bg-red-50 text-red-600 rounded-lg hover:bg-red-100 text-sm font-medium"
              >
                Hapus
              </button>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Empty State -->
    <div v-else-if="!isLoading && products.length === 0" class="bg-white rounded-lg shadow p-12 text-center">
      <svg class="w-16 h-16 mx-auto text-gray-400 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4" />
      </svg>
      <h3 class="text-lg font-semibold text-gray-900 mb-2">Belum ada produk</h3>
      <p class="text-gray-600 mb-6">Mulai tambahkan produk pertama Anda</p>
      <BaseButton variant="primary" @click="showAddModal = true">
        + Tambah Produk
      </BaseButton>
    </div>

    <!-- Loading State -->
    <div v-if="isLoading" class="bg-white rounded-lg shadow p-12 text-center">
      <div class="inline-block animate-spin rounded-full h-12 w-12 border-b-2 border-blue-600"></div>
      <p class="mt-4 text-gray-600">Memuat produk...</p>
    </div>

    <!-- Add/Edit Product Modal -->
    <ProductModal
      v-if="showAddModal || showEditModal"
      :product="selectedProduct"
      @close="closeModal"
      @saved="handleProductSaved"
    />

    <!-- Delete Confirmation Modal -->
    <ConfirmModal
      v-if="showDeleteModal"
      title="Hapus Produk"
      message="Apakah Anda yakin ingin menghapus produk ini? Tindakan ini tidak dapat dibatalkan."
      confirm-text="Hapus"
      @confirm="handleDelete"
      @cancel="showDeleteModal = false"
    />
  </div>
</template>

<script setup>
import { ref, computed, onMounted } from 'vue'
import { useCatalogStore } from '@/stores/catalog'
import { useProductStore } from '@/stores/product'
import { formatPrice } from '@/utils/helpers'
import BaseButton from '@/components/common/BaseButton.vue'
import ProductModal from '@/components/dashboard/ProductModal.vue'
import ConfirmModal from '@/components/common/ConfirmModal.vue'

const catalogStore = useCatalogStore()
const productStore = useProductStore()

const showAddModal = ref(false)
const showEditModal = ref(false)
const showDeleteModal = ref(false)
const selectedProduct = ref(null)

const hasCatalog = computed(() => !!catalogStore.catalog)
const products = computed(() => productStore.products)
const isLoading = computed(() => productStore.isLoading)

function editProduct(product) {
  selectedProduct.value = product
  showEditModal.value = true
}

function confirmDelete(product) {
  selectedProduct.value = product
  showDeleteModal.value = true
}

async function handleDelete() {
  try {
    await productStore.deleteProduct(selectedProduct.value.id)
    showDeleteModal.value = false
    selectedProduct.value = null
  } catch (error) {
    alert(error || 'Gagal menghapus produk')
  }
}

function closeModal() {
  showAddModal.value = false
  showEditModal.value = false
  selectedProduct.value = null
}

async function handleProductSaved() {
  closeModal()
  await productStore.fetchProducts()
}

onMounted(async () => {
  try {
    await catalogStore.fetchCatalog()
    if (hasCatalog.value) {
      await productStore.fetchProducts()
    }
  } catch (error) {
    console.error('Error loading products:', error)
  }
})
</script>
