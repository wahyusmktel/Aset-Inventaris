<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import M3Button from '@/Components/M3Button.vue';
import { Head, Link } from '@inertiajs/vue3';
import { ref, onMounted, nextTick } from 'vue';
import {
  Chart,
  ArcElement,
  BarElement,
  BarController,
  DoughnutController,
  PieController,
  CategoryScale,
  LinearScale,
  RadialLinearScale,
  Tooltip,
  Legend,
  Title,
} from 'chart.js';

// Register Chart.js components
Chart.register(
  ArcElement,
  BarElement,
  BarController,
  DoughnutController,
  PieController,
  CategoryScale,
  LinearScale,
  RadialLinearScale,
  Tooltip,
  Legend,
  Title
);

const props = defineProps({
  activeSchool: {
    type: Object,
    default: null,
  },
  metrics: {
    type: Object,
    default: () => ({
      total_items: 0,
      total_quantity: 0,
      good_condition: 0,
      damaged_condition: 0,
      good_percent: 100,
      total_categories: 0,
      total_buildings: 0,
      total_rooms: 0,
      total_functions: 0,
      total_users: 1,
    }),
  },
  chartData: {
    type: Object,
    default: () => ({
      categories: [],
      rooms: [],
      functions: [],
      condition: { baik: 0, rusak: 0 },
    }),
  },
  recentItems: {
    type: Array,
    default: () => [],
  },
});

// Canvas Refs for Charts
const categoryChartRef = ref(null);
const conditionChartRef = ref(null);
const roomChartRef = ref(null);
const functionChartRef = ref(null);

let categoryChart = null;
let conditionChart = null;
let roomChart = null;
let functionChart = null;

const initCharts = () => {
  // 1. Chart Kategori Barang (Horizontal Bar Chart)
  if (categoryChartRef.value) {
    if (categoryChart) categoryChart.destroy();
    const catLabels = props.chartData.categories.map((c) => c.name);
    const catCounts = props.chartData.categories.map((c) => c.count);

    categoryChart = new Chart(categoryChartRef.value, {
      type: 'bar',
      data: {
        labels: catLabels.length > 0 ? catLabels : ['Belum ada data'],
        datasets: [
          {
            label: 'Jumlah Jenis Barang',
            data: catCounts.length > 0 ? catCounts : [0],
            backgroundColor: [
              '#2563EB', // Blue
              '#0284C7', // Sky
              '#0D9488', // Teal
              '#16A34A', // Green
              '#D97706', // Amber
              '#7C3AED', // Violet
              '#DB2777', // Pink
            ],
            borderRadius: 6,
            barThickness: 16,
          },
        ],
      },
      options: {
        indexAxis: 'y',
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            padding: 10,
            cornerRadius: 8,
            bodyFont: { family: 'system-ui', size: 12 },
          },
        },
        scales: {
          x: {
            grid: { color: 'rgba(0, 0, 0, 0.04)' },
            ticks: { precision: 0, font: { size: 11 } },
          },
          y: {
            grid: { display: false },
            ticks: { font: { size: 11, weight: '500' } },
          },
        },
      },
    });
  }

  // 2. Chart Kondisi Barang (Doughnut Chart)
  if (conditionChartRef.value) {
    if (conditionChart) conditionChart.destroy();
    const baik = props.chartData.condition.baik || 0;
    const rusak = props.chartData.condition.rusak || 0;

    conditionChart = new Chart(conditionChartRef.value, {
      type: 'doughnut',
      data: {
        labels: ['Kondisi Baik', 'Kondisi Rusak'],
        datasets: [
          {
            data: baik === 0 && rusak === 0 ? [1, 0] : [baik, rusak],
            backgroundColor: ['#10B981', '#EF4444'], // Emerald vs Red
            borderWidth: 3,
            borderColor: '#FFFFFF',
            hoverOffset: 6,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '72%',
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              usePointStyle: true,
              pointStyle: 'circle',
              padding: 16,
              font: { size: 12, weight: '600' },
            },
          },
          tooltip: {
            padding: 10,
            cornerRadius: 8,
            bodyFont: { size: 12 },
          },
        },
      },
    });
  }

  // 3. Chart Distribusi Ruangan / Lab (Bar Chart)
  if (roomChartRef.value) {
    if (roomChart) roomChart.destroy();
    const roomLabels = props.chartData.rooms.map((r) => r.name);
    const roomCounts = props.chartData.rooms.map((r) => r.count);

    roomChart = new Chart(roomChartRef.value, {
      type: 'bar',
      data: {
        labels: roomLabels.length > 0 ? roomLabels : ['Belum ada data'],
        datasets: [
          {
            label: 'Barang di Ruangan',
            data: roomCounts.length > 0 ? roomCounts : [0],
            backgroundColor: '#4F46E5', // Indigo
            hoverBackgroundColor: '#4338CA',
            borderRadius: 6,
            barThickness: 24,
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: { display: false },
          tooltip: {
            padding: 10,
            cornerRadius: 8,
          },
        },
        scales: {
          x: {
            grid: { display: false },
            ticks: {
              font: { size: 10, weight: '500' },
              maxRotation: 45,
              minRotation: 20,
            },
          },
          y: {
            grid: { color: 'rgba(0, 0, 0, 0.04)' },
            ticks: { precision: 0, font: { size: 11 } },
          },
        },
      },
    });
  }

  // 4. Chart Fungsi Barang (Pie Chart)
  if (functionChartRef.value) {
    if (functionChart) functionChart.destroy();
    const fnLabels = props.chartData.functions.map((f) => f.name);
    const fnCounts = props.chartData.functions.map((f) => f.count);

    functionChart = new Chart(functionChartRef.value, {
      type: 'pie',
      data: {
        labels: fnLabels.length > 0 ? fnLabels : ['Belum ada data'],
        datasets: [
          {
            data: fnCounts.length > 0 ? fnCounts : [1],
            backgroundColor: [
              '#3B82F6', // Blue
              '#8B5CF6', // Purple
              '#EC4899', // Pink
              '#F59E0B', // Amber
              '#10B981', // Emerald
              '#06B6D4', // Cyan
            ],
            borderWidth: 2,
            borderColor: '#FFFFFF',
          },
        ],
      },
      options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
          legend: {
            position: 'bottom',
            labels: {
              usePointStyle: true,
              pointStyle: 'circle',
              padding: 12,
              font: { size: 11 },
            },
          },
        },
      },
    });
  }
};

onMounted(async () => {
  await nextTick();
  initCharts();
});

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
  });
};
</script>

<template>
  <Head title="Beranda - Sistem Aset & Inventaris Sekolah" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      
      <!-- 1. School Identity & Welcome Banner -->
      <div class="bg-gradient-to-r from-primary via-primary/95 to-slate-900 rounded-m3-xl p-6 sm:p-8 text-white relative overflow-hidden shadow-m3-elevation-2">
        <!-- Background decoration circles -->
        <div class="absolute -right-10 -bottom-10 w-72 h-72 rounded-full bg-white/5 pointer-events-none blur-2xl"></div>
        <div class="absolute right-32 -top-12 w-48 h-48 rounded-full bg-secondary/15 pointer-events-none blur-xl"></div>

        <div class="relative z-10 flex flex-col lg:flex-row lg:items-center justify-between gap-6">
          <div class="space-y-2.5 max-w-2xl">
            <!-- Active School Badge -->
            <div class="inline-flex items-center gap-2 px-3 py-1 rounded-m3-full bg-white/15 backdrop-blur-md text-xs font-semibold">
              <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
              <span>Lembaga Aktif: {{ activeSchool ? activeSchool.name : 'SMK Telkom Lampung' }}</span>
            </div>

            <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight">
              Dashboard Aset & Inventaris Sekolah
            </h1>

            <p class="text-xs sm:text-sm text-white/80 leading-relaxed">
              Selamat datang kembali, <strong>{{ $page.props.auth.user.name }}</strong>. 
              Sistem telah mencatat <strong>{{ metrics.total_items }} jenis barang</strong> dengan total fisik <strong>{{ metrics.total_quantity }} unit</strong> 
              yang tersebar di <strong>{{ metrics.total_rooms }} ruangan/lab</strong> kampus.
            </p>

            <!-- School Meta Tags -->
            <div v-if="activeSchool" class="pt-1 flex flex-wrap items-center gap-4 text-xs text-white/90">
              <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px] text-amber-300">account_circle</span>
                <span>Kepsek: {{ activeSchool.principal_name }}</span>
              </div>
              <div class="flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[16px] text-emerald-300">location_on</span>
                <span class="truncate max-w-xs">{{ activeSchool.address }}</span>
              </div>
            </div>
          </div>

          <!-- Quick Action Buttons -->
          <div class="flex flex-wrap items-center gap-3 shrink-0">
            <a
              :href="route('inventory.items.export')"
              class="h-11 px-4 rounded-m3-full bg-white/10 hover:bg-white/20 backdrop-blur-md border border-white/20 text-white font-semibold text-xs inline-flex items-center gap-2 transition-all cursor-pointer"
            >
              <span class="material-symbols-outlined text-[18px]">table_view</span>
              <span>Unduh Excel Aset</span>
            </a>

            <Link
              :href="route('inventory.items.index')"
              class="h-11 px-5 rounded-m3-full bg-white text-primary font-bold text-xs inline-flex items-center gap-2 shadow-md hover:bg-surface-container transition-all cursor-pointer"
            >
              <span class="material-symbols-outlined text-[20px]">add_box</span>
              <span>Kelola Inventaris</span>
            </Link>
          </div>
        </div>
      </div>

      <!-- 2. KPI Metrics Stats Grid (6 Cards) -->
      <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-6 gap-3 sm:gap-4">
        <!-- 1. Total Jenis Barang -->
        <div class="bg-surface-container-lowest p-4 rounded-m3-lg border border-outline-variant/40 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-semibold text-surface-on-variant">Jenis Barang</span>
            <div class="w-7 h-7 rounded-m3-xs bg-primary-container text-primary flex items-center justify-center">
              <span class="material-symbols-outlined text-[16px]">inventory_2</span>
            </div>
          </div>
          <div class="mt-2">
            <div class="text-xl sm:text-2xl font-black text-surface-foreground">{{ metrics.total_items }}</div>
            <div class="text-[10px] text-surface-on-variant mt-0.5">Item Terdaftar</div>
          </div>
        </div>

        <!-- 2. Total Kuantitas Fisik -->
        <div class="bg-surface-container-lowest p-4 rounded-m3-lg border border-outline-variant/40 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-semibold text-surface-on-variant">Total Fisik</span>
            <div class="w-7 h-7 rounded-m3-xs bg-secondary-container text-secondary flex items-center justify-center">
              <span class="material-symbols-outlined text-[16px]">widgets</span>
            </div>
          </div>
          <div class="mt-2">
            <div class="text-xl sm:text-2xl font-black text-surface-foreground">{{ metrics.total_quantity }}</div>
            <div class="text-[10px] text-surface-on-variant mt-0.5">Unit Aset</div>
          </div>
        </div>

        <!-- 3. Kondisi Baik (Kelaikan) -->
        <div class="bg-surface-container-lowest p-4 rounded-m3-lg border border-outline-variant/40 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-semibold text-surface-on-variant">Kondisi Baik</span>
            <div class="w-7 h-7 rounded-m3-xs bg-emerald-100 text-emerald-700 flex items-center justify-center">
              <span class="material-symbols-outlined text-[16px]">check_circle</span>
            </div>
          </div>
          <div class="mt-2">
            <div class="text-xl sm:text-2xl font-black text-emerald-700">{{ metrics.good_condition }}</div>
            <div class="text-[10px] text-emerald-800 font-bold mt-0.5">{{ metrics.good_percent }}% Laik Pakai</div>
          </div>
        </div>

        <!-- 4. Kondisi Rusak -->
        <div class="bg-surface-container-lowest p-4 rounded-m3-lg border border-outline-variant/40 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-semibold text-surface-on-variant">Kondisi Rusak</span>
            <div class="w-7 h-7 rounded-m3-xs bg-red-100 text-error flex items-center justify-center">
              <span class="material-symbols-outlined text-[16px]">warning</span>
            </div>
          </div>
          <div class="mt-2">
            <div class="text-xl sm:text-2xl font-black text-error">{{ metrics.damaged_condition }}</div>
            <div class="text-[10px] text-error font-semibold mt-0.5">Perlu Tindakan</div>
          </div>
        </div>

        <!-- 5. Kategori Barang -->
        <div class="bg-surface-container-lowest p-4 rounded-m3-lg border border-outline-variant/40 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-semibold text-surface-on-variant">Kategori</span>
            <div class="w-7 h-7 rounded-m3-xs bg-amber-100 text-amber-800 flex items-center justify-center">
              <span class="material-symbols-outlined text-[16px]">category</span>
            </div>
          </div>
          <div class="mt-2">
            <div class="text-xl sm:text-2xl font-black text-surface-foreground">{{ metrics.total_categories }}</div>
            <div class="text-[10px] text-surface-on-variant mt-0.5">Kelompok Aset</div>
          </div>
        </div>

        <!-- 6. Ruangan & Lab -->
        <div class="bg-surface-container-lowest p-4 rounded-m3-lg border border-outline-variant/40 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between">
            <span class="text-[11px] font-semibold text-surface-on-variant">Ruangan & Lab</span>
            <div class="w-7 h-7 rounded-m3-xs bg-purple-100 text-purple-800 flex items-center justify-center">
              <span class="material-symbols-outlined text-[16px]">meeting_room</span>
            </div>
          </div>
          <div class="mt-2">
            <div class="text-xl sm:text-2xl font-black text-surface-foreground">{{ metrics.total_rooms }}</div>
            <div class="text-[10px] text-surface-on-variant mt-0.5">Titik Lokasi</div>
          </div>
        </div>
      </div>

      <!-- 3. Interactive Charts Grid (2x2) -->
      <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        
        <!-- Chart 1: Distribusi Kategori Barang Terbanyak -->
        <div class="bg-surface-container-lowest rounded-m3-xl p-5 sm:p-6 border border-outline-variant/40 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
            <div>
              <h3 class="text-sm font-bold text-surface-foreground flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-primary">bar_chart</span>
                <span>Distribusi Kategori Aset Terbanyak</span>
              </h3>
              <p class="text-[11px] text-surface-on-variant mt-0.5">Pengelompokan barang berdasarkan kategori utama sekolah</p>
            </div>
            <Link :href="route('master-data.item-categories.index')" class="text-xs font-bold text-primary hover:underline">
              Lihat Semua
            </Link>
          </div>

          <div class="h-64 mt-4 relative flex items-center justify-center">
            <canvas ref="categoryChartRef"></canvas>
          </div>
        </div>

        <!-- Chart 2: Status Kondisi & Kelaikan Fisik Barang -->
        <div class="bg-surface-container-lowest rounded-m3-xl p-5 sm:p-6 border border-outline-variant/40 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
            <div>
              <h3 class="text-sm font-bold text-surface-foreground flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-emerald-600">donut_large</span>
                <span>Tingkat Kelaikan & Kondisi Aset</span>
              </h3>
              <p class="text-[11px] text-surface-on-variant mt-0.5">Perbandingan barang kondisi Baik vs Rusak</p>
            </div>
            <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[11px] font-bold">
              {{ metrics.good_percent }}% Siap Pakai
            </span>
          </div>

          <div class="h-64 mt-4 relative flex items-center justify-center">
            <canvas ref="conditionChartRef"></canvas>
            
            <!-- Center Doughnut Badge -->
            <div class="absolute inset-0 flex flex-col items-center justify-center pointer-events-none pb-8">
              <span class="text-2xl font-black text-surface-foreground">{{ metrics.total_items }}</span>
              <span class="text-[10px] text-surface-on-variant font-semibold">Total Item</span>
            </div>
          </div>
        </div>

        <!-- Chart 3: Distribusi Barang per Ruangan / Laboratorium -->
        <div class="bg-surface-container-lowest rounded-m3-xl p-5 sm:p-6 border border-outline-variant/40 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
            <div>
              <h3 class="text-sm font-bold text-surface-foreground flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-indigo-600">apartment</span>
                <span>Penempatan Barang per Ruangan / Lab</span>
              </h3>
              <p class="text-[11px] text-surface-on-variant mt-0.5">Sebaran aset pada laboratorium kejuruan dan ruang kelas</p>
            </div>
            <Link :href="route('master-data.rooms.index')" class="text-xs font-bold text-primary hover:underline">
              Kelola Ruangan
            </Link>
          </div>

          <div class="h-64 mt-4 relative flex items-center justify-center">
            <canvas ref="roomChartRef"></canvas>
          </div>
        </div>

        <!-- Chart 4: Peruntukan Fungsi Operasional Barang -->
        <div class="bg-surface-container-lowest rounded-m3-xl p-5 sm:p-6 border border-outline-variant/40 shadow-xs flex flex-col justify-between">
          <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
            <div>
              <h3 class="text-sm font-bold text-surface-foreground flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-pink-600">pie_chart</span>
                <span>Peruntukan Fungsi Operasional Aset</span>
              </h3>
              <p class="text-[11px] text-surface-on-variant mt-0.5">Praktikum Siswa, Media Teori, Infrastruktur Jaringan, dsb.</p>
            </div>
            <Link :href="route('master-data.item-functions.index')" class="text-xs font-bold text-primary hover:underline">
              Data Fungsi
            </Link>
          </div>

          <div class="h-64 mt-4 relative flex items-center justify-center">
            <canvas ref="functionChartRef"></canvas>
          </div>
        </div>

      </div>

      <!-- 4. Recent Audited Items Table Widget -->
      <div class="bg-surface-container-lowest rounded-m3-xl p-5 sm:p-6 border border-outline-variant/40 shadow-xs space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
          <div>
            <h3 class="text-sm font-bold text-surface-foreground flex items-center gap-2">
              <span class="material-symbols-outlined text-[20px] text-primary">schedule</span>
              <span>Barang Inventaris Terbaru yang Didata</span>
            </h3>
            <p class="text-xs text-surface-on-variant">6 aset terakhir yang baru saja diinput oleh staf admin</p>
          </div>

          <Link
            :href="route('inventory.items.index')"
            class="px-3.5 py-1.5 rounded-full bg-primary/10 text-primary hover:bg-primary hover:text-white text-xs font-bold transition-colors inline-flex items-center gap-1 cursor-pointer"
          >
            <span>Buka Inventaris</span>
            <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
          </Link>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="border-b border-outline-variant/40 text-surface-on-variant font-semibold uppercase tracking-wider bg-surface-container-low/40">
                <th class="py-2.5 px-3 rounded-l-m3-xs">Foto</th>
                <th class="py-2.5 px-3">Nama Barang & Merk</th>
                <th class="py-2.5 px-3">Kategori</th>
                <th class="py-2.5 px-3 text-center">Jumlah</th>
                <th class="py-2.5 px-3 text-center">Kondisi</th>
                <th class="py-2.5 px-3">Ruangan</th>
                <th class="py-2.5 px-3 text-right rounded-r-m3-xs">Admin & Tanggal</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
              <tr v-for="item in recentItems" :key="item.id" class="hover:bg-surface-container-low transition-colors">
                <!-- Thumbnail -->
                <td class="py-2.5 px-3">
                  <div class="w-9 h-9 rounded-m3-xs bg-surface-container overflow-hidden border border-outline-variant/40 flex items-center justify-center shrink-0">
                    <img v-if="item.photo_path" :src="item.photo_path" :alt="item.name" class="w-full h-full object-cover" />
                    <span v-else class="material-symbols-outlined text-[18px] text-surface-on-variant/50">image_not_supported</span>
                  </div>
                </td>

                <!-- Name & Brand -->
                <td class="py-2.5 px-3 max-w-xs">
                  <div class="font-bold text-surface-foreground text-xs line-clamp-1">{{ item.name }}</div>
                  <div class="text-[10px] text-surface-on-variant font-semibold text-primary">{{ item.brand || '-' }}</div>
                </td>

                <!-- Category -->
                <td class="py-2.5 px-3">
                  <span class="px-2 py-0.5 rounded bg-surface-container text-[10px] font-semibold text-surface-foreground">
                    {{ item.category ? item.category.name : '-' }}
                  </span>
                </td>

                <!-- Quantity -->
                <td class="py-2.5 px-3 text-center font-bold text-surface-foreground">
                  {{ item.quantity }} Unit
                </td>

                <!-- Condition -->
                <td class="py-2.5 px-3 text-center">
                  <span
                    class="px-2 py-0.5 rounded-full text-[10px] font-bold"
                    :class="item.condition === 'Baik' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'"
                  >
                    {{ item.condition }}
                  </span>
                </td>

                <!-- Room -->
                <td class="py-2.5 px-3 text-surface-foreground font-medium">
                  {{ item.room ? item.room.name : '-' }}
                </td>

                <!-- Admin & Date -->
                <td class="py-2.5 px-3 text-right">
                  <div class="font-semibold text-surface-foreground text-[11px]">{{ item.creator ? item.creator.name : 'Sistem' }}</div>
                  <div class="text-[10px] text-surface-on-variant">{{ formatDate(item.created_at) }}</div>
                </td>
              </tr>

              <tr v-if="recentItems.length === 0">
                <td colspan="7" class="py-8 text-center text-surface-on-variant">
                  Belum ada data barang inventaris.
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>

      <!-- 5. Quick Access Navigation Cards (Data Referensi) -->
      <div class="grid grid-cols-2 sm:grid-cols-5 gap-3">
        <!-- Data Sekolah -->
        <Link
          :href="route('master-data.schools.index')"
          class="p-4 rounded-m3-lg bg-surface-container-lowest hover:bg-primary-container/40 border border-outline-variant/40 transition-all flex flex-col items-center text-center group cursor-pointer"
        >
          <div class="w-10 h-10 rounded-full bg-primary-container text-primary flex items-center justify-center group-hover:scale-110 transition-transform">
            <span class="material-symbols-outlined text-[20px]">school</span>
          </div>
          <span class="text-xs font-bold text-surface-foreground mt-2">Data Sekolah</span>
          <span class="text-[10px] text-surface-on-variant">Profil & Lokasi Map</span>
        </Link>

        <!-- Kategori Barang -->
        <Link
          :href="route('master-data.item-categories.index')"
          class="p-4 rounded-m3-lg bg-surface-container-lowest hover:bg-primary-container/40 border border-outline-variant/40 transition-all flex flex-col items-center text-center group cursor-pointer"
        >
          <div class="w-10 h-10 rounded-full bg-amber-100 text-amber-800 flex items-center justify-center group-hover:scale-110 transition-transform">
            <span class="material-symbols-outlined text-[20px]">category</span>
          </div>
          <span class="text-xs font-bold text-surface-foreground mt-2">Kategori Barang</span>
          <span class="text-[10px] text-surface-on-variant">{{ metrics.total_categories }} Kategori</span>
        </Link>

        <!-- Data Gedung -->
        <Link
          :href="route('master-data.buildings.index')"
          class="p-4 rounded-m3-lg bg-surface-container-lowest hover:bg-primary-container/40 border border-outline-variant/40 transition-all flex flex-col items-center text-center group cursor-pointer"
        >
          <div class="w-10 h-10 rounded-full bg-blue-100 text-blue-800 flex items-center justify-center group-hover:scale-110 transition-transform">
            <span class="material-symbols-outlined text-[20px]">domain</span>
          </div>
          <span class="text-xs font-bold text-surface-foreground mt-2">Data Gedung</span>
          <span class="text-[10px] text-surface-on-variant">{{ metrics.total_buildings }} Gedung</span>
        </Link>

        <!-- Data Ruangan -->
        <Link
          :href="route('master-data.rooms.index')"
          class="p-4 rounded-m3-lg bg-surface-container-lowest hover:bg-primary-container/40 border border-outline-variant/40 transition-all flex flex-col items-center text-center group cursor-pointer"
        >
          <div class="w-10 h-10 rounded-full bg-purple-100 text-purple-800 flex items-center justify-center group-hover:scale-110 transition-transform">
            <span class="material-symbols-outlined text-[20px]">meeting_room</span>
          </div>
          <span class="text-xs font-bold text-surface-foreground mt-2">Data Ruangan</span>
          <span class="text-[10px] text-surface-on-variant">{{ metrics.total_rooms }} Lab & Kelas</span>
        </Link>

        <!-- Fungsi Barang -->
        <Link
          :href="route('master-data.item-functions.index')"
          class="p-4 rounded-m3-lg bg-surface-container-lowest hover:bg-primary-container/40 border border-outline-variant/40 transition-all flex flex-col items-center text-center group cursor-pointer col-span-2 sm:col-span-1"
        >
          <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-800 flex items-center justify-center group-hover:scale-110 transition-transform">
            <span class="material-symbols-outlined text-[20px]">construction</span>
          </div>
          <span class="text-xs font-bold text-surface-foreground mt-2">Fungsi Barang</span>
          <span class="text-[10px] text-surface-on-variant">{{ metrics.total_functions }} Klasifikasi</span>
        </Link>
      </div>

    </div>
  </AuthenticatedLayout>
</template>
