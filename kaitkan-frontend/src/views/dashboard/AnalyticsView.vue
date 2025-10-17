<template>
  <div class="space-y-6">
    <!-- Header + Filters -->
    <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4">
      <div>
        <h2 class="text-2xl font-extrabold text-gray-900">Analitik</h2>
        <p class="text-gray-500">Pantau performa katalog Anda secara ringkas</p>
      </div>
      <div class="flex items-center gap-2">
        <button
          v-for="p in presets"
          :key="p.value"
          :class="[
            'px-4 py-2 rounded-xl text-sm font-semibold transition-all',
            range === p.value
              ? 'bg-primary-600 text-white shadow'
              : 'bg-white text-gray-700 border border-gray-200 hover:bg-gray-50'
          ]"
          @click="range = p.value"
        >
          {{ p.label }}
        </button>
        <BaseButton variant="ghost" size="sm" @click="exportSummary">Export Summary</BaseButton>
        <BaseButton variant="ghost" size="sm" @click="exportTopLinks">Export Top Links</BaseButton>
        <BaseButton variant="ghost" size="sm" @click="exportTopProducts">Export Top Products</BaseButton>
      </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
      <KpiCard
        icon="views"
        title="Tayangan"
        :value="formatNumber(overview.views)"
        :delta="overviewDelta.views"
      />
      <KpiCard
        icon="clicks"
        title="Klik WhatsApp"
        :value="formatNumber(overview.clicks)"
        :delta="overviewDelta.clicks"
      />
      <KpiCard
        icon="ctr"
        title="CTR"
        :value="overview.ctr.toFixed(1) + '%'"
        :delta="overviewDelta.ctr"
      />
      <KpiCard
        icon="top"
        title="Produk Teratas"
        :value="topProducts[0]?.name || '-'"
        :delta="null"
      />
    </div>

    <!-- Chart + Breakdown -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
      <!-- Chart Placeholder -->
      <div class="lg:col-span-2 bg-white rounded-2xl shadow p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-bold text-gray-900">Tren Klik & Tayangan</h3>
          <span class="text-sm text-gray-500">{{ labelRange }}</span>
        </div>
        <div class="h-64 relative rounded-xl bg-gradient-to-b from-gray-50 to-white border border-gray-100 overflow-hidden">
          <!-- Simple faux chart grid -->
          <div class="absolute inset-0">
            <div v-for="i in 6" :key="i" class="border-t border-gray-100 h-1/6"></div>
          </div>
          <!-- Trend lines (decorative) -->
          <svg class="absolute inset-0 w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
            <polyline fill="none" stroke="rgba(59,130,246,0.35)" stroke-width="2"
              :points="chartPoints(viewsSeries)" />
            <polyline fill="none" stroke="rgba(16,185,129,0.6)" stroke-width="2"
              :points="chartPoints(clicksSeries)" />
          </svg>
          <div class="absolute bottom-3 right-3 text-xs text-gray-500">Ilustrasi chart (dummy)</div>
        </div>
      </div>

      <!-- Breakdown -->
      <div class="bg-white rounded-2xl shadow p-6">
        <h3 class="text-lg font-bold text-gray-900 mb-4">Ringkasan</h3>
        <ul class="space-y-3">
          <li class="flex items-center justify-between">
            <span class="text-gray-600">Rata-rata klik / hari</span>
            <span class="font-semibold text-gray-900">{{ avgClicksPerDay }}</span>
          </li>
          <li class="flex items-center justify-between">
            <span class="text-gray-600">Produk dengan klik</span>
            <span class="font-semibold text-gray-900">{{ productsWithClicks }}/{{ topProducts.length }}</span>
          </li>
          <li class="flex items-center justify-between">
            <span class="text-gray-600">Sumber terbanyak</span>
            <span class="font-semibold text-gray-900">{{ (topSources[0]?.name || '-') }} ({{ topSources[0]?.count || 0 }})</span>
          </li>
          <li class="flex items-center justify-between">
            <span class="text-gray-600">Perangkat</span>
            <span class="font-semibold text-gray-900">Mobile {{ devices.mobile || 0 }} · Desktop {{ devices.desktop || 0 }}</span>
          </li>
        </ul>
        <div class="mt-5 p-3 rounded-xl bg-primary-50 border border-primary-100 text-sm text-primary-900">
          Insight: produk dengan gambar kontras mendapat CTR lebih tinggi.
        </div>
      </div>
    </div>

    <!-- Top products + Recent activity -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
      <!-- Top Products -->
      <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-bold text-gray-900">Produk Teratas</h3>
          <span class="text-sm text-gray-500">Klik & CTR</span>
        </div>
        <div class="divide-y divide-gray-100">
          <div v-for="(p, idx) in topProducts" :key="p.id" class="py-3 flex items-center gap-4">
            <div class="w-8 h-8 rounded-lg bg-gray-100 flex items-center justify-center text-xs font-bold text-gray-600">{{ idx+1 }}</div>
            <div class="min-w-0 flex-1">
              <div class="flex items-center justify-between gap-2">
                <p class="font-semibold text-gray-900 truncate">{{ p.name }}</p>
                <p class="text-sm text-gray-500">{{ p.clicks }} klik</p>
              </div>
              <div class="mt-2 h-2 bg-gray-100 rounded-full overflow-hidden">
                <div class="h-full bg-gradient-to-r from-green-500 to-emerald-400" :style="{ width: p.ctr + '%' }"></div>
              </div>
              <div class="mt-1 text-xs text-gray-500">CTR {{ p.ctr.toFixed(1) }}%</div>
            </div>
          </div>
        </div>
      </div>

      <!-- Recent Clicks -->
      <div class="bg-white rounded-2xl shadow p-6">
        <div class="flex items-center justify-between mb-4">
          <h3 class="text-lg font-bold text-gray-900">Aktivitas Terbaru</h3>
          <span class="text-sm text-gray-500">10 terakhir</span>
        </div>
        <div class="space-y-3">
          <div v-for="item in recentClicks" :key="item.id" class="flex items-start gap-3">
            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-500 to-secondary-500 text-white flex items-center justify-center font-bold">
              {{ item.product.charAt(0).toUpperCase() }}
            </div>
            <div class="min-w-0 flex-1">
              <p class="text-sm text-gray-900"><span class="font-semibold">{{ item.product }}</span> diklik</p>
              <p class="text-xs text-gray-500">{{ item.time }} • Sumber: {{ item.source }}</p>
            </div>
            <span class="text-xs px-2 py-1 rounded-lg bg-gray-100 text-gray-600">{{ item.device }}</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Coming soon notice -->
    <div class="rounded-2xl border border-dashed border-gray-300 bg-white p-6 text-center text-gray-600">
      Fitur analitik lanjutan (filter sumber, export, multi-range) segera hadir setelah Anda setujui UI ini.
    </div>
  </div>
  
</template>

<script setup>
import { ref, computed, watch, onMounted } from 'vue'
import BaseButton from '@/components/common/BaseButton.vue'
import { analyticsService } from '@/services/analytics.service'

// Range presets
const presets = [
  { label: '7 Hari', value: '7d' },
  { label: '30 Hari', value: '30d' },
]
const range = ref('7d')
const labelRange = computed(() => presets.find(p => p.value === range.value)?.label || '')

// Loading / error
const isLoading = ref(false)
const errorMessage = ref('')

// Overview
const overview = ref({ views: 0, clicks: 0, ctr: 0 })
const overviewDelta = ref({ views: null, clicks: null, ctr: null }) // reserved for future

// Series (raw from API)
const labels = ref([])
const viewsSeries = ref([])
const clicksSeries = ref([])

// Top lists
const topProducts = ref([])
const topLinks = ref([])

function chartPoints(series) {
  const s = Array.isArray(series) ? series : []
  const step = s.length > 1 ? 100 / (s.length - 1) : 100
  const max = Math.max(...s, 1)
  return s.map((v, i) => `${i * step},${100 - (v / max) * 100}`).join(' ')
}

async function loadAnalytics() {
  isLoading.value = true
  errorMessage.value = ''
  try {
    const [summary, links, products] = await Promise.all([
      analyticsService.getSummary(range.value),
      analyticsService.getTopLinks(range.value),
      analyticsService.getTopProducts(range.value),
    ])

    if (summary?.success) {
      overview.value = summary.data?.totals || { views: 0, clicks: 0, ctr: 0 }
      labels.value = summary.data?.series?.labels || []
      viewsSeries.value = summary.data?.series?.views || []
      clicksSeries.value = summary.data?.series?.clicks || []
      topSources.value = summary.data?.breakdown?.sources || []
      devices.value = summary.data?.breakdown?.devices || { mobile: 0, desktop: 0 }
    }
    if (links?.success) topLinks.value = links.data || []
    if (products?.success) topProducts.value = (products.data || []).map(p => ({ id: p.product_id, name: `#${p.product_id}`, clicks: p.clicks, ctr: null }))
  } catch (e) {
    errorMessage.value = typeof e === 'string' ? e : 'Gagal memuat analitik'
  } finally {
    isLoading.value = false
  }
}

watch(range, () => loadAnalytics())
onMounted(() => loadAnalytics())

const avgClicksPerDay = computed(() => {
  const days = range.value === '30d' ? 30 : 7
  return Math.round((overview.value.clicks || 0) / days)
})
const productsWithClicks = computed(() => (topProducts.value || []).filter(p => p.clicks > 0).length)

function formatNumber(n) { return new Intl.NumberFormat('id-ID').format(n || 0) }

// Breakdown reactive stores
const topSources = ref([])
const devices = ref({ mobile: 0, desktop: 0 })

// Export helpers
function downloadBlob(filename, blob) {
  const url = window.URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = filename
  document.body.appendChild(a)
  a.click()
  a.remove()
  window.URL.revokeObjectURL(url)
}

async function exportSummary() {
  const blob = await analyticsService.exportSummary(range.value)
  downloadBlob(`analytics_summary_${range.value}.csv`, blob)
}
async function exportTopLinks() {
  const blob = await analyticsService.exportTopLinks(range.value)
  downloadBlob(`analytics_top_links_${range.value}.csv`, blob)
}
async function exportTopProducts() {
  const blob = await analyticsService.exportTopProducts(range.value)
  downloadBlob(`analytics_top_products_${range.value}.csv`, blob)
}
</script>

<script>
// Local KPI card sub-component for compactness
export default {
  components: {
    KpiCard: {
      props: { title: String, value: [String, Number], delta: Number, icon: String },
      template: `
        <div class="bg-white rounded-2xl shadow p-5">
          <div class="flex items-center justify-between">
            <div>
              <p class="text-sm text-gray-500">{{ title }}</p>
              <p class="mt-1 text-2xl font-extrabold text-gray-900">{{ value }}</p>
            </div>
            <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center">
              <svg v-if="icon==='views'" class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2 12s4-7 10-7 10 7 10 7-4 7-10 7S2 12 2 12zm10 3a3 3 0 100-6 3 3 0 000 6z" />
              </svg>
              <svg v-else-if="icon==='clicks'" class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8M3 3h6v6H3V3z" />
              </svg>
              <svg v-else-if="icon==='ctr'" class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 3v10l-3-3m-4 9h16" />
              </svg>
              <svg v-else class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h8m-8 6h16" />
              </svg>
            </div>
          </div>
          <div v-if="delta!==null" class="mt-3 text-sm">
            <span :class="[delta>=0 ? 'text-emerald-600' : 'text-red-600', 'font-semibold']">
              {{ delta>=0 ? '+'+delta : delta }}%
            </span>
            <span class="text-gray-500">vs periode sebelumnya</span>
          </div>
        </div>
      `,
    },
  },
}
</script>

<style scoped>
.h-1\/6 { height: 16.6666%; }
</style>
