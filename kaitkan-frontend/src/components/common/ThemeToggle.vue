<template>
  <button
    @click="toggleTheme"
    class="relative p-2 rounded-lg transition-all duration-300 hover:scale-110 focus:outline-none focus:ring-2 focus:ring-primary-500 dark:focus:ring-primary-400"
    :class="[
      isDark
        ? 'bg-gray-800 text-yellow-400 hover:bg-gray-700'
        : 'bg-gray-100 text-gray-700 hover:bg-gray-200',
    ]"
    :title="isDark ? 'Switch to Light Mode' : 'Switch to Dark Mode'"
  >
    <!-- Sun Icon (Light Mode) -->
    <svg
      v-if="!isDark"
      class="w-5 h-5 transition-transform duration-500 rotate-0"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"
      />
    </svg>

    <!-- Moon Icon (Dark Mode) -->
    <svg
      v-else
      class="w-5 h-5 transition-transform duration-500 rotate-180"
      fill="none"
      stroke="currentColor"
      viewBox="0 0 24 24"
    >
      <path
        stroke-linecap="round"
        stroke-linejoin="round"
        stroke-width="2"
        d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"
      />
    </svg>

    <!-- Ripple effect -->
    <span
      v-if="ripple"
      class="absolute inset-0 rounded-lg bg-primary-500 opacity-30 animate-ping"
    ></span>
  </button>
</template>

<script setup>
import { ref } from 'vue'
import { useTheme } from '@/composables/useTheme'

const { isDark, toggleTheme: toggle } = useTheme()
const ripple = ref(false)

const toggleTheme = () => {
  ripple.value = true
  toggle()
  setTimeout(() => {
    ripple.value = false
  }, 600)
}
</script>
