<template>
  <div class="min-h-screen flex items-center justify-center bg-gray-50 px-4">
    <div class="text-center">
      <h1 class="text-6xl font-bold text-gray-900 mb-4">404</h1>
      <p class="text-xl text-gray-600 mb-8">Halaman tidak ditemukan</p>
      <BaseButton
        variant="primary"
        @click="goHome"
      >
        Kembali ke Beranda
      </BaseButton>
    </div>
  </div>
</template>

<script setup>
import { useRouter } from 'vue-router'
import BaseButton from '@/components/common/BaseButton.vue'

const router = useRouter()

function goHome() {
  router.push('/')
}
</script>
