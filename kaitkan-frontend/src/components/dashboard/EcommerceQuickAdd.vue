<template>
  <div>
    <h3 class="mb-4 font-bold text-gray-900 text-lg">E-commerce Indonesia</h3>
    <p class="text-sm text-gray-600 mb-3">Tambahkan tautan toko Anda di marketplace populer. Nonaktifkan untuk menyembunyikan.</p>

    <div class="space-y-3">
      <div v-for="p in platforms" :key="p.key" class="flex items-center gap-3 p-3 bg-white border border-gray-200 rounded-xl">
        <SocialIcon :name="p.key" :size="20" class="text-gray-700" />
        <div class="flex-1">
          <label class="block text-xs font-semibold text-gray-600">{{ p.label }}</label>
          <input v-model="p.value" type="text" class="w-full px-3 py-2 border border-gray-200 rounded-lg text-sm" :placeholder="p.placeholder" />
        </div>
        <label class="inline-flex items-center gap-2 text-sm">
          <input type="checkbox" v-model="p.active" />
          <span>Aktif</span>
        </label>
      </div>
    </div>

    <div class="mt-4">
      <BaseButton :loading="saving" :disabled="saving" @click="save">Simpan E-commerce</BaseButton>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue'
import BaseButton from '@/components/common/BaseButton.vue'
import SocialIcon from '@/components/common/SocialIcon.vue'
import { useLinksStore } from '@/stores/links'

const linksStore = useLinksStore()
const saving = ref(false)

const platforms = ref([
  { key: 'tokopedia', label: 'Tokopedia', placeholder: 'username atau URL', active: false, value: '' },
  { key: 'shopee', label: 'Shopee', placeholder: 'username atau URL', active: false, value: '' },
  { key: 'lazada', label: 'Lazada', placeholder: 'username atau URL', active: false, value: '' },
  { key: 'blibli', label: 'Blibli', placeholder: 'username atau URL', active: false, value: '' },
  { key: 'bukalapak', label: 'Bukalapak', placeholder: 'username atau URL', active: false, value: '' },
  { key: 'tiktokshop', label: 'TikTok Shop', placeholder: '@username atau URL', active: false, value: '' },
  { key: 'zalora', label: 'Zalora', placeholder: 'username atau URL', active: false, value: '' },
  { key: 'akulaku', label: 'Akulaku', placeholder: 'username atau URL', active: false, value: '' },
  { key: 'bhinneka', label: 'Bhinneka', placeholder: 'username atau URL', active: false, value: '' },
])

function buildUrl(key, value) {
  if (!value) return ''
  const v = value.trim()
  if (/^https?:\/\//i.test(v)) return v
  const handle = v.replace(/^@/, '')
  switch (key) {
    case 'tokopedia': return `https://www.tokopedia.com/${handle}`
    case 'shopee': return `https://shopee.co.id/${handle}`
    case 'lazada': return `https://www.lazada.co.id/shop/${handle}`
    case 'blibli': return `https://www.blibli.com/store/${handle}`
    case 'bukalapak': return `https://www.bukalapak.com/u/${handle}`
    case 'tiktokshop': return `https://www.tiktok.com/@${handle}`
    case 'zalora': return `https://www.zalora.co.id/store/${handle}`
    case 'akulaku': return `https://www.akulaku.com/store/${handle}`
    case 'bhinneka': return `https://www.bhinneka.com/store/${handle}`
    default: return v
  }
}

async function save() {
  saving.value = true
  try {
    if (!Array.isArray(linksStore.links) || linksStore.links.length === 0) {
      try { await linksStore.fetchLinks() } catch {}
    }
    const existing = new Map()
    for (const l of linksStore.links) {
      if ((l.type === 'shop_collection' || l.type === 'general') && l.icon) existing.set(l.icon, l)
    }

    for (const p of platforms.value) {
      const url = buildUrl(p.key, p.value)
      const found = existing.get(p.key)
      if (p.active && url) {
        const payload = { title: p.label, url, type: 'shop_collection', icon: p.key, is_active: true }
        if (found) await linksStore.updateLink(found.id, payload)
        else await linksStore.addLink(payload)
      } else if (found) {
        await linksStore.updateLink(found.id, { is_active: false })
      }
    }
    alert('E-commerce tersimpan')
  } catch (e) {
    alert(typeof e === 'string' ? e : (e?.message || 'Gagal menyimpan data e-commerce'))
  } finally {
    saving.value = false
  }
}

onMounted(async () => {
  try { await linksStore.fetchLinks() } catch {}
  const map = new Map()
  for (const l of linksStore.links) {
    if ((l.type === 'shop_collection' || l.type === 'general') && l.icon) map.set(l.icon, l)
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

