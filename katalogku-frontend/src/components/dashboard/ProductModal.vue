<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4 overflow-y-auto">
    <div class="bg-white rounded-lg max-w-2xl w-full p-6 my-8" @click.stop>
      <h3 class="text-xl font-bold text-gray-900 mb-6">
        {{ isEdit ? 'Edit Produk' : 'Tambah Produk' }}
      </h3>

      <form @submit.prevent="handleSubmit" class="space-y-6">
        <!-- Product Name -->
        <BaseInput
          v-model="form.name"
          label="Nama Produk"
          placeholder="Contoh: Kaos Polos Premium"
          :error="validation.getError('name')"
          required
          maxlength="200"
          @blur="validateName"
        />

        <!-- Price & Category Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
          <!-- Price -->
          <BaseInput
            v-model="form.price"
            label="Harga"
            type="number"
            placeholder="50000"
            :error="validation.getError('price')"
            required
            min="0"
            step="1000"
            @blur="validatePrice"
          />

          <!-- Category -->
          <div>
            <label class="block text-sm font-medium text-gray-700 mb-1">
              Kategori <span class="text-red-500">*</span>
            </label>
            <select
              v-model="form.category"
              class="block w-full px-3 py-2 border border-gray-300 rounded-lg shadow-sm focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500"
              required
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
        </div>

        <!-- Product Image -->
        <ImageUpload
          v-model="form.image"
          :label="isEdit ? 'Gambar Produk (kosongkan jika tidak ingin mengubah)' : 'Gambar Produk'"
          hint="Rekomendasi: 800x800px, maksimal 10MB"
          :required="!isEdit"
          @change="handleImageChange"
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
            placeholder="Deskripsi produk (opsional)..."
            maxlength="1000"
          ></textarea>
          <p class="mt-1 text-sm text-gray-500">
            {{ form.description?.length || 0 }}/1000 karakter
          </p>
        </div>

        <!-- External Link -->
        <BaseInput
          v-model="form.external_link"
          label="Link Eksternal (Opsional)"
          type="url"
          placeholder="https://shopee.co.id/produk"
          hint="Link ke Shopee/Tokopedia (jika ada)"
        />

        <!-- Stock Status -->
        <div class="flex items-center">
          <input
            id="in_stock"
            v-model="form.in_stock"
            type="checkbox"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          />
          <label for="in_stock" class="ml-2 block text-sm text-gray-900">
            Produk tersedia / dalam stok
          </label>
        </div>

        <!-- Error Message -->
        <div v-if="errorMessage" class="rounded-md bg-red-50 p-4">
          <p class="text-sm text-red-800">{{ errorMessage }}</p>
        </div>

        <!-- Actions -->
        <div class="flex justify-end space-x-3 pt-4 border-t">
          <BaseButton
            type="button"
            variant="secondary"
            @click="$emit('close')"
          >
            Batal
          </BaseButton>
          <BaseButton
            type="submit"
            variant="primary"
            :loading="isLoading"
            :disabled="isLoading"
          >
            {{ isEdit ? 'Perbarui' : 'Tambah' }} Produk
          </BaseButton>
        </div>
      </form>
    </div>
  </div>
</template>

<script setup>
import { ref, reactive, computed, onMounted } from 'vue'
import { useProductStore } from '@/stores/product'
import { useFormValidation } from '@/composables/useFormValidation'
import BaseInput from '@/components/common/BaseInput.vue'
import BaseButton from '@/components/common/BaseButton.vue'
import ImageUpload from '@/components/common/ImageUpload.vue'

const props = defineProps({
  product: {
    type: Object,
    default: null,
  },
})

const emit = defineEmits(['close', 'saved'])

const productStore = useProductStore()
const validation = useFormValidation()

const form = reactive({
  name: '',
  price: '',
  category: '',
  image: null,
  description: '',
  external_link: '',
  in_stock: true,
})

const isLoading = ref(false)
const errorMessage = ref('')
const isEdit = computed(() => !!props.product)

function validateName() {
  const error = validation.validateProductName(form.name)
  if (error) {
    validation.setError('name', error)
  } else {
    validation.clearError('name')
  }
}

function validatePrice() {
  const error = validation.validatePrice(form.price)
  if (error) {
    validation.setError('price', error)
  } else {
    validation.clearError('price')
  }
}

function handleImageChange({ file, error }) {
  if (error) {
    errorMessage.value = error
  } else {
    errorMessage.value = ''
  }
}

async function handleSubmit() {
  console.log('=== Product Submit Started ===')
  console.log('Is Edit:', isEdit.value)
  console.log('Form data:', { ...form, image: form.image ? (form.image instanceof File ? 'FILE' : 'URL') : null })

  // Validate required fields
  validateName()
  validatePrice()

  console.log('Validation errors:', validation.errors.value)
  console.log('Has errors:', validation.hasErrors.value)

  if (!form.category) {
    console.log('Error: Category not selected')
    errorMessage.value = 'Kategori harus dipilih'
    return
  }

  if (!isEdit.value && !form.image) {
    console.log('Error: Image not uploaded (create mode)')
    errorMessage.value = 'Gambar produk harus diupload'
    return
  }

  if (validation.hasErrors.value) {
    console.log('Error: Validation failed')
    return
  }

  isLoading.value = true
  errorMessage.value = ''

  try {
    console.log('Calling productStore...')
    if (isEdit.value) {
      console.log('Update product:', props.product.id)
      await productStore.updateProduct(props.product.id, form)
    } else {
      console.log('Create product')
      await productStore.createProduct(form)
    }
    console.log('Product saved successfully')
    emit('saved')
  } catch (error) {
    console.error('Product save error:', error)
    errorMessage.value = error || 'Gagal menyimpan produk. Silakan coba lagi.'
  } finally {
    isLoading.value = false
  }
}

onMounted(() => {
  if (props.product) {
    // Populate form with existing product data
    form.name = props.product.name
    form.price = props.product.price
    form.category = props.product.category
    form.description = props.product.description || ''
    form.external_link = props.product.external_link || ''
    form.in_stock = props.product.in_stock

    // Set image preview
    if (props.product.image?.webp) {
      form.image = props.product.image.webp
    }
  }
})
</script>
