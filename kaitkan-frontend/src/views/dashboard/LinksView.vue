<template>
  <div class="space-y-6">
    <div>
      <h2 class="font-extrabold text-gray-900 text-2xl">Tautan</h2>
      <p class="text-gray-500">Kelola link di halaman profil publik Anda</p>
    </div>

    <!-- Social Icons (moved from Settings) -->
    <section class="bg-white shadow p-6 rounded-2xl">
      <h3 class="mb-4 font-bold text-gray-900 text-lg">Social Icons</h3>
      <SocialIconsEditor />
      <div class="mt-4">
        <label class="block mb-1 text-sm font-semibold text-gray-700">Posisi Ikon Sosial</label>
        <div class="flex items-center gap-6 text-sm">
          <label class="inline-flex items-center gap-2">
            <input type="radio" value="top" v-model="socialIconsPosition" />
            <span>Top</span>
          </label>
          <label class="inline-flex items-center gap-2">
            <input type="radio" value="bottom" v-model="socialIconsPosition" />
            <span>Bottom</span>
          </label>
          <BaseButton size="sm" class="ml-2" :loading="isSavingPos" :disabled="isSavingPos" @click="saveSocialPosition">Simpan Posisi</BaseButton>
        </div>
      </div>
    </section>

    <!-- Add Link Form -->
    <section class="bg-white shadow p-6 rounded-2xl">
      <EcommerceQuickAdd />
    </section>

    <!-- Add Link Form -->
    <section class="bg-white shadow p-6 rounded-2xl">
      <h3 class="mb-4 font-bold text-gray-900 text-lg">Tambah Tautan</h3>

      <div class="gap-4 grid grid-cols-1 md:grid-cols-4">
        <BaseInput v-model="newLink.title" label="Judul" placeholder="mis. Instagram" />
        <BaseInput v-model="newLink.url" label="URL" placeholder="https://..." />
        <div>
          <label class="block mb-1 text-sm font-semibold text-gray-700">Tipe</label>
          <select v-model="newLink.type" class="bg-white px-3 py-2 border border-gray-200 rounded-lg text-sm w-full">
            <option value="general">General</option>
            <option value="social">Social</option>
            <option value="product">Product</option>
            <option value="shop_collection">Shop Collection</option>
          </select>
        </div>
        <BaseInput v-model="newLink.icon" label="Ikon (opsional)" placeholder="feather:instagram" />
      </div>
      <div class="flex items-center gap-3 mt-4">
        <BaseButton :loading="isSubmitting" :disabled="isSubmitting" @click="handleAddLink">Tambah</BaseButton>
        <span v-if="errorMessage" class="text-sm text-red-600">{{ errorMessage }}</span>
      </div>
    </section>

    <!-- Links List (non-social) -->
    <section class="bg-white shadow p-6 rounded-2xl">
      <div class="flex justify-between items-center mb-4">
        <h3 class="font-bold text-gray-900 text-lg">Daftar Tautan</h3>
        <span class="text-sm text-gray-500">{{ nonSocialLinks.length }} item</span>
      </div>

      <div v-if="nonSocialLinks.length === 0" class="text-sm text-gray-500">Belum ada tautan. Tambahkan dari formulir di atas.</div>

      <ul class="divide-y divide-gray-100">
        <li v-for="(link, idx) in nonSocialLinks" :key="link.id" class="py-4 flex items-start gap-4">
          <div class="flex items-center gap-2">
            <button class="text-gray-400 hover:text-gray-600" :disabled="idx === 0" @click="moveUp(idx)" title="Naikkan urutan">▲</button>
            <button class="text-gray-400 hover:text-gray-600" :disabled="idx === nonSocialLinks.length - 1" @click="moveDown(idx)" title="Turunkan urutan">▼</button>
          </div>

          <div class="flex-1 grid grid-cols-1 md:grid-cols-5 gap-3">
            <BaseInput v-model="link.title" label="Judul" />
            <BaseInput v-model="link.url" label="URL" />
            <div>
              <label class="block mb-1 text-sm font-semibold text-gray-700">Tipe</label>
              <select v-model="link.type" class="bg-white px-3 py-2 border border-gray-200 rounded-lg text-sm w-full">
                <option value="general">General</option>
                <option value="social">Social</option>
                <option value="product">Product</option>
                <option value="shop_collection">Shop Collection</option>
              </select>
            </div>
            <BaseInput v-model="link.icon" label="Ikon" />
            <div>
              <label class="block mb-1 text-sm font-semibold text-gray-700">Aktif</label>
              <input type="checkbox" v-model="link.is_active" class="w-5 h-5" />
            </div>
          </div>

          <div class="flex items-center gap-2">
            <BaseButton variant="secondary" size="sm" :loading="savingId === link.id" @click="saveLink(link)">Simpan</BaseButton>
            <BaseButton variant="danger" size="sm" :disabled="savingId === link.id" @click="removeLink(link.id)">Hapus</BaseButton>
          </div>
        </li>
      </ul>

      <div v-if="nonSocialLinks.length > 1" class="mt-4">
        <BaseButton variant="ghost" size="sm" :loading="isReordering" @click="commitReorder">Simpan Urutan</BaseButton>
      </div>
    </section>
  </div>
  
</template>

<script setup>
import { ref, onMounted, computed } from 'vue'
import BaseInput from '@/components/common/BaseInput.vue'
import BaseButton from '@/components/common/BaseButton.vue'
import { useLinksStore } from '@/stores/links'
import SocialIconsEditor from '@/components/dashboard/SocialIconsEditor.vue'
import { useProfileStore } from '@/stores/profile'
import EcommerceQuickAdd from '@/components/dashboard/EcommerceQuickAdd.vue'

const linksStore = useLinksStore()
const links = linksStore.links
const profileStore = useProfileStore()
const socialIconsPosition = ref('top')
const isSavingPos = ref(false)

const newLink = ref({ title: '', url: '', type: 'general', icon: '', is_active: true })
const isSubmitting = ref(false)
const errorMessage = ref('')
const savingId = ref(null)
const isReordering = ref(false)

const nonSocialLinks = computed(() => links.filter((l) => l.type !== 'social'))

async function handleAddLink() {
  errorMessage.value = ''
  if (!newLink.value.title || !newLink.value.url) {
    errorMessage.value = 'Judul dan URL wajib diisi'
    return
  }
  isSubmitting.value = true
  try {
    await linksStore.addLink({
      title: newLink.value.title,
      url: newLink.value.url,
      type: newLink.value.type,
      icon: newLink.value.icon || null,
      is_active: newLink.value.is_active,
    })
    newLink.value = { title: '', url: '', type: 'general', icon: '', is_active: true }
  } catch (e) {
    errorMessage.value = typeof e === 'string' ? e : 'Gagal menambahkan tautan'
  } finally {
    isSubmitting.value = false
  }
}

async function saveLink(link) {
  savingId.value = link.id
  try {
    await linksStore.updateLink(link.id, {
      title: link.title,
      url: link.url,
      type: link.type,
      icon: link.icon || null,
      is_active: !!link.is_active,
    })
  } catch (e) {
    console.debug('save link failed', e)
  } finally {
    savingId.value = null
  }
}

function moveUp(index) {
  if (index <= 0) return
  const indices = links.map((l, i) => ({ i, l })).filter(x => x.l.type !== 'social').map(x => x.i)
  const a = indices[index - 1]
  const b = indices[index]
  const tmp = links[a]
  links[a] = links[b]
  links[b] = tmp
}

function moveDown(index) {
  const indices = links.map((l, i) => ({ i, l })).filter(x => x.l.type !== 'social').map(x => x.i)
  if (index >= indices.length - 1) return
  const a = indices[index]
  const b = indices[index + 1]
  const tmp = links[a]
  links[a] = links[b]
  links[b] = tmp
}

async function commitReorder() {
  isReordering.value = true
  try {
    const ids = links.filter((l) => l.type !== 'social').map((l) => l.id)
    await linksStore.reorder(ids)
  } catch (e) {
    console.debug('reorder failed', e)
  } finally {
    isReordering.value = false
  }
}

async function saveSocialPosition() {
  try {
    isSavingPos.value = true
    await profileStore.updateProfile({ social_icons_position: socialIconsPosition.value })
  } finally {
    isSavingPos.value = false
  }
}

onMounted(async () => {
  try {
    await linksStore.fetchLinks()
  } catch (e) {
    console.debug('fetch links failed', e)
  }
  try {
    await profileStore.fetchProfile()
    socialIconsPosition.value = profileStore.profile?.social_icons_position || 'top'
  } catch {}
})
</script>
