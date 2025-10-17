<template>
  <svg
    v-if="icon"
    :viewBox="icon.viewBox"
    :width="size"
    :height="size"
    fill="currentColor"
    aria-hidden="true"
  >
    <path v-for="(d,i) in icon.paths" :key="i" :d="d" />
  </svg>
  <svg v-else :width="size" :height="size" viewBox="0 0 24 24" fill="currentColor">
    <path d="M3 12a9 9 0 1118 0 9 9 0 01-18 0zm5-1h8v2H8v-2z" />
  </svg>
  
</template>

<script setup>
import { computed } from 'vue'

const props = defineProps({
  name: { type: String, required: true },
  size: { type: [Number, String], default: 20 },
})

// Minimal inline icon set (paths simplified). Easily extendable.
const ICONS = {
  instagram: {
    viewBox: '0 0 24 24',
    paths: [
      'M7 2h10a5 5 0 015 5v10a5 5 0 01-5 5H7a5 5 0 01-5-5V7a5 5 0 015-5zm0 2a3 3 0 00-3 3v10a3 3 0 003 3h10a3 3 0 003-3V7a3 3 0 00-3-3H7zm5 3a5 5 0 110 10 5 5 0 010-10zm6.5-.75a1.25 1.25 0 110 2.5 1.25 1.25 0 010-2.5z',
    ],
  },
  whatsapp: {
    viewBox: '0 0 24 24',
    paths: [
      'M20.52 3.48A11.8 11.8 0 0012.05 0C5.49 0 .16 5.33.16 11.88c0 2.1.55 4.15 1.59 5.95L0 24l6.3-1.65a11.9 11.9 0 005.75 1.45h.01c6.56 0 11.89-5.33 11.92-11.88A11.82 11.82 0 0020.52 3.48zM12.06 21a9.9 9.9 0 01-5.05-1.38l-.36-.21-3.74.98 1-3.64-.24-.37a9.86 9.86 0 1116.95-3.86 9.88 9.88 0 01-8.56 8.48z',
      'M17.47 14.38c-.3-.15-1.76-.87-2.03-.97-.27-.1-.47-.15-.67.15-.2.3-.77.97-.94 1.16-.17.2-.35.22-.64.07-.3-.15-1.26-.46-2.39-1.48-.88-.79-1.48-1.76-1.65-2.06-.17-.3-.02-.46.13-.6.13-.13.3-.35.45-.52.15-.17.2-.3.3-.5.1-.2.05-.37-.03-.52-.08-.15-.67-1.61-.92-2.21-.24-.58-.49-.5-.67-.51h-.57c-.2 0-.52.07-.79.37-.27.3-1.04 1.02-1.04 2.48 0 1.46 1.06 2.88 1.22 3.08.15.2 2.1 3.2 5.08 4.49.71.31 1.26.49 1.69.63.71.23 1.36.2 1.87.12.57-.09 1.76-.72 2-1.41.25-.69.25-1.29.17-1.41-.07-.13-.27-.2-.57-.35z',
    ],
  },
  tiktok: {
    viewBox: '0 0 24 24',
    paths: ['M14 3a5 5 0 005 5v3a8 8 0 01-5-2v7a6 6 0 11-6-6 6 6 0 012 .35V8a5 5 0 00-2-.35 8 8 0 108 8V3z'],
  },
  facebook: {
    viewBox: '0 0 24 24',
    paths: ['M13 3h4a1 1 0 011 1v3h-3a2 2 0 00-2 2v3h5l-1 4h-4v7h-4v-7H6v-4h3V9a5 5 0 015-5z'],
  },
  youtube: {
    viewBox: '0 0 24 24',
    paths: ['M23 7.5a4 4 0 00-2.82-2.83C18.2 4 12 4 12 4s-6.2 0-8.18.67A4 4 0 001 7.5 41.7 41.7 0 000 12a41.7 41.7 0 001 4.5A4 4 0 003.82 19.3C5.8 20 12 20 12 20s6.2 0 8.18-.67A4 4 0 0023 16.5 41.7 41.7 0 0024 12a41.7 41.7 0 00-1-4.5zM10 15.5V8.5l6 3.5-6 3.5z'],
  },
  threads: {
    viewBox: '0 0 24 24',
    paths: ['M12 2C6.5 2 2 6.5 2 12s4.5 10 10 10 10-4.5 10-10S17.5 2 12 2zm5.6 11.2c-.3 3-2.7 5.1-5.9 5.1-2.6 0-4.9-1.6-5.7-3.9l2-.8c.5 1.4 2 2.6 3.7 2.6 2 0 3.7-1.2 3.9-3.1h-3c-.5 0-.9-.4-.9-.9s.4-.9.9-.9h2.9c-.3-1.6-1.7-2.9-3.7-2.9-1.7 0-3.1 1.1-3.6 2.6l-2-.8C6.8 6.5 9.1 5 12 5c3.3 0 6 2.3 6.6 5.6h1.1v1.6h-2.1z'],
  },
  x: {
    viewBox: '0 0 24 24',
    paths: ['M3 3h4.6l4.2 6 4.8-6H21l-7.2 9.1L21 21h-4.6l-4.6-6.6L6.6 21H3l7.4-9.3L3 3z'],
  },
  email: {
    viewBox: '0 0 24 24',
    paths: ['M2 6a2 2 0 012-2h16a2 2 0 012 2v12a2 2 0 01-2 2H4a2 2 0 01-2-2V6zm18 0l-8 5-8-5', 'M2 18l8-5 8 5'],
  },
  bag: {
    viewBox: '0 0 24 24',
    paths: [
      'M6 7V6a6 6 0 1112 0v1h2a2 2 0 012 2v11a2 2 0 01-2 2H4a2 2 0 01-2-2V9a2 2 0 012-2h2zm2 0h8V6a4 4 0 10-8 0v1z',
    ],
  },
}

const ECOM_KEYS = new Set(['tokopedia','shopee','lazada','blibli','bukalapak','tiktokshop','zalora','akulaku','bhinneka'])

const icon = computed(() => {
  const key = (props.name || '').toLowerCase()
  if (ICONS[key]) return ICONS[key]
  if (ECOM_KEYS.has(key)) return ICONS['bag']
  return null
})
</script>
