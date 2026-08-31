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
  buildings: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({ search: '' }),
  },
  nextCode: {
    type: String,
    default: '001',
  },
  nextName: {
    type: String,
    default: 'Gedung 1',
  },
});

const alert = useAlert();

// Search State
const search = ref(props.filters.search || '');
let searchTimeout = null;

const handleSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get(
      route('master-data.buildings.index'),
      { search: search.value },
      { preserveState: true, preserveScroll: true, replace: true }
    );
  }, 400);
};

// Modal & Form State
const isModalOpen = ref(false);
const isEditing = ref(false);
const selectedBuilding = ref(null);
const isBulkSeeding = ref(false);

const form = useForm({
  code: props.nextCode,
  name: props.nextName,
  total_floors: 1,
  description: '',
});

const openCreateModal = () => {
  isEditing.value = false;
  selectedBuilding.value = null;
  form.reset();
  form.clearErrors();
  form.code = props.nextCode;
  form.name = props.nextName;
  form.total_floors = 1;
  isModalOpen.value = true;
};

const openEditModal = (building) => {
  isEditing.value = true;
  selectedBuilding.value = building;
  form.clearErrors();
  form.code = building.code;
  form.name = building.name;
  form.total_floors = building.total_floors || 1;
  form.description = building.description || '';
  isModalOpen.value = true;
};

const handleSubmit = () => {
  if (isEditing.value && selectedBuilding.value) {
    form.put(route('master-data.buildings.update', selectedBuilding.value.id), {
      onSuccess: () => {
        isModalOpen.value = false;
        form.reset();
      },
    });
  } else {
    form.post(route('master-data.buildings.store'), {
      onSuccess: () => {
        isModalOpen.value = false;
        form.reset();
      },
    });
  }
};

const handleDelete = async (building) => {
  const isConfirmed = await alert.confirm({
    title: 'Hapus Data Gedung?',
    message: `Apakah Anda yakin ingin menghapus [${building.code}] "${building.name}"? Data ruangan dan fasilitas di gedung ini akan terpengaruh.`,
    type: 'error',
    confirmText: 'Ya, Hapus Gedung',
    cancelText: 'Batal',
  });

  if (isConfirmed) {
    router.delete(route('master-data.buildings.destroy', building.id), {
      preserveScroll: true,
    });
  }
};

const handleBulkSeed = async () => {
  const isConfirmed = await alert.confirm({
    title: 'Muat Standar Gedung 1 s/d 5?',
    message: 'Aksi ini akan menyelaraskan data standar Gedung 1 sampai Gedung 5 kampus SMK Telkom Lampung beserta peruntukannya.',
    type: 'question',
    confirmText: 'Ya, Muat Data',
    cancelText: 'Batal',
  });

  if (isConfirmed) {
    isBulkSeeding.value = true;
    router.post(
      route('master-data.buildings.bulk-seed'),
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
    route('master-data.buildings.index'),
    { page, search: search.value },
    { preserveState: true, preserveScroll: true }
  );
};
</script>

<template>
  <Head title="Data Gedung - Master Data" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      <!-- Header Banner -->
      <div class="bg-surface-container rounded-m3-xl p-6 sm:p-8 border border-outline-variant/40 relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 relative z-10 max-w-xl text-center md:text-left">
          <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-m3-full bg-primary-container text-primary-on-container text-xs font-semibold">
            <span class="material-symbols-outlined text-[16px]">domain</span>
            <span>Master Data &bull; Manajemen Fasilitas Kampus</span>
          </div>

          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-surface-foreground">
            Data Gedung Kampus
          </h1>

          <p class="text-sm text-surface-on-variant leading-relaxed">
            Kelola data gedung (diawali kode <strong>001</strong> | <strong>Gedung 1</strong> s/d <strong>Gedung 5</strong> dan seterusnya) sebagai basis pemetaan ruangan, lab kejuruan, dan inventaris sekolah.
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
            Muat Gedung 1 - 5
          </M3Button>

          <!-- Create New Building Button -->
          <M3Button variant="filled" icon="add" size="large" @click="openCreateModal">
            Tambah Gedung Baru
          </M3Button>
        </div>
      </div>

      <!-- Table & Filter Card -->
      <div class="bg-surface-container-lowest rounded-m3-xl p-6 border border-outline-variant/40 shadow-sm space-y-4">
        
        <!-- Filter & Search Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <!-- Search Box -->
          <div class="relative w-full sm:w-80">
            <span class="material-symbols-outlined text-[20px] text-surface-on-variant absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none">
              search
            </span>
            <input
              type="text"
              v-model="search"
              @input="handleSearch"
              placeholder="Cari kode (001), nama (Gedung 1), fungsi..."
              class="w-full h-11 pl-10 pr-4 rounded-m3-full border border-outline focus:border-primary focus:ring-0 bg-surface-container-low text-xs text-surface-foreground placeholder:text-surface-on-variant transition-colors"
            />
          </div>

          <div class="flex items-center gap-2 text-xs text-surface-on-variant">
            <span class="px-3 py-1 rounded-full bg-secondary-container text-secondary-on-container font-bold">
              Total: {{ buildings.total }} Gedung Kampus
            </span>
          </div>
        </div>

        <!-- Buildings Data Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="border-b border-outline-variant/40 text-surface-on-variant font-semibold uppercase tracking-wider bg-surface-container-low/50">
                <th class="py-3 px-3.5 w-32 rounded-l-m3-xs text-center">Kode Gedung</th>
                <th class="py-3 px-3.5">Nama Gedung</th>
                <th class="py-3 px-3.5 text-center">Jumlah Lantai</th>
                <th class="py-3 px-3.5">Deskripsi / Peruntukan Ruangan</th>
                <th class="py-3 px-3.5 text-right rounded-r-m3-xs">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
              <tr
                v-for="bld in buildings.data"
                :key="bld.id"
                class="hover:bg-surface-container-low transition-colors"
              >
                <!-- Code (001, 002, ...) -->
                <td class="py-3.5 px-3.5 text-center">
                  <span class="px-3 py-1 rounded-m3-sm bg-primary-container text-primary-on-container font-mono font-bold text-xs shadow-xs border border-primary/20">
                    {{ bld.code }}
                  </span>
                </td>

                <!-- Building Name (Gedung 1, Gedung 2, ...) -->
                <td class="py-3.5 px-3.5">
                  <div class="font-bold text-surface-foreground text-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-primary">domain</span>
                    <span>{{ bld.name }}</span>
                  </div>
                </td>

                <!-- Total Floors -->
                <td class="py-3.5 px-3.5 text-center">
                  <span class="px-2.5 py-1 rounded-full bg-surface-container-high text-surface-foreground font-semibold text-[11px]">
                    {{ bld.total_floors || 1 }} Lantai
                  </span>
                </td>

                <!-- Description -->
                <td class="py-3.5 px-3.5 max-w-md">
                  <div class="text-surface-on-variant leading-relaxed text-xs">
                    {{ bld.description || '-' }}
                  </div>
                </td>

                <!-- Actions (Edit & Delete) -->
                <td class="py-3.5 px-3.5 text-right">
                  <div class="flex items-center justify-end gap-1">
                    <!-- Edit Button -->
                    <button
                      type="button"
                      @click="openEditModal(bld)"
                      class="p-2 text-surface-on-variant hover:text-primary hover:bg-surface-variant/40 rounded-m3-full transition-colors cursor-pointer"
                      title="Ubah Data Gedung"
                    >
                      <span class="material-symbols-outlined text-[18px]">edit</span>
                    </button>

                    <!-- Delete Button -->
                    <button
                      type="button"
                      @click="handleDelete(bld)"
                      class="p-2 text-surface-on-variant hover:text-error hover:bg-error-container/40 rounded-m3-full transition-colors cursor-pointer"
                      title="Hapus Data Gedung"
                    >
                      <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Empty State -->
              <tr v-if="buildings.data.length === 0">
                <td colspan="5" class="py-12 text-center text-surface-on-variant">
                  <div class="flex flex-col items-center justify-center space-y-2">
                    <span class="material-symbols-outlined text-[48px] text-outline">domain</span>
                    <p class="text-sm font-semibold">Belum ada data gedung yang ditemukan.</p>
                    <p class="text-xs">Klik tombol "Muat Gedung 1 - 5" untuk memuat otomatis data standar.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- 10 Items Per Page Pagination Component -->
        <div class="pt-2 border-t border-outline-variant/30">
          <M3Pagination
            :current-page="buildings.current_page"
            :total-items="buildings.total"
            :per-page="buildings.per_page"
            @page-change="handlePageChange"
          />
        </div>

      </div>
    </div>

    <!-- Draggable, Maximizable, Persistent Modal Dialog -->
    <M3InteractiveModal
      v-model="isModalOpen"
      :title="isEditing ? 'Ubah Data Gedung' : 'Tambah Gedung Baru'"
      subtitle="Format kode dimulai dari 001 dan penamaan Gedung 1, Gedung 2, dst."
      max-width="max-w-lg"
    >
      <form @submit.prevent="handleSubmit" autocomplete="off" class="space-y-4 py-2">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <!-- 1. Kode Gedung (Starting from 001) -->
          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              Kode Gedung <span class="text-error">*</span>
            </label>
            <M3TextField
              id="bld_code"
              name="bld_code_input"
              v-model="form.code"
              label="Contoh: 001, 002..."
              leading-icon="tag"
              required
              autocomplete="off"
              :error-message="form.errors.code"
            />
          </div>

          <!-- Jumlah Lantai -->
          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              Jumlah Lantai
            </label>
            <input
              type="number"
              min="1"
              max="50"
              v-model="form.total_floors"
              class="w-full h-14 px-4 rounded-m3-xs border border-outline focus:border-primary focus:ring-0 bg-transparent text-sm text-surface-foreground"
            />
          </div>
        </div>

        <!-- 2. Nama Gedung (Gedung 1, Gedung 2, ...) -->
        <div>
          <label class="block text-xs font-semibold text-surface-foreground mb-1">
            Nama Gedung <span class="text-error">*</span>
          </label>
          <M3TextField
            id="bld_name"
            name="bld_name_input"
            v-model="form.name"
            label="Contoh: Gedung 1, Gedung 2, Gedung 3..."
            leading-icon="domain"
            required
            autocomplete="off"
            :error-message="form.errors.name"
          />
        </div>

        <!-- Deskripsi / Catatan Peruntukan Gedung -->
        <div>
          <label class="block text-xs font-semibold text-surface-foreground mb-1">
            Deskripsi / Peruntukan Gedung
          </label>
          <textarea
            v-model="form.description"
            rows="3"
            placeholder="Contoh: Gedung Laboratorium Komputer, Lab RPL & Lab Jaringan Komputer TKJ..."
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
          {{ isEditing ? 'Simpan Perubahan' : 'Simpan Gedung' }}
        </M3Button>
      </template>
    </M3InteractiveModal>
  </AuthenticatedLayout>
</template>
