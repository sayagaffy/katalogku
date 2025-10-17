import { defineStore } from 'pinia'
import { ref } from 'vue'
import { linkService } from '@/services/link.service'

export const useLinksStore = defineStore('links', () => {
  const links = ref([])
  const isLoading = ref(false)
  const error = ref(null)

  async function fetchLinks() {
    isLoading.value = true
    error.value = null
    try {
      const res = await linkService.list()
      if (res?.success) links.value = res.data || []
      return res
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function addLink(payload) {
    isLoading.value = true
    error.value = null
    try {
      const res = await linkService.create(payload)
      if (res?.success && res.data) links.value.push(res.data)
      return res
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function updateLink(id, payload) {
    isLoading.value = true
    error.value = null
    try {
      const res = await linkService.update(id, payload)
      if (res?.success && res.data) {
        const idx = links.value.findIndex((l) => l.id === id)
        if (idx !== -1) links.value[idx] = res.data
      }
      return res
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function deleteLink(id) {
    isLoading.value = true
    error.value = null
    try {
      const res = await linkService.remove(id)
      if (res?.success) {
        links.value = links.value.filter((l) => l.id !== id)
      }
      return res
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  async function reorder(newOrderIds) {
    isLoading.value = true
    error.value = null
    try {
      const res = await linkService.reorder(newOrderIds)
      if (res?.success) {
        // Re-sort locally by order in newOrderIds
        const map = new Map()
        links.value.forEach((l) => map.set(l.id, l))
        links.value = newOrderIds.map((id) => map.get(id)).filter(Boolean)
      }
      return res
    } catch (err) {
      error.value = err
      throw err
    } finally {
      isLoading.value = false
    }
  }

  return {
    links,
    isLoading,
    error,
    fetchLinks,
    addLink,
    updateLink,
    deleteLink,
    reorder,
  }
})

