<template>
  <div class="image-upload">
    <label v-if="label" class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">
      {{ label }}
      <span v-if="required" class="text-red-500 dark:text-red-400">*</span>
    </label>

    <!-- Preview Area -->
    <div
      class="upload-area"
      :class="{
        'border-red-300 dark:border-red-700': error,
        'border-blue-500 dark:border-blue-400': isDragging,
        'border-gray-300 dark:border-gray-600': !isDragging && !error,
      }"
      @click="!disabled && !previewUrl && fileInput?.click()"
      @dragover.prevent="handleDragOver"
      @dragleave.prevent="handleDragLeave"
      @drop.prevent="handleDrop"
    >
      <!-- Preview Image -->
      <div v-if="previewUrl" class="preview-container">
        <img :src="previewUrl" :alt="label" class="preview-image" />
        <button
          type="button"
          class="remove-btn"
          @click="removeImage"
          :disabled="disabled"
        >
          <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
            <path
              fill-rule="evenodd"
              d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z"
              clip-rule="evenodd"
            />
          </svg>
        </button>
      </div>

      <!-- Upload Button -->
      <div v-else class="upload-prompt">
        <svg class="upload-icon" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path
            stroke-linecap="round"
            stroke-linejoin="round"
            stroke-width="2"
            d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"
          />
        </svg>
        <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">
          <span class="font-semibold text-primary-600 dark:text-primary-400">Klik untuk upload</span> atau drag & drop
        </p>
        <p class="text-xs text-gray-500 dark:text-gray-500 mt-1">
          {{ hint || 'PNG, JPG, WebP maksimal 10MB' }}
        </p>
      </div>

      <!-- Hidden File Input -->
      <input
        ref="fileInput"
        type="file"
        accept="image/jpeg,image/jpg,image/png,image/webp"
        class="hidden"
        @change="handleFileSelect"
        :disabled="disabled"
      />
    </div>

    <!-- Error Message -->
    <p v-if="error" class="mt-1 text-sm text-red-600">
      {{ error }}
    </p>
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

const props = defineProps({
  modelValue: {
    type: [File, String, null],
    default: null,
  },
  label: {
    type: String,
    default: '',
  },
  hint: {
    type: String,
    default: '',
  },
  required: {
    type: Boolean,
    default: false,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  error: {
    type: String,
    default: '',
  },
  maxSize: {
    type: Number,
    default: 10 * 1024 * 1024, // 10MB
  },
})

const emit = defineEmits(['update:modelValue', 'change'])

const fileInput = ref(null)
const previewUrl = ref(null)
const isDragging = ref(false)

// Watch for external changes (e.g., existing image URL)
watch(
  () => props.modelValue,
  (newValue) => {
    if (typeof newValue === 'string') {
      // It's a URL
      previewUrl.value = newValue
    } else if (newValue instanceof File) {
      // It's a file, create preview
      createPreview(newValue)
    } else {
      previewUrl.value = null
    }
  },
  { immediate: true },
)

function createPreview(file) {
  const reader = new FileReader()
  reader.onload = (e) => {
    previewUrl.value = e.target.result
  }
  reader.readAsDataURL(file)
}

function validateFile(file) {
  // Check file size
  if (file.size > props.maxSize) {
    const maxMB = props.maxSize / 1024 / 1024
    return `Ukuran file terlalu besar. Maksimal ${maxMB}MB`
  }

  // Check file type
  const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']
  if (!allowedTypes.includes(file.type)) {
    return 'Format file tidak didukung. Gunakan JPG, PNG, atau WebP'
  }

  return null
}

function handleFileSelect(event) {
  const file = event.target.files[0]
  if (file) {
    const error = validateFile(file)
    if (error) {
      emit('change', { file: null, error })
      return
    }

    createPreview(file)
    emit('update:modelValue', file)
    emit('change', { file, error: null })
  }
}

function handleDragOver() {
  if (!props.disabled) {
    isDragging.value = true
  }
}

function handleDragLeave() {
  isDragging.value = false
}

function handleDrop(event) {
  isDragging.value = false
  if (props.disabled) return

  const file = event.dataTransfer.files[0]
  if (file) {
    const error = validateFile(file)
    if (error) {
      emit('change', { file: null, error })
      return
    }

    createPreview(file)
    emit('update:modelValue', file)
    emit('change', { file, error: null })
  }
}

function removeImage() {
  if (!props.disabled) {
    previewUrl.value = null
    emit('update:modelValue', null)
    emit('change', { file: null, error: null })
    if (fileInput.value) {
      fileInput.value.value = ''
    }
  }
}

// Expose click method for parent components
function click() {
  fileInput.value?.click()
}

defineExpose({ click })
</script>

<style scoped>
.upload-area {
  position: relative;
  display: flex;
  align-items: center;
  justify-content: center;
  min-height: 200px;
  border: 2px dashed;
  border-radius: 0.75rem;
  cursor: pointer;
  transition: all 0.3s;
  background-color: #ffffff;
}

.dark .upload-area {
  background-color: #111827;
}

.upload-area:hover {
  border-color: #0ea5e9;
  background-color: #f0f9ff;
}

.dark .upload-area:hover {
  border-color: #38bdf8;
  background-color: #0c4a6e;
}

.preview-container {
  position: relative;
  width: 100%;
  height: 100%;
  padding: 1rem;
}

.preview-image {
  max-width: 100%;
  max-height: 300px;
  margin: 0 auto;
  display: block;
  border-radius: 0.5rem;
  object-fit: contain;
}

.remove-btn {
  position: absolute;
  top: 1.5rem;
  right: 1.5rem;
  padding: 0.5rem;
  background-color: rgba(239, 68, 68, 0.9);
  color: white;
  border-radius: 9999px;
  transition: all 0.2s;
}

.remove-btn:hover:not(:disabled) {
  background-color: rgba(220, 38, 38, 1);
  transform: scale(1.1);
}

.remove-btn:disabled {
  opacity: 0.5;
  cursor: not-allowed;
}

.upload-prompt {
  text-align: center;
  padding: 2rem;
}

.upload-icon {
  width: 3rem;
  height: 3rem;
  margin: 0 auto;
  color: #9ca3af;
}

.dark .upload-icon {
  color: #6b7280;
}
</style>
