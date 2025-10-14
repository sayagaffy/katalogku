<template>
  <div class="min-h-screen bg-gray-50">
    <div class="max-w-4xl mx-auto px-4 py-8">
      <h2 class="text-2xl font-bold text-gray-900 mb-6">Katalog Publik</h2>

      <div class="bg-white rounded-lg shadow p-6">
        <p class="text-gray-600">
          Halaman katalog publik sedang dalam pengembangan.
          <br />
          Username: {{ username }}
        </p>
      </div>
    </div>
  </div>
</template>

<script setup>
import { computed } from 'vue'
import { useRoute } from 'vue-router'

const route = useRoute()
const username = computed(() => route.params.username)
</script>
