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
        <BaseButton variant="ghost" size="sm" disabled title="Export CSV (segera)">
          <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v8m0 0l-3-3m3 3l3-3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2h-5l-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z" />
          </svg>
          Export
        </BaseButton>
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
            <span class="font-semibold text-gray-900">Instagram</span>
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
import { ref, computed } from 'vue'
import BaseButton from '@/components/common/BaseButton.vue'

// Range presets (dummy)
const presets = [
  { label: '7 Hari', value: '7d' },
  { label: '30 Hari', value: '30d' },
  { label: 'Semua', value: 'all' },
]
const range = ref('7d')
const labelRange = computed(() => presets.find(p => p.value === range.value)?.label || '')

// Overview dummy data
const overview = {
  views: 3280,
  clicks: 412,
  ctr: (412 / 3280) * 100,
}
const overviewDelta = {
  views: +8.4,
  clicks: +11.2,
  ctr: +2.6,
}

// Series for faux chart (normalized 0..100)
const viewsSeries = [20, 35, 30, 40, 45, 38, 55, 60, 58, 70, 68, 75]
const clicksSeries = [5, 10, 8, 12, 15, 11, 18, 22, 20, 26, 24, 28]

function chartPoints(series) {
  const step = 100 / (series.length - 1)
  const max = Math.max(...series) || 1
  return series.map((v, i) => `${i * step},${100 - (v / max) * 100}`).join(' ')
}

// Top products dummy
const topProducts = [
  { id: 1, name: 'Dress Floral Cantik', clicks: 96, ctr: 42.1 },
  { id: 2, name: 'Earphone Bluetooth TWS', clicks: 81, ctr: 36.8 },
  { id: 3, name: 'Power Bank 20000mAh', clicks: 67, ctr: 28.4 },
  { id: 4, name: 'Kaos Oversized Premium', clicks: 55, ctr: 22.6 },
]

// Recent clicks dummy
const recentClicks = [
  { id: 1, product: 'Dress Floral Cantik', time: '2m lalu', source: 'Instagram', device: 'Mobile' },
  { id: 2, product: 'Power Bank 20000mAh', time: '8m lalu', source: 'WhatsApp', device: 'Mobile' },
  { id: 3, product: 'Smart Watch Fitness', time: '12m lalu', source: 'TikTok', device: 'Web' },
  { id: 4, product: 'Kaos Oversized Premium', time: '25m lalu', source: 'Instagram', device: 'Mobile' },
]

const avgClicksPerDay = computed(() => Math.round(overview.clicks / 7))
const productsWithClicks = computed(() => topProducts.filter(p => p.clicks > 0).length)

function formatNumber(n) {
  return new Intl.NumberFormat('id-ID').format(n)
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
