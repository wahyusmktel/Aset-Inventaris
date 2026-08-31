<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import M3Button from '@/Components/M3Button.vue';
import M3TextField from '@/Components/M3TextField.vue';
import M3Pagination from '@/Components/M3Pagination.vue';
import M3InteractiveModal from '@/Components/M3InteractiveModal.vue';
import { useAlert } from '@/Composables/useAlert';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  rooms: {
    type: Object,
    required: true,
  },
  buildings: {
    type: Array,
    default: () => [],
  },
  filters: {
    type: Object,
    default: () => ({ search: '', building_id: '' }),
  },
  nextCode: {
    type: String,
    default: '0001',
  },
});

const alert = useAlert();

// Search & Filter State
const search = ref(props.filters.search || '');
const selectedBuildingFilter = ref(props.filters.building_id || '');
let searchTimeout = null;

const handleFilterChange = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get(
      route('master-data.rooms.index'),
      {
        search: search.value,
        building_id: selectedBuildingFilter.value,
      },
      { preserveState: true, preserveScroll: true, replace: true }
    );
  }, 400);
};

// Modal & Form State
const isModalOpen = ref(false);
const isEditing = ref(false);
const selectedRoom = ref(null);
const isBulkSeeding = ref(false);

const form = useForm({
  code: props.nextCode,
  name: '',
  building_id: '',
  floor: 1,
  capacity: 36,
  type: 'Laboratorium Komputer',
  description: '',
});

const quickRoomSuggestions = [
  'Ruang Kelas X',
  'Ruang Kelas XI',
  'Ruang Kelas XII',
  'Ruang Guru',
  'Lab Software (RPL)',
  'Lab Fiber Optic (TJAT)',
  'Lab Animasi',
  'Lab Hardware',
  'Lab Jaringan (TKJ)',
  'Lab Informatika',
  'Lab IPAS',
  'Lab Bahasa',
  'Ruang Kepala Sekolah',
  'Ruang Tata Usaha (TU)',
  'Ruang Server & NOC',
  'Perpustakaan Digital',
  'Ruang Bimbingan Konseling (BK)',
  'Ruang OSIS & MPK',
  'Ruang UKS',
  'Ruang Robotika & IoT',
  'Studio Podcast & Broadcast',
  'Musholla As-Salam',
  'Aula Serbaguna',
];

const openCreateModal = () => {
  isEditing.value = false;
  selectedRoom.value = null;
  form.reset();
  form.clearErrors();
  form.code = props.nextCode;
  form.building_id = props.buildings.length > 0 ? props.buildings[0].id : '';
  form.floor = 1;
  form.capacity = 36;
  form.type = 'Laboratorium Komputer';
  isModalOpen.value = true;
};

const openEditModal = (room) => {
  isEditing.value = true;
  selectedRoom.value = room;
  form.clearErrors();
  form.code = room.code;
  form.name = room.name;
  form.building_id = room.building_id || '';
  form.floor = room.floor || 1;
  form.capacity = room.capacity || 36;
  form.type = room.type || 'Laboratorium Komputer';
  form.description = room.description || '';
  isModalOpen.value = true;
};

const selectRoomSuggestion = (name) => {
  form.name = name;
  if (name.includes('Lab')) {
    form.type = 'Laboratorium';
  } else if (name.includes('Kelas')) {
    form.type = 'Ruang Teori / Kelas';
  } else if (name.includes('Guru') || name.includes('Kepala') || name.includes('TU')) {
    form.type = 'Kantor / Staf';
  } else {
    form.type = 'Fasilitas Umum';
  }
};

const handleSubmit = () => {
  if (isEditing.value && selectedRoom.value) {
    form.put(route('master-data.rooms.update', selectedRoom.value.id), {
      onSuccess: () => {
        isModalOpen.value = false;
        form.reset();
      },
    });
  } else {
    form.post(route('master-data.rooms.store'), {
      onSuccess: () => {
        isModalOpen.value = false;
        form.reset();
      },
    });
  }
};

const handleDelete = async (room) => {
  const isConfirmed = await alert.confirm({
    title: 'Hapus Data Ruangan?',
    message: `Apakah Anda yakin ingin menghapus [${room.code}] "${room.name}"? Data penempatan barang dan inventaris pada ruangan ini akan terpengaruh.`,
    type: 'error',
    confirmText: 'Ya, Hapus Ruangan',
    cancelText: 'Batal',
  });

  if (isConfirmed) {
    router.delete(route('master-data.rooms.destroy', room.id), {
      preserveScroll: true,
    });
  }
};

const handleBulkSeed = async () => {
  const isConfirmed = await alert.confirm({
    title: 'Muat Data Ruangan Lengkap?',
    message: 'Aksi ini akan menyelaraskan 27 daftar ruangan standar sekolah (Lab Software, Fiber Optik, Animasi, Hardware, Jaringan, Ruang Kelas, Ruang Guru, OSIS, dll) yang terdistribusi di Gedung 1 s/d Gedung 5.',
    type: 'question',
    confirmText: 'Ya, Muat Data',
    cancelText: 'Batal',
  });

  if (isConfirmed) {
    isBulkSeeding.value = true;
    router.post(
      route('master-data.rooms.bulk-seed'),
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

const handlePageChange = (page) => {
  router.get(
    route('master-data.rooms.index'),
    {
      page,
      search: search.value,
      building_id: selectedBuildingFilter.value,
    },
    { preserveState: true, preserveScroll: true }
  );
};
</script>

<template>
  <Head title="Data Ruangan - Master Data" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      <!-- Header Banner -->
      <div class="bg-surface-container rounded-m3-xl p-6 sm:p-8 border border-outline-variant/40 relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 relative z-10 max-w-xl text-center md:text-left">
          <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-m3-full bg-primary-container text-primary-on-container text-xs font-semibold">
            <span class="material-symbols-outlined text-[16px]">meeting_room</span>
            <span>Master Data &bull; Manajemen Ruangan & Lab</span>
          </div>

          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-surface-foreground">
            Data Ruangan & Laboratorium
          </h1>

          <p class="text-sm text-surface-on-variant leading-relaxed">
            Format kode diawali <strong>0001</strong> untuk pemetaan laboratorium kejuruan (<strong>RPL</strong>, <strong>TJAT</strong>, <strong>Animasi</strong>, <strong>TKJ</strong>), ruang kelas teori, kantor, dan fasilitas kesiswaan.
          </p>
        </div>

        <div class="relative z-10 flex flex-wrap items-center gap-3 shrink-0">
          <!-- Bulk Seed Action Button -->
          <M3Button
            variant="tonal"
            icon="auto_fix_high"
            size="medium"
            :loading="isBulkSeeding"
            :disabled="isBulkSeeding"
            @click="handleBulkSeed"
          >
            Muat Data Ruangan Lengkap
          </M3Button>

          <!-- Create New Room Button -->
          <M3Button variant="filled" icon="add" size="large" @click="openCreateModal">
            Tambah Ruangan Baru
          </M3Button>
        </div>
      </div>

      <!-- Table & Filter Card -->
      <div class="bg-surface-container-lowest rounded-m3-xl p-6 border border-outline-variant/40 shadow-sm space-y-4">
        
        <!-- Filter & Search Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div class="flex flex-wrap items-center gap-2.5 flex-1">
            <!-- Search Box -->
            <div class="relative w-full sm:w-72">
              <span class="material-symbols-outlined text-[20px] text-surface-on-variant absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none">
                search
              </span>
              <input
                type="text"
                v-model="search"
                @input="handleFilterChange"
                placeholder="Cari kode (0001), nama ruangan, tipe..."
                class="w-full h-11 pl-10 pr-4 rounded-m3-full border border-outline focus:border-primary focus:ring-0 bg-surface-container-low text-xs text-surface-foreground placeholder:text-surface-on-variant transition-colors"
              />
            </div>

            <!-- Filter by Building -->
            <select
              v-model="selectedBuildingFilter"
              @change="handleFilterChange"
              class="h-11 px-4 rounded-m3-full border border-outline focus:border-primary focus:ring-0 bg-surface-container-low text-xs text-surface-foreground cursor-pointer"
            >
              <option value="">Semua Gedung</option>
              <option v-for="bld in buildings" :key="bld.id" :value="bld.id">
                {{ bld.name }} ({{ bld.code }})
              </option>
            </select>
          </div>

          <div class="flex items-center gap-2 text-xs text-surface-on-variant">
            <span class="px-3 py-1 rounded-full bg-secondary-container text-secondary-on-container font-bold">
              Total: {{ rooms.total }} Ruangan Terdaftar
            </span>
          </div>
        </div>

        <!-- Rooms Data Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="border-b border-outline-variant/40 text-surface-on-variant font-semibold uppercase tracking-wider bg-surface-container-low/50">
                <th class="py-3 px-3.5 w-32 rounded-l-m3-xs text-center">Kode Ruang</th>
                <th class="py-3 px-3.5">Nama Ruangan & Tipe</th>
                <th class="py-3 px-3.5">Lokasi Gedung & Lantai</th>
                <th class="py-3 px-3.5 text-center">Kapasitas</th>
                <th class="py-3 px-3.5">Deskripsi Fasilitas</th>
                <th class="py-3 px-3.5 text-right rounded-r-m3-xs">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
              <tr
                v-for="room in rooms.data"
                :key="room.id"
                class="hover:bg-surface-container-low transition-colors"
              >
                <!-- Code (0001, 0002, ...) -->
                <td class="py-3.5 px-3.5 text-center">
                  <span class="px-3 py-1 rounded-m3-sm bg-primary-container text-primary-on-container font-mono font-bold text-xs shadow-xs border border-primary/20">
                    {{ room.code }}
                  </span>
                </td>

                <!-- Room Name & Type -->
                <td class="py-3.5 px-3.5">
                  <div class="font-bold text-surface-foreground text-sm flex items-center gap-2">
                    <span>{{ room.name }}</span>
                  </div>
                  <div v-if="room.type" class="text-[10px] text-surface-on-variant font-medium mt-0.5 inline-flex items-center gap-1">
                    <span class="w-1.5 h-1.5 rounded-full bg-primary"></span>
                    <span>{{ room.type }}</span>
                  </div>
                </td>

                <!-- Building & Floor -->
                <td class="py-3.5 px-3.5">
                  <div class="font-semibold text-surface-foreground flex items-center gap-1.5">
                    <span class="material-symbols-outlined text-[16px] text-primary">domain</span>
                    <span>{{ room.building ? room.building.name : 'Belum Ditentukan' }}</span>
                  </div>
                  <div class="text-[11px] text-surface-on-variant mt-0.5">
                    Lantai {{ room.floor || 1 }}
                  </div>
                </td>

                <!-- Capacity -->
                <td class="py-3.5 px-3.5 text-center">
                  <span class="px-2.5 py-1 rounded-full bg-surface-container-high text-surface-foreground font-semibold text-[11px]">
                    {{ room.capacity ? `${room.capacity} Orang` : '-' }}
                  </span>
                </td>

                <!-- Description -->
                <td class="py-3.5 px-3.5 max-w-xs">
                  <div class="line-clamp-2 text-surface-on-variant leading-relaxed text-xs">
                    {{ room.description || '-' }}
                  </div>
                </td>

                <!-- Actions (Edit & Delete) -->
                <td class="py-3.5 px-3.5 text-right">
                  <div class="flex items-center justify-end gap-1">
                    <!-- Edit Button -->
                    <button
                      type="button"
                      @click="openEditModal(room)"
                      class="p-2 text-surface-on-variant hover:text-primary hover:bg-surface-variant/40 rounded-m3-full transition-colors cursor-pointer"
                      title="Ubah Data Ruangan"
                    >
                      <span class="material-symbols-outlined text-[18px]">edit</span>
                    </button>

                    <!-- Delete Button -->
                    <button
                      type="button"
                      @click="handleDelete(room)"
                      class="p-2 text-surface-on-variant hover:text-error hover:bg-error-container/40 rounded-m3-full transition-colors cursor-pointer"
                      title="Hapus Data Ruangan"
                    >
                      <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Empty State -->
              <tr v-if="rooms.data.length === 0">
                <td colspan="6" class="py-12 text-center text-surface-on-variant">
                  <div class="flex flex-col items-center justify-center space-y-2">
                    <span class="material-symbols-outlined text-[48px] text-outline">meeting_room</span>
                    <p class="text-sm font-semibold">Belum ada data ruangan yang ditemukan.</p>
                    <p class="text-xs">Klik tombol "Muat Data Ruangan Lengkap" untuk memuat 27 data ruangan standar.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- 10 Items Per Page Pagination Component -->
        <div class="pt-2 border-t border-outline-variant/30">
          <M3Pagination
            :current-page="rooms.current_page"
            :total-items="rooms.total"
            :per-page="rooms.per_page"
            @page-change="handlePageChange"
          />
        </div>

      </div>
    </div>

    <!-- Draggable, Maximizable, Persistent Modal Dialog -->
    <M3InteractiveModal
      v-model="isModalOpen"
      :title="isEditing ? 'Ubah Data Ruangan' : 'Tambah Ruangan Baru'"
      subtitle="Format kode diawali 4 digit angka (0001, 0002, dst)."
      max-width="max-w-2xl"
    >
      <form @submit.prevent="handleSubmit" autocomplete="off" class="space-y-4 py-2">
        
        <!-- Quick Preset Badges -->
        <div>
          <label class="block text-[11px] font-semibold text-surface-on-variant mb-1.5">
            Pilihan Cepat Nama Ruangan:
          </label>
          <div class="flex flex-wrap gap-1.5 max-h-24 overflow-y-auto p-1.5 bg-surface-container-low rounded-m3-xs border border-outline-variant/40">
            <button
              v-for="preset in quickRoomSuggestions"
              :key="preset"
              type="button"
              @click="selectRoomSuggestion(preset)"
              class="px-2.5 py-1 rounded-full text-[11px] font-medium bg-surface-container hover:bg-primary-container hover:text-primary transition-colors cursor-pointer border border-outline-variant/30"
            >
              {{ preset }}
            </button>
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- 1. Kode Ruang (Starting from 0001) -->
          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              Kode Ruangan <span class="text-error">*</span>
            </label>
            <M3TextField
              id="rm_code"
              name="rm_code_input"
              v-model="form.code"
              label="Contoh: 0001, 0002..."
              leading-icon="tag"
              required
              autocomplete="off"
              :error-message="form.errors.code"
            />
          </div>

          <!-- 2. Nama Ruangan -->
          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              Nama Ruangan <span class="text-error">*</span>
            </label>
            <M3TextField
              id="rm_name"
              name="rm_name_input"
              v-model="form.name"
              label="Contoh: Lab Software (RPL), Ruang Kelas"
              leading-icon="meeting_room"
              required
              autocomplete="off"
              :error-message="form.errors.name"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
          <!-- Gedung Selection -->
          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              Gedung Lokasi
            </label>
            <select
              v-model="form.building_id"
              class="w-full h-14 px-4 rounded-m3-xs border border-outline focus:border-primary focus:ring-0 bg-transparent text-xs text-surface-foreground"
            >
              <option value="">-- Pilih Gedung --</option>
              <option v-for="bld in buildings" :key="bld.id" :value="bld.id">
                {{ bld.name }} ({{ bld.code }})
              </option>
            </select>
          </div>

          <!-- Lantai -->
          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              Lantai
            </label>
            <input
              type="number"
              min="1"
              max="50"
              v-model="form.floor"
              placeholder="Contoh: 1"
              class="w-full h-14 px-4 rounded-m3-xs border border-outline focus:border-primary focus:ring-0 bg-transparent text-sm text-surface-foreground"
            />
          </div>

          <!-- Kapasitas -->
          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              Kapasitas (Orang)
            </label>
            <input
              type="number"
              min="1"
              max="1000"
              v-model="form.capacity"
              placeholder="Contoh: 36"
              class="w-full h-14 px-4 rounded-m3-xs border border-outline focus:border-primary focus:ring-0 bg-transparent text-sm text-surface-foreground"
            />
          </div>
        </div>

        <!-- Tipe / Kategori Ruangan -->
        <div>
          <label class="block text-xs font-semibold text-surface-foreground mb-1">
            Kategori / Tipe Ruangan
          </label>
          <input
            type="text"
            v-model="form.type"
            placeholder="Contoh: Laboratorium Komputer, Ruang Teori, Kantor, Fasilitas"
            class="w-full h-12 px-4 rounded-m3-xs border border-outline focus:border-primary focus:ring-0 bg-transparent text-xs text-surface-foreground"
          />
        </div>

        <!-- Deskripsi / Fasilitas Ruangan -->
        <div>
          <label class="block text-xs font-semibold text-surface-foreground mb-1">
            Deskripsi / Fasilitas Ruangan
          </label>
          <textarea
            v-model="form.description"
            rows="3"
            placeholder="Contoh: Dilengkapi 36 PC Core i7, Smartboard, AC 2 PK, Switch Cisco 24-Port..."
            class="w-full p-3 text-xs rounded-m3-xs border border-outline focus:border-primary focus:ring-0 bg-transparent text-surface-foreground placeholder:text-surface-on-variant"
          ></textarea>
          <p v-if="form.errors.description" class="text-xs text-error mt-1">
            {{ form.errors.description }}
          </p>
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
          :disabled="form.processing"
          icon="save"
          @click="handleSubmit"
        >
          {{ isEditing ? 'Simpan Perubahan' : 'Simpan Ruangan' }}
        </M3Button>
      </template>
    </M3InteractiveModal>
  </AuthenticatedLayout>
</template>
