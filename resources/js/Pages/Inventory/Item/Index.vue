<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import M3Button from '@/Components/M3Button.vue';
import M3TextField from '@/Components/M3TextField.vue';
import M3Pagination from '@/Components/M3Pagination.vue';
import M3InteractiveModal from '@/Components/M3InteractiveModal.vue';
import M3SearchableSelect from '@/Components/M3SearchableSelect.vue';
import M3PhotoUpload from '@/Components/M3PhotoUpload.vue';
import { useAlert } from '@/Composables/useAlert';
import { useToast } from '@/Composables/useToast';
import { Head, Link, router, useForm, usePage } from '@inertiajs/vue3';
import { ref, computed, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  items: {
    type: Object,
    required: true,
  },
  statistics: {
    type: Object,
    default: () => ({ total_items: 0, total_good: 0, total_damaged: 0, total_quantity: 0 }),
  },
  categories: {
    type: Array,
    default: () => [],
  },
  buildings: {
    type: Array,
    default: () => [],
  },
  rooms: {
    type: Array,
    default: () => [],
  },
  functions: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({
      search: '',
      condition: '',
      category_id: '',
      building_id: '',
      room_id: '',
      function_id: '',
    }),
  },
});

const page = usePage();
const alert = useAlert();
const toast = useToast();

// Governance Role & Cutoff Status
const isSuperAdmin = computed(() => page.props.auth.user?.role === 'super_admin');
const isAnggota = computed(() => page.props.auth.user?.role === 'anggota');
const isFinalized = computed(() => Boolean(page.props.auth.user?.has_finalized));
const isCutoff = computed(() => Boolean(page.props.governance?.active_period?.is_cutoff_passed));
const isActionDisabled = computed(() => isAnggota.value && (isFinalized.value || isCutoff.value));

// Ownership check: Anggota can only edit/delete items they created
const canModifyItem = (item) => {
  if (isSuperAdmin.value) return true;
  if (isActionDisabled.value) return false;
  return item.created_by === page.props.auth.user?.id;
};

// Countdown Timer State
const countdownText = ref('');
let timerInterval = null;

const updateCountdown = () => {
  const cutoffStr = page.props.governance?.active_period?.cutoff_date;
  if (!cutoffStr) {
    countdownText.value = 'Tidak ada batas cutoff aktif';
    return;
  }

  const cutoffTime = new Date(cutoffStr).getTime();
  const now = new Date().getTime();
  const diff = cutoffTime - now;

  if (diff <= 0) {
    countdownText.value = 'WAKTU CUT-OFF TELAH HABIS';
    return;
  }

  const days = Math.floor(diff / (1000 * 60 * 60 * 24));
  const hours = Math.floor((diff % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
  const minutes = Math.floor((diff % (1000 * 60 * 60)) / (1000 * 60));
  const seconds = Math.floor((diff % (1000 * 60)) / 1000);

  countdownText.value = `${days}h ${hours}j ${minutes}m ${seconds}d`;
};

onMounted(() => {
  updateCountdown();
  timerInterval = setInterval(updateCountdown, 1000);
});

onUnmounted(() => {
  if (timerInterval) clearInterval(timerInterval);
});

// Search & Filter State
const search = ref(props.filters.search || '');
const filterCondition = ref(props.filters.condition || '');
const filterCategory = ref(props.filters.category_id || '');
const filterBuilding = ref(props.filters.building_id || '');
const filterRoom = ref(props.filters.room_id || '');
const filterFunction = ref(props.filters.function_id || '');
let searchTimeout = null;

const applyFilters = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get(
      route('inventory.items.index'),
      {
        search: search.value,
        condition: filterCondition.value,
        category_id: filterCategory.value,
        building_id: filterBuilding.value,
        room_id: filterRoom.value,
        function_id: filterFunction.value,
      },
      { preserveState: true, preserveScroll: true, replace: true }
    );
  }, 400);
};

// Filtered Rooms in Form based on selected building
const formRooms = computed(() => {
  if (!form.building_id) return props.rooms;
  return props.rooms.filter((r) => r.building_id === form.building_id);
});

// Modal & Form State
const isModalOpen = ref(false);
const isEditing = ref(false);
const selectedItem = ref(null);
const isBulkSeeding = ref(false);
const isExporting = ref(false);

// Detail Modal State
const isDetailModalOpen = ref(false);
const detailItem = ref(null);

const openDetailModal = (item) => {
  detailItem.value = item;
  isDetailModalOpen.value = true;
};

const form = useForm({
  name: '',
  serial_number: '',
  has_no_serial_number: false,
  brand: '',
  quantity: 1,
  condition: 'Baik',
  category_id: '',
  building_id: '',
  room_id: '',
  function_id: '',
  notes: '',
  photo: null,
});

watch(
  () => form.has_no_serial_number,
  (val) => {
    if (val) {
      form.serial_number = '';
    }
  }
);

watch(
  () => form.building_id,
  (newBuildingId) => {
    if (form.room_id) {
      const room = props.rooms.find((r) => r.id === form.room_id);
      if (room && room.building_id !== newBuildingId) {
        form.room_id = '';
      }
    }
  }
);

const openCreateModal = () => {
  if (isActionDisabled.value) {
    toast.warning('Aksi tidak diizinkan. Pendataan telah melewati batas cutoff atau telah difinalisasi.');
    return;
  }
  isEditing.value = false;
  selectedItem.value = null;
  form.reset();
  form.clearErrors();
  form.condition = 'Baik';
  form.quantity = 1;
  form.has_no_serial_number = false;
  form.photo = null;
  isModalOpen.value = true;
};

const openEditModal = (item) => {
  if (!canModifyItem(item)) {
    toast.warning('Anda hanya memiliki hak akses untuk mengubah barang yang Anda input sendiri.');
    return;
  }
  isEditing.value = true;
  selectedItem.value = item;
  form.clearErrors();
  form.name = item.name;
  form.serial_number = item.serial_number || '';
  form.has_no_serial_number = Boolean(item.has_no_serial_number);
  form.brand = item.brand || '';
  form.quantity = item.quantity || 1;
  form.condition = item.condition || 'Baik';
  form.category_id = item.category_id || '';
  form.building_id = item.building_id || '';
  form.room_id = item.room_id || '';
  form.function_id = item.function_id || '';
  form.notes = item.notes || '';
  form.photo = null;
  isModalOpen.value = true;
};

const handleSubmit = () => {
  if (isEditing.value && selectedItem.value) {
    form.transform((data) => ({ ...data, _method: 'PUT' }))
      .post(route('inventory.items.update', selectedItem.value.id), {
        onSuccess: () => {
          isModalOpen.value = false;
          form.reset();
        },
      });
  } else {
    form.post(route('inventory.items.store'), {
      onSuccess: () => {
        isModalOpen.value = false;
        form.reset();
      },
    });
  }
};

const handleDelete = async (item) => {
  if (!canModifyItem(item)) {
    toast.warning('Anda hanya memiliki hak akses untuk menghapus barang yang Anda input sendiri.');
    return;
  }

  const isConfirmed = await alert.confirm({
    title: 'Arsipkan Barang Ini?',
    message: `Apakah Anda yakin ingin memindahkan barang "${item.name}" ke arsip inventaris? Data akan disimpan dengan sistem Soft Delete dan dapat diaudit kembali.`,
    type: 'error',
    confirmText: 'Ya, Arsipkan Barang',
    cancelText: 'Batal',
  });

  if (isConfirmed) {
    router.delete(route('inventory.items.destroy', item.id), {
      preserveScroll: true,
    });
  }
};

const handleBulkSeed = async () => {
  const isConfirmed = await alert.confirm({
    title: 'Muat Data Inventaris Awal?',
    message: 'Aksi ini akan menyelaraskan data inventaris barang sekolah contoh (PC ROG, Router Mikrotik, Fusion Splicer, Smartboard 75", dll).',
    type: 'question',
    confirmText: 'Ya, Muat Data',
    cancelText: 'Batal',
  });

  if (isConfirmed) {
    isBulkSeeding.value = true;
    router.post(
      route('inventory.items.bulk-seed'),
      {},
      {
        preserveScroll: true,
        onFinish: () => {
          isBulkSeeding.value = false;
        },
      }
    );
  }
};

const isClearing = ref(false);

const handleClearInventory = async () => {
  const isConfirmed = await alert.confirm({
    title: 'Kosongkan Seluruh Data Inventaris?',
    message: 'PERINGATAN: Tindakan ini akan menghapus seluruh data barang inventaris yang telah didata, foto barang, serta mereset berita acara finalisasi. Data yang dihapus TIDAK DAPAT dipulihkan!',
    type: 'error',
    confirmText: 'Ya, Kosongkan Data',
    cancelText: 'Batal',
  });

  if (isConfirmed) {
    isClearing.value = true;
    router.post(
      route('system.reset.inventory'),
      {},
      {
        preserveScroll: true,
        onFinish: () => {
          isClearing.value = false;
        },
      }
    );
  }
};

const handleExportExcel = () => {
  isExporting.value = true;
  toast.info('Menyiapkan file Excel laporan inventaris barang...');

  const params = new URLSearchParams({
    search: search.value || '',
    condition: filterCondition.value || '',
    category_id: filterCategory.value || '',
    building_id: filterBuilding.value || '',
    room_id: filterRoom.value || '',
    function_id: filterFunction.value || '',
  });

  window.location.href = `${route('inventory.items.export')}?${params.toString()}`;

  setTimeout(() => {
    isExporting.value = false;
    toast.success('File Excel laporan inventaris berhasil diunduh.');
  }, 1200);
};

const handlePageChange = (page) => {
  router.get(
    route('inventory.items.index'),
    {
      page,
      search: search.value,
      condition: filterCondition.value,
      category_id: filterCategory.value,
      building_id: filterBuilding.value,
      room_id: filterRoom.value,
      function_id: filterFunction.value,
    },
    { preserveState: true, preserveScroll: true }
  );
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'short',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>

<template>
  <Head title="Inventaris Barang - Pencatatan Aset Sekolah" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      
      <!-- Cutoff & Finalization Alert Notification -->
      <div
        v-if="isActionDisabled"
        class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-m3-md shadow-xs flex items-center justify-between gap-4 flex-wrap"
      >
        <div class="flex items-center gap-3">
          <span class="material-symbols-outlined text-[24px] text-amber-600 shrink-0">lock_clock</span>
          <div class="text-xs text-amber-900 leading-relaxed">
            <strong class="font-bold block text-sm">
              {{ isFinalized ? 'Data Telah Difinalisasi' : 'Batas Waktu Cut-off Tercapai' }}
            </strong>
            Fitur tambah, ubah, dan hapus barang telah dinonaktifkan. Anda hanya dapat melihat dan mengunduh laporan Excel.
          </div>
        </div>

        <Link
          :href="route('data-finalization.index')"
          class="h-9 px-4 rounded-m3-full bg-primary text-white text-xs font-bold inline-flex items-center gap-1.5 shadow-xs hover:bg-primary-hover transition-all shrink-0"
        >
          <span>{{ isFinalized ? 'Lihat Berita Acara' : 'Lakukan Finalisasi Data' }}</span>
          <span class="material-symbols-outlined text-[16px]">arrow_forward</span>
        </Link>
      </div>

      <!-- Cutoff Countdown Timer Bar -->
      <div
        v-if="$page.props.governance?.active_period"
        class="bg-gradient-to-r from-slate-900 via-primary to-slate-900 rounded-m3-xl p-4 sm:p-5 text-white flex flex-col sm:flex-row items-center justify-between gap-4 shadow-sm"
      >
        <div class="flex items-center gap-3 text-center sm:text-left">
          <div class="w-10 h-10 rounded-full bg-white/15 backdrop-blur-md flex items-center justify-center text-white shrink-0">
            <span class="material-symbols-outlined text-[22px]">timer</span>
          </div>
          <div>
            <div class="text-[11px] text-white/80 font-medium">Periode: {{ $page.props.governance.active_period.name }}</div>
            <div class="text-xs font-bold">Hitung Mundur Batas Akhir Pendataan (Cut-off)</div>
          </div>
        </div>

        <!-- Live Countdown Badge -->
        <div class="px-4 py-2 rounded-m3-full bg-white/10 backdrop-blur-md border border-white/20 font-mono font-bold text-sm tracking-wider text-amber-300 shadow-xs">
          {{ countdownText }}
        </div>
      </div>

      <!-- Top Instruction & Guidance Box -->
      <div class="bg-gradient-to-r from-primary/10 via-primary/5 to-surface-container rounded-m3-xl p-5 sm:p-6 border border-primary/20 shadow-xs">
        <div class="flex items-start gap-4">
          <div class="w-10 h-10 rounded-m3-md bg-primary text-primary-foreground flex items-center justify-center shrink-0 shadow-sm mt-0.5">
            <span class="material-symbols-outlined text-[24px]">menu_book</span>
          </div>
          <div class="space-y-1">
            <h3 class="text-sm font-bold text-surface-foreground">
              Petunjuk Pendataan Inventaris & Aset Sekolah
            </h3>
            <p class="text-xs text-surface-on-variant leading-relaxed">
              Halaman ini digunakan untuk pencatatan barang dari awal sebelum resmi dijadikan aset tetap sekolah. 
              Sebagai <strong>Anggota Tim</strong>, Anda dapat mencatat barang baru serta mengubah/menghapus barang yang Anda input sendiri. Klik ikon <strong>Mata (Detail)</strong> untuk melihat informasi lengkap audit barang.
            </p>
          </div>
        </div>
      </div>

      <!-- Header & Statistics Cards -->
      <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
        <div class="bg-surface-container-lowest p-4 sm:p-5 rounded-m3-lg border border-outline-variant/40 shadow-xs flex items-center justify-between">
          <div>
            <div class="text-[11px] font-medium text-surface-on-variant">Total Jenis Barang</div>
            <div class="text-xl sm:text-2xl font-black text-surface-foreground mt-0.5">{{ statistics.total_items }} Item</div>
          </div>
          <div class="w-10 h-10 rounded-m3-md bg-primary-container text-primary flex items-center justify-center">
            <span class="material-symbols-outlined text-[22px]">inventory_2</span>
          </div>
        </div>

        <div class="bg-surface-container-lowest p-4 sm:p-5 rounded-m3-lg border border-outline-variant/40 shadow-xs flex items-center justify-between">
          <div>
            <div class="text-[11px] font-medium text-surface-on-variant">Total Kuantitas Fisik</div>
            <div class="text-xl sm:text-2xl font-black text-surface-foreground mt-0.5">{{ statistics.total_quantity }} Unit</div>
          </div>
          <div class="w-10 h-10 rounded-m3-md bg-secondary-container text-secondary flex items-center justify-center">
            <span class="material-symbols-outlined text-[22px]">widgets</span>
          </div>
        </div>

        <div class="bg-surface-container-lowest p-4 sm:p-5 rounded-m3-lg border border-outline-variant/40 shadow-xs flex items-center justify-between">
          <div>
            <div class="text-[11px] font-medium text-surface-on-variant">Kondisi Baik</div>
            <div class="text-xl sm:text-2xl font-black text-emerald-700 mt-0.5">{{ statistics.total_good }} Item</div>
          </div>
          <div class="w-10 h-10 rounded-m3-md bg-emerald-100 text-emerald-700 flex items-center justify-center">
            <span class="material-symbols-outlined text-[22px]">check_circle</span>
          </div>
        </div>

        <div class="bg-surface-container-lowest p-4 sm:p-5 rounded-m3-lg border border-outline-variant/40 shadow-xs flex items-center justify-between">
          <div>
            <div class="text-[11px] font-medium text-surface-on-variant">Kondisi Rusak</div>
            <div class="text-xl sm:text-2xl font-black text-error mt-0.5">{{ statistics.total_damaged }} Item</div>
          </div>
          <div class="w-10 h-10 rounded-m3-md bg-red-100 text-error flex items-center justify-center">
            <span class="material-symbols-outlined text-[22px]">warning</span>
          </div>
        </div>
      </div>

      <!-- Action & Filter Bar Card -->
      <div class="bg-surface-container-lowest rounded-m3-xl p-5 sm:p-6 border border-outline-variant/40 shadow-sm space-y-4">
        
        <!-- Action Row -->
        <div class="flex flex-col md:flex-row md:items-center justify-between gap-3 pb-3 border-b border-outline-variant/30">
          <div>
            <h2 class="text-base font-bold text-surface-foreground">Daftar Inventarisasi Barang</h2>
            <p class="text-xs text-surface-on-variant">Catatan seluruh barang sekolah dengan detail penempatan & rekam jejak admin</p>
          </div>

          <div class="flex flex-wrap items-center gap-2.5">
            <!-- Export Excel Button -->
            <button
              type="button"
              @click="handleExportExcel"
              :disabled="isExporting"
              class="h-10 px-4 rounded-m3-full bg-emerald-700 hover:bg-emerald-800 text-white font-semibold text-xs inline-flex items-center gap-2 shadow-xs transition-all cursor-pointer disabled:opacity-50"
              title="Unduh data inventaris dalam format Microsoft Excel"
            >
              <span class="material-symbols-outlined text-[18px]">table_view</span>
              <span>Export Excel</span>
            </button>

            <!-- Kosongkan Data Button (SUPER ADMIN ONLY) -->
            <button
              v-if="isSuperAdmin"
              type="button"
              @click="handleClearInventory"
              :disabled="isClearing || statistics.total_items === 0"
              class="h-10 px-4 rounded-m3-full bg-red-50 hover:bg-red-100 text-error border border-red-200 font-semibold text-xs inline-flex items-center gap-1.5 transition-all cursor-pointer disabled:opacity-40 disabled:cursor-not-allowed"
              title="Kosongkan seluruh data inventaris barang dan berita acara"
            >
              <span class="material-symbols-outlined text-[18px]">delete_sweep</span>
              <span>{{ isClearing ? 'Mengosongkan...' : 'Kosongkan Data' }}</span>
            </button>

            <!-- Muat Data Contoh Button (SUPER ADMIN ONLY) -->
            <M3Button
              v-if="isSuperAdmin"
              variant="tonal"
              icon="auto_fix_high"
              size="medium"
              :loading="isBulkSeeding"
              :disabled="isBulkSeeding"
              @click="handleBulkSeed"
            >
              Muat Data Contoh
            </M3Button>

            <!-- Create Button -->
            <M3Button
              variant="filled"
              icon="add"
              size="large"
              :disabled="isActionDisabled"
              @click="openCreateModal"
            >
              Catat Barang Baru
            </M3Button>
          </div>
        </div>

        <!-- Filters Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-3">
          <div class="relative">
            <span class="material-symbols-outlined text-[18px] text-surface-on-variant absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none">
              search
            </span>
            <input
              type="text"
              v-model="search"
              @input="applyFilters"
              placeholder="Cari nama, merk, SN, ruangan..."
              class="w-full h-11 pl-10 pr-4 rounded-m3-full border border-outline focus:border-primary focus:ring-0 bg-surface-container-low text-xs text-surface-foreground placeholder:text-surface-on-variant"
            />
          </div>

          <select
            v-model="filterCondition"
            @change="applyFilters"
            class="h-11 px-4 rounded-m3-full border border-outline focus:border-primary focus:ring-0 bg-surface-container-low text-xs text-surface-foreground cursor-pointer"
          >
            <option value="">Semua Kondisi (Baik & Rusak)</option>
            <option value="Baik">Kondisi Baik</option>
            <option value="Rusak">Kondisi Rusak</option>
          </select>

          <select
            v-model="filterCategory"
            @change="applyFilters"
            class="h-11 px-4 rounded-m3-full border border-outline focus:border-primary focus:ring-0 bg-surface-container-low text-xs text-surface-foreground cursor-pointer truncate"
          >
            <option value="">Semua Kategori Barang</option>
            <option v-for="cat in categories" :key="cat.id" :value="cat.id">
              {{ cat.code }} - {{ cat.name }}
            </option>
          </select>

          <select
            v-model="filterBuilding"
            @change="applyFilters"
            class="h-11 px-4 rounded-m3-full border border-outline focus:border-primary focus:ring-0 bg-surface-container-low text-xs text-surface-foreground cursor-pointer truncate"
          >
            <option value="">Semua Lokasi Gedung</option>
            <option v-for="bld in buildings" :key="bld.id" :value="bld.id">
              {{ bld.name }}
            </option>
          </select>
        </div>

        <!-- Inventory Data Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="border-b border-outline-variant/40 text-surface-on-variant font-semibold uppercase tracking-wider bg-surface-container-low/50">
                <th class="py-3 px-3.5 w-16 text-center rounded-l-m3-xs">Foto</th>
                <th class="py-3 px-3.5">Nama Barang & Merk</th>
                <th class="py-3 px-3.5">Serial Number</th>
                <th class="py-3 px-3.5 text-center">Jumlah</th>
                <th class="py-3 px-3.5 text-center">Kondisi</th>
                <th class="py-3 px-3.5">Lokasi Penempatan</th>
                <th class="py-3 px-3.5">Admin Pencatat</th>
                <th class="py-3 px-3.5 text-right rounded-r-m3-xs">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
              <tr
                v-for="item in items.data"
                :key="item.id"
                class="hover:bg-surface-container-low transition-colors"
              >
                <!-- Photo Thumbnail -->
                <td class="py-3 px-3.5 text-center">
                  <div
                    class="w-12 h-12 rounded-m3-xs bg-surface-container overflow-hidden border border-outline-variant/40 flex items-center justify-center shrink-0 mx-auto shadow-xs cursor-pointer group"
                    @click="openDetailModal(item)"
                    title="Klik untuk melihat detail barang"
                  >
                    <img
                      v-if="item.photo_path"
                      :src="item.photo_path"
                      :alt="item.name"
                      class="w-full h-full object-cover group-hover:scale-110 transition-transform"
                    />
                    <span v-else class="material-symbols-outlined text-[20px] text-surface-on-variant/60">
                      image_not_supported
                    </span>
                  </div>
                </td>

                <!-- Name & Brand -->
                <td class="py-3 px-3.5 max-w-xs">
                  <div
                    class="font-bold text-surface-foreground text-xs leading-snug line-clamp-2 hover:text-primary cursor-pointer"
                    @click="openDetailModal(item)"
                    :title="item.name"
                  >
                    {{ item.name }}
                  </div>
                  <div class="text-[11px] text-surface-on-variant font-medium mt-0.5 flex items-center gap-1.5">
                    <span v-if="item.brand" class="font-semibold text-primary">Merk: {{ item.brand }}</span>
                    <span v-if="item.category" class="px-1.5 py-0.2 rounded bg-surface-container text-[10px]">
                      {{ item.category.name }}
                    </span>
                  </div>
                </td>

                <!-- Serial Number -->
                <td class="py-3 px-3.5 font-mono text-[11px]">
                  <span v-if="item.has_no_serial_number" class="text-surface-on-variant/70 italic">
                    Tanpa SN
                  </span>
                  <span v-else-if="item.serial_number" class="px-2 py-0.5 rounded bg-surface-container font-semibold text-surface-foreground border border-outline-variant/40">
                    {{ item.serial_number }}
                  </span>
                  <span v-else class="text-surface-on-variant/50">-</span>
                </td>

                <!-- Quantity -->
                <td class="py-3 px-3.5 text-center font-bold text-surface-foreground">
                  <span class="px-2 py-0.5 rounded-full bg-surface-container-high text-xs">
                    {{ item.quantity }} Unit
                  </span>
                </td>

                <!-- Condition -->
                <td class="py-3 px-3.5 text-center">
                  <span
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold inline-flex items-center gap-1 shadow-2xs"
                    :class="item.condition === 'Baik' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'"
                  >
                    <span class="w-1.5 h-1.5 rounded-full" :class="item.condition === 'Baik' ? 'bg-emerald-600' : 'bg-red-600'"></span>
                    <span>{{ item.condition }}</span>
                  </span>
                </td>

                <!-- Location -->
                <td class="py-3 px-3.5 text-xs">
                  <div class="font-bold text-surface-foreground flex items-center gap-1">
                    <span class="material-symbols-outlined text-[15px] text-primary">meeting_room</span>
                    <span>{{ item.room ? item.room.name : 'Ruangan -' }}</span>
                  </div>
                  <div class="text-[10px] text-surface-on-variant mt-0.5">
                    {{ item.building ? item.building.name : 'Gedung -' }}
                    <span v-if="item.item_function" class="text-primary font-medium"> &bull; {{ item.item_function.name }}</span>
                  </div>
                </td>

                <!-- Audit Admin & Ownership Badge -->
                <td class="py-3 px-3.5 text-[11px]">
                  <div class="font-semibold text-surface-foreground flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px] text-surface-on-variant">account_circle</span>
                    <span>{{ item.creator ? item.creator.name : 'Sistem' }}</span>
                  </div>
                  <div class="text-[10px] text-surface-on-variant font-mono mt-0.5 flex items-center gap-1">
                    <span>{{ formatDate(item.created_at) }}</span>
                    <span
                      v-if="item.created_by === $page.props.auth.user?.id"
                      class="px-1.5 py-0.2 rounded bg-primary/10 text-primary font-bold text-[9px]"
                      title="Barang yang Anda input sendiri"
                    >
                      Milik Saya
                    </span>
                  </div>
                </td>

                <!-- Actions: Detail (Eye), Edit, Delete -->
                <td class="py-3.5 px-3.5 text-right">
                  <div class="flex items-center justify-end gap-1">
                    <!-- 1. Eye Button (View Detail) - ALWAYS AVAILABLE -->
                    <button
                      type="button"
                      @click="openDetailModal(item)"
                      class="p-2 text-primary hover:bg-primary/10 rounded-m3-full transition-colors cursor-pointer"
                      title="Lihat Detail Barang"
                    >
                      <span class="material-symbols-outlined text-[18px]">visibility</span>
                    </button>

                    <!-- 2. Edit Button (Only if Super Admin or Owned by Anggota) -->
                    <button
                      v-if="canModifyItem(item)"
                      type="button"
                      @click="openEditModal(item)"
                      class="p-2 text-surface-on-variant hover:text-primary hover:bg-surface-variant/40 rounded-m3-full transition-colors cursor-pointer"
                      title="Ubah Data Barang"
                    >
                      <span class="material-symbols-outlined text-[18px]">edit</span>
                    </button>

                    <!-- 3. Delete Button (Only if Super Admin or Owned by Anggota) -->
                    <button
                      v-if="canModifyItem(item)"
                      type="button"
                      @click="handleDelete(item)"
                      class="p-2 text-surface-on-variant hover:text-error hover:bg-error-container/40 rounded-m3-full transition-colors cursor-pointer"
                      title="Arsipkan / Hapus Barang"
                    >
                      <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>

                    <!-- View Only Badge for Other People's Items on Anggota role -->
                    <span
                      v-if="!canModifyItem(item) && isAnggota"
                      class="px-2 py-1 rounded-full bg-surface-container text-surface-on-variant text-[10px] font-medium"
                      title="Hanya dapat melihat (diinput oleh pengguna lain atau data dikunci)"
                    >
                      Lihat Saja
                    </span>
                  </div>
                </td>
              </tr>

              <tr v-if="items.data.length === 0">
                <td colspan="8" class="py-12 text-center text-surface-on-variant">
                  Belum ada data barang inventaris yang tercatat.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="pt-2 border-t border-outline-variant/30">
          <M3Pagination
            :current-page="items.current_page"
            :total-items="items.total"
            :per-page="items.per_page"
            @page-change="handlePageChange"
          />
        </div>

      </div>
    </div>

    <!-- MODAL 1: FORM INPUT & EDIT BARANG -->
    <M3InteractiveModal
      v-model="isModalOpen"
      :title="isEditing ? 'Ubah Data Inventaris Barang' : 'Pencatatan Inventaris Barang Baru'"
      subtitle="Silakan lengkapi spesifikasi barang, nomor seri, foto dokumentasi, dan lokasi ruangan."
      max-width="max-w-3xl"
    >
      <form @submit.prevent="handleSubmit" autocomplete="off" class="space-y-4 py-2">
        <div class="p-3 bg-surface-container-low rounded-m3-xs border border-outline-variant/40 flex items-start gap-2.5">
          <span class="material-symbols-outlined text-[20px] text-primary shrink-0 mt-0.5">info</span>
          <p class="text-[11px] text-surface-on-variant leading-relaxed">
            Akun Anda (<strong>{{ $page.props.auth.user.name }}</strong>) akan otomatis direkam sebagai admin pencatat barang ini.
          </p>
        </div>

        <!-- 1. Nama Barang -->
        <div>
          <label class="block text-xs font-semibold text-surface-foreground mb-1">
            Nama Barang Lengkap & Tipe/Seri <span class="text-error">*</span>
          </label>
          <M3TextField
            id="item_name"
            name="item_name_input"
            v-model="form.name"
            label="Contoh: PC Desktop Asus ROG Strix GT15 Core i7 16GB"
            leading-icon="devices"
            required
            autocomplete="off"
            :error-message="form.errors.name"
          />
          <p class="text-[11px] text-surface-on-variant mt-1">
            * <strong>Panduan:</strong> Tuliskan nama barang secara detail beserta spesifikasi/tipe utamanya.
          </p>
        </div>

        <!-- 2. Serial Number & Checklist Tanpa SN -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              Serial Number (SN) / No. Seri
            </label>
            <M3TextField
              id="item_sn"
              name="item_sn_input"
              v-model="form.serial_number"
              :disabled="form.has_no_serial_number"
              label="Contoh: ROG-2026-X89211"
              leading-icon="qr_code"
              autocomplete="off"
              :error-message="form.errors.serial_number"
            />

            <div class="mt-2 flex items-center gap-2">
              <input
                type="checkbox"
                id="no_sn_checkbox"
                v-model="form.has_no_serial_number"
                class="w-4 h-4 rounded text-primary focus:ring-primary/30 cursor-pointer"
              />
              <label for="no_sn_checkbox" class="text-xs text-surface-foreground font-medium cursor-pointer select-none">
                Barang ini <strong>tidak memiliki Serial Number</strong>
              </label>
            </div>
          </div>

          <!-- 3. Merk / Brand -->
          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              Merk / Brand Pabrikan
            </label>
            <M3TextField
              id="item_brand"
              name="item_brand_input"
              v-model="form.brand"
              label="Contoh: ASUS, Mikrotik, Cisco, Daikin, Chitose"
              leading-icon="verified"
              autocomplete="off"
              :error-message="form.errors.brand"
            />
          </div>
        </div>

        <!-- 4. Jumlah & 6. Kondisi Barang -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 pt-1">
          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              Jumlah / Kuantitas Barang <span class="text-error">*</span>
            </label>
            <input
              type="number"
              min="1"
              max="10000"
              required
              v-model="form.quantity"
              class="w-full h-14 px-4 rounded-m3-xs border border-outline focus:border-primary focus:ring-0 bg-transparent text-sm font-bold text-surface-foreground"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-2">
              Kondisi Barang Saat Ini <span class="text-error">*</span>
            </label>
            <div class="flex items-center gap-4 h-14 px-4 rounded-m3-xs border border-outline bg-surface-container-lowest">
              <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="radio" value="Baik" v-model="form.condition" class="w-4 h-4 text-emerald-600" />
                <span class="text-xs font-bold text-emerald-700">1. Baik</span>
              </label>

              <label class="flex items-center gap-2 cursor-pointer select-none">
                <input type="radio" value="Rusak" v-model="form.condition" class="w-4 h-4 text-error" />
                <span class="text-xs font-bold text-error">2. Rusak</span>
              </label>
            </div>
          </div>
        </div>

        <!-- 7, 8, 9, 10. Klasifikasi & Penempatan -->
        <div class="pt-2 border-t border-outline-variant/30 space-y-3">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
            <div>
              <label class="block text-xs font-semibold text-surface-foreground mb-1">
                Kategori Barang
              </label>
              <M3SearchableSelect
                v-model="form.category_id"
                :options="categories"
                placeholder="-- Pilih Kategori --"
                leading-icon="category"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-surface-foreground mb-1">
                Fungsi Barang
              </label>
              <M3SearchableSelect
                v-model="form.function_id"
                :options="functions"
                placeholder="-- Pilih Fungsi --"
                leading-icon="construction"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-surface-foreground mb-1">
                Lokasi Gedung
              </label>
              <M3SearchableSelect
                v-model="form.building_id"
                :options="buildings"
                placeholder="-- Pilih Gedung --"
                leading-icon="domain"
              />
            </div>

            <div>
              <label class="block text-xs font-semibold text-surface-foreground mb-1">
                Ruangan / Laboratorium
              </label>
              <M3SearchableSelect
                v-model="form.room_id"
                :options="formRooms"
                placeholder="-- Pilih Ruangan --"
                leading-icon="meeting_room"
              />
            </div>
          </div>
        </div>

        <!-- 5. Foto Barang -->
        <div class="pt-2 border-t border-outline-variant/30">
          <label class="block text-xs font-bold text-surface-foreground mb-1">
            Foto Dokumentasi Barang (Auto-Kompresi &lt; 1MB)
          </label>
          <M3PhotoUpload
            v-model="form.photo"
            :existing-photo-url="selectedItem ? selectedItem.photo_path : ''"
          />
        </div>

        <!-- Notes -->
        <div>
          <label class="block text-xs font-semibold text-surface-foreground mb-1">
            Catatan Tambahan
          </label>
          <textarea
            v-model="form.notes"
            rows="2"
            placeholder="Contoh: Hibah industri tahun 2026..."
            class="w-full p-3 text-xs rounded-m3-xs border border-outline focus:border-primary focus:ring-0 bg-transparent text-surface-foreground placeholder:text-surface-on-variant"
          ></textarea>
        </div>
      </form>

      <template #footer="{ close }">
        <M3Button variant="text" size="medium" :disabled="form.processing" @click="close">
          Tutup / Batal
        </M3Button>

        <M3Button
          type="button"
          variant="filled"
          size="medium"
          :loading="form.processing"
          :disabled="form.processing || isActionDisabled"
          icon="save"
          @click="handleSubmit"
        >
          {{ isEditing ? 'Simpan Perubahan' : 'Catat Barang ke Inventaris' }}
        </M3Button>
      </template>
    </M3InteractiveModal>

    <!-- MODAL 2: DETAIL LENGKAP BARANG (EYE BUTTON) -->
    <M3InteractiveModal
      v-model="isDetailModalOpen"
      title="Detail Lengkap Barang Inventaris"
      subtitle="Informasi spesifikasi teknis, kondisi fisik, lokasi penempatan, dan audit kepemilikan data."
      max-width="max-w-3xl"
    >
      <div v-if="detailItem" class="space-y-5 py-2">
        
        <!-- Header Profile Barang (Photo & Quick Stats) -->
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4 p-4 rounded-m3-lg bg-surface-container-low border border-outline-variant/40">
          <div class="w-24 h-24 sm:w-28 sm:h-28 rounded-m3-md bg-surface-container overflow-hidden border border-outline-variant/40 flex items-center justify-center shrink-0 shadow-sm">
            <img
              v-if="detailItem.photo_path"
              :src="detailItem.photo_path"
              :alt="detailItem.name"
              class="w-full h-full object-cover"
            />
            <span v-else class="material-symbols-outlined text-[36px] text-surface-on-variant/50">
              image_not_supported
            </span>
          </div>

          <div class="space-y-1.5 flex-1 min-w-0">
            <div class="flex flex-wrap items-center gap-2">
              <span
                class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                :class="detailItem.condition === 'Baik' ? 'bg-emerald-100 text-emerald-800' : 'bg-red-100 text-red-800'"
              >
                Kondisi: {{ detailItem.condition }}
              </span>

              <span class="px-2.5 py-0.5 rounded-full bg-primary-container text-primary text-[10px] font-bold">
                {{ detailItem.quantity }} Unit Fisik
              </span>

              <span v-if="detailItem.category" class="px-2.5 py-0.5 rounded-full bg-surface-container text-surface-foreground text-[10px] font-medium">
                {{ detailItem.category.name }}
              </span>
            </div>

            <h3 class="text-base sm:text-lg font-black text-surface-foreground leading-snug">
              {{ detailItem.name }}
            </h3>

            <div class="text-xs text-surface-on-variant flex flex-wrap items-center gap-3">
              <span>Merk: <strong class="text-primary font-bold">{{ detailItem.brand || '-' }}</strong></span>
              <span>&bull;</span>
              <span>Serial Number: <strong class="font-mono text-surface-foreground">{{ detailItem.has_no_serial_number ? 'Tanpa SN' : (detailItem.serial_number || '-') }}</strong></span>
            </div>
          </div>
        </div>

        <!-- Spesifikasi & Lokasi Penempatan Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 text-xs">
          <!-- Kolom 1: Penempatan Ruangan & Gedung -->
          <div class="p-4 rounded-m3-md bg-surface-container-lowest border border-outline-variant/30 space-y-2.5 shadow-xs">
            <div class="font-bold text-surface-foreground flex items-center gap-1.5 pb-1 border-b border-outline-variant/20">
              <span class="material-symbols-outlined text-[18px] text-primary">apartment</span>
              <span>Lokasi Penempatan</span>
            </div>

            <div class="space-y-1.5">
              <div class="flex justify-between">
                <span class="text-surface-on-variant">Ruangan / Lab:</span>
                <span class="font-bold text-surface-foreground text-right">{{ detailItem.room ? detailItem.room.name : '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-surface-on-variant">Gedung:</span>
                <span class="font-medium text-surface-foreground text-right">{{ detailItem.building ? detailItem.building.name : '-' }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-surface-on-variant">Peruntukan Fungsi:</span>
                <span class="font-medium text-primary text-right">{{ detailItem.item_function ? detailItem.item_function.name : '-' }}</span>
              </div>
            </div>
          </div>

          <!-- Kolom 2: Rekam Jejak Audit Admin -->
          <div class="p-4 rounded-m3-md bg-surface-container-lowest border border-outline-variant/30 space-y-2.5 shadow-xs">
            <div class="font-bold text-surface-foreground flex items-center gap-1.5 pb-1 border-b border-outline-variant/20">
              <span class="material-symbols-outlined text-[18px] text-emerald-700">history_edu</span>
              <span>Rekam Jejak Admin & Kepemilikan</span>
            </div>

            <div class="space-y-1.5">
              <div class="flex justify-between">
                <span class="text-surface-on-variant">Admin Pencatat:</span>
                <span class="font-bold text-surface-foreground text-right">
                  {{ detailItem.creator ? detailItem.creator.name : 'Sistem' }}
                  <span v-if="detailItem.creator && detailItem.creator.nip" class="block text-[10px] text-surface-on-variant font-mono">
                    NIP: {{ detailItem.creator.nip }}
                  </span>
                </span>
              </div>
              <div class="flex justify-between">
                <span class="text-surface-on-variant">Waktu Input:</span>
                <span class="font-mono text-surface-on-variant text-right">{{ formatDate(detailItem.created_at) }}</span>
              </div>
              <div class="flex justify-between">
                <span class="text-surface-on-variant">Terakhir Diperbarui:</span>
                <span class="font-mono text-surface-on-variant text-right">{{ formatDate(detailItem.updated_at) }}</span>
              </div>
            </div>
          </div>
        </div>

        <!-- Catatan / Keterangan -->
        <div class="p-3.5 rounded-m3-md bg-surface-container-lowest border border-outline-variant/30 text-xs space-y-1">
          <span class="font-bold text-surface-foreground block">Catatan Tambahan:</span>
          <p class="text-surface-on-variant leading-relaxed italic">
            {{ detailItem.notes || 'Tidak ada catatan tambahan untuk barang ini.' }}
          </p>
        </div>

      </div>

      <template #footer="{ close }">
        <div class="flex items-center justify-between w-full">
          <!-- Left: Ownership Tag -->
          <div class="text-xs">
            <span
              v-if="detailItem && detailItem.created_by === $page.props.auth.user?.id"
              class="px-2.5 py-1 rounded-full bg-emerald-100 text-emerald-800 font-bold inline-flex items-center gap-1"
            >
              <span class="material-symbols-outlined text-[14px]">check_circle</span>
              <span>Data Milik Anda</span>
            </span>
            <span
              v-else
              class="text-surface-on-variant text-[11px]"
            >
              Diinput oleh staf: <strong>{{ detailItem?.creator ? detailItem.creator.name : 'Sistem' }}</strong>
            </span>
          </div>

          <div class="flex items-center gap-2">
            <!-- Quick Edit Button in Detail Modal if permitted -->
            <M3Button
              v-if="detailItem && canModifyItem(detailItem)"
              variant="tonal"
              size="medium"
              icon="edit"
              @click="() => { close(); openEditModal(detailItem); }"
            >
              Ubah Data
            </M3Button>

            <M3Button variant="filled" size="medium" @click="close">
              Tutup
            </M3Button>
          </div>
        </div>
      </template>
    </M3InteractiveModal>
  </AuthenticatedLayout>
</template>
