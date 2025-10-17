<template>
  <div>
    <h3 class="mb-4 font-bold text-gray-900 text-lg">Social Icons</h3>

    <div class="mb-3 text-sm text-gray-600">Tambah username/URL akun sosial. Nonaktifkan untuk menyembunyikan.</div>

    <div class="space-y-3">
      <div v-for="p in platforms" :key="p.key" class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-xl">
        <SocialIcon :name="p.key" :size="20" class="text-gray-700" />
        <div class="flex-1">
          <label class="block text-xs font-semibold text-gray-600">{{ p.label }}</label>
          <input
            v-model="p.value"
            type="text"
            class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm"
            :placeholder="p.placeholder"
          />
        </div>
        <label class="inline-flex items-center gap-2 text-sm">
          <input type="checkbox" v-model="p.active" />
          <span>Aktif</span>
        </label>
      </div>
    </div>

    <div class="mt-4">
      <BaseButton :loading="saving" :disabled="saving" @click="save">Simpan Social Icons</BaseButton>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, onMounted } from 'vue'
import BaseButton from '@/components/common/BaseButton.vue'
import SocialIcon from '@/components/common/SocialIcon.vue'
import { useLinksStore } from '@/stores/links'

const linksStore = useLinksStore()
const saving = ref(false)

// Supported platforms (can be extended)
const platforms = ref([
  { key: 'instagram', label: 'Instagram', placeholder: '@username', active: false, value: '' },
  { key: 'whatsapp', label: 'WhatsApp', placeholder: '08xxxxxxxxxx', active: false, value: '' },
  { key: 'tiktok', label: 'TikTok', placeholder: '@username', active: false, value: '' },
  { key: 'facebook', label: 'Facebook', placeholder: 'username atau URL', active: false, value: '' },
  { key: 'youtube', label: 'YouTube', placeholder: '@channel atau URL', active: false, value: '' },
  { key: 'threads', label: 'Threads', placeholder: '@username', active: false, value: '' },
])

function buildUrl(key, value) {
  if (!value) return ''
  const v = value.trim()
  if (/^https?:\/\//i.test(v)) return v
  const handle = v.replace(/^@/, '')
  switch (key) {
    case 'instagram': return `https://instagram.com/${handle}`
    case 'whatsapp': {
      // normalize 08xxxx → 62xxxx for wa.me
      const digits = handle.replace(/\D/g, '')
      const phone = digits.startsWith('0') ? `62${digits.slice(1)}` : (digits.startsWith('62') ? digits : `62${digits}`)
      return `https://wa.me/${phone}`
    }
    case 'tiktok': return `https://tiktok.com/@${handle}`
    case 'facebook': return `https://facebook.com/${handle}`
    case 'youtube': return `https://youtube.com/@${handle}`
    case 'threads': return `https://www.threads.net/@${handle}`
    default: return v
  }
}

async function save() {
  saving.value = true
  try {
    // Ensure existing links loaded
    if (!Array.isArray(linksStore.links) || linksStore.links.length === 0) {
      try { await linksStore.fetchLinks() } catch {}
    }

    const existing = new Map()
    for (const l of linksStore.links) {
      if (l.type === 'social' && l.icon) existing.set(l.icon, l)
    }

    for (const p of platforms.value) {
      const url = buildUrl(p.key, p.value)
      const found = existing.get(p.key)
      if (p.active && url) {
        if (found) {
          await linksStore.updateLink(found.id, { title: p.label, url, type: 'social', icon: p.key, is_active: true })
        } else {
          await linksStore.addLink({ title: p.label, url, type: 'social', icon: p.key, is_active: true })
        }
      } else if (found) {
        // Deactivate instead of delete (preserve value)
        await linksStore.updateLink(found.id, { is_active: false })
      }
    }
    alert('Social icons tersimpan')
  } catch (e) {
    console.debug('save social icons failed', e)
    alert(typeof e === 'string' ? e : (e?.message || 'Gagal menyimpan social icons'))
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  try { await linksStore.fetchLinks() } catch {}
  const map = new Map()
  for (const l of linksStore.links) {
    if (l.type === 'social' && l.icon) map.set(l.icon, l)
  }
  for (const p of platforms.value) {
    const found = map.get(p.key)
    if (found) {
      p.active = !!found.is_active
      p.value = found.url || ''
    }
  }
})
</script>

