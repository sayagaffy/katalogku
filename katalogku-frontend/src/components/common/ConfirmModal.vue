<template>
  <div class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 p-4">
    <div class="bg-white rounded-lg max-w-md w-full p-6" @click.stop>
      <h3 class="text-lg font-semibold text-gray-900 mb-4">{{ title }}</h3>
      <p class="text-gray-600 mb-6">{{ message }}</p>

      <div class="flex justify-end space-x-3">
        <BaseButton
          variant="secondary"
          @click="$emit('cancel')"
        >
          Batal
        </BaseButton>
        <BaseButton
          variant="danger"
          @click="$emit('confirm')"
        >
          {{ confirmText }}
        </BaseButton>
      </div>
    </div>
  </div>
</template>

<script setup>
import BaseButton from './BaseButton.vue'

defineProps({
  title: {
    type: String,
    required: true,
  },
  message: {
    type: String,
    required: true,
  },
  confirmText: {
    type: String,
    default: 'Konfirmasi',
  },
})

defineEmits(['confirm', 'cancel'])
</script>
