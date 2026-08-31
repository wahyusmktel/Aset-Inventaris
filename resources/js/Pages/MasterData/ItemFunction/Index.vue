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
  functions: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({ search: '' }),
  },
  nextCode: {
    type: String,
    default: '01',
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
      route('master-data.item-functions.index'),
      { search: search.value },
      { preserveState: true, preserveScroll: true, replace: true }
    );
  }, 400);
};

// Modal & Form State
const isModalOpen = ref(false);
const isEditing = ref(false);
const selectedFunction = ref(null);
const isBulkSeeding = ref(false);

const form = useForm({
  code: props.nextCode,
  name: '',
  description: '',
});

const openCreateModal = () => {
  isEditing.value = false;
  selectedFunction.value = null;
  form.reset();
  form.clearErrors();
  form.code = props.nextCode;
  isModalOpen.value = true;
};

const openEditModal = (func) => {
  isEditing.value = true;
  selectedFunction.value = func;
  form.clearErrors();
  form.code = func.code;
  form.name = func.name;
  form.description = func.description || '';
  isModalOpen.value = true;
};

const handleSubmit = () => {
  if (isEditing.value && selectedFunction.value) {
    form.put(route('master-data.item-functions.update', selectedFunction.value.id), {
      onSuccess: () => {
        isModalOpen.value = false;
        form.reset();
      },
    });
  } else {
    form.post(route('master-data.item-functions.store'), {
      onSuccess: () => {
        isModalOpen.value = false;
        form.reset();
      },
    });
  }
};

const handleDelete = async (func) => {
  const isConfirmed = await alert.confirm({
    title: 'Hapus Fungsi Barang?',
    message: `Apakah Anda yakin ingin menghapus [${func.code}] "${func.name}"? Pengelompokan operasional aset terkait fungsi ini akan terpengaruh.`,
    type: 'error',
    confirmText: 'Ya, Hapus Fungsi',
    cancelText: 'Batal',
  });

  if (isConfirmed) {
    router.delete(route('master-data.item-functions.destroy', func.id), {
      preserveScroll: true,
    });
  }
};

const handleBulkSeed = async () => {
  const isConfirmed = await alert.confirm({
    title: 'Muat Standar Fungsi Barang?',
    message: 'Aksi ini akan menyelaraskan 14 daftar fungsi barang standar sekolah (format 2 digit: 01 s/d 14).',
    type: 'question',
    confirmText: 'Ya, Muat Data',
    cancelText: 'Batal',
  });

  if (isConfirmed) {
    isBulkSeeding.value = true;
    router.post(
      route('master-data.item-functions.bulk-seed'),
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
    route('master-data.item-functions.index'),
    { page, search: search.value },
    { preserveState: true, preserveScroll: true }
  );
};
</script>

<template>
  <Head title="Fungsi Barang - Master Data" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      <!-- Header Banner -->
      <div class="bg-surface-container rounded-m3-xl p-6 sm:p-8 border border-outline-variant/40 relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 relative z-10 max-w-xl text-center md:text-left">
          <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-m3-full bg-primary-container text-primary-on-container text-xs font-semibold">
            <span class="material-symbols-outlined text-[16px]">construction</span>
            <span>Master Data &bull; Klasifikasi Peruntukan Aset</span>
          </div>

          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-surface-foreground">
            Fungsi Barang & Aset Sekolah
          </h1>

          <p class="text-sm text-surface-on-variant leading-relaxed">
            Klasifikasi operasional barang berdasarkan kegunaannya dengan format kode 2 digit (<strong>01</strong>, <strong>02</strong>, dst) untuk pelaporan inventaris yang akurat.
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
            Muat Standar Fungsi
          </M3Button>

          <!-- Create New Function Button -->
          <M3Button variant="filled" icon="add" size="large" @click="openCreateModal">
            Tambah Fungsi Baru
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
              placeholder="Cari kode (01), nama fungsi (Praktikum)..."
              class="w-full h-11 pl-10 pr-4 rounded-m3-full border border-outline focus:border-primary focus:ring-0 bg-surface-container-low text-xs text-surface-foreground placeholder:text-surface-on-variant transition-colors"
            />
          </div>

          <div class="flex items-center gap-2 text-xs text-surface-on-variant">
            <span class="px-3 py-1 rounded-full bg-secondary-container text-secondary-on-container font-bold">
              Total: {{ functions.total }} Fungsi Terdaftar
            </span>
          </div>
        </div>

        <!-- Functions Data Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="border-b border-outline-variant/40 text-surface-on-variant font-semibold uppercase tracking-wider bg-surface-container-low/50">
                <th class="py-3 px-3.5 w-32 rounded-l-m3-xs text-center">Kode Fungsi</th>
                <th class="py-3 px-3.5">Nama Fungsi Barang</th>
                <th class="py-3 px-3.5">Deskripsi Peruntukan Operasional</th>
                <th class="py-3 px-3.5 text-right rounded-r-m3-xs">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
              <tr
                v-for="func in functions.data"
                :key="func.id"
                class="hover:bg-surface-container-low transition-colors"
              >
                <!-- Code (01, 02, ...) -->
                <td class="py-3.5 px-3.5 text-center">
                  <span class="px-3 py-1 rounded-m3-sm bg-primary-container text-primary-on-container font-mono font-bold text-xs shadow-xs border border-primary/20">
                    {{ func.code }}
                  </span>
                </td>

                <!-- Function Name -->
                <td class="py-3.5 px-3.5">
                  <div class="font-bold text-surface-foreground text-sm flex items-center gap-2">
                    <span class="material-symbols-outlined text-[18px] text-primary">construction</span>
                    <span>{{ func.name }}</span>
                  </div>
                </td>

                <!-- Description -->
                <td class="py-3.5 px-3.5 max-w-md">
                  <div class="text-surface-on-variant leading-relaxed text-xs">
                    {{ func.description || '-' }}
                  </div>
                </td>

                <!-- Actions (Edit & Delete) -->
                <td class="py-3.5 px-3.5 text-right">
                  <div class="flex items-center justify-end gap-1">
                    <!-- Edit Button -->
                    <button
                      type="button"
                      @click="openEditModal(func)"
                      class="p-2 text-surface-on-variant hover:text-primary hover:bg-surface-variant/40 rounded-m3-full transition-colors cursor-pointer"
                      title="Ubah Fungsi"
                    >
                      <span class="material-symbols-outlined text-[18px]">edit</span>
                    </button>

                    <!-- Delete Button -->
                    <button
                      type="button"
                      @click="handleDelete(func)"
                      class="p-2 text-surface-on-variant hover:text-error hover:bg-error-container/40 rounded-m3-full transition-colors cursor-pointer"
                      title="Hapus Fungsi"
                    >
                      <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                  </div>
                </td>
              </tr>

              <!-- Empty State -->
              <tr v-if="functions.data.length === 0">
                <td colspan="4" class="py-12 text-center text-surface-on-variant">
                  <div class="flex flex-col items-center justify-center space-y-2">
                    <span class="material-symbols-outlined text-[48px] text-outline">construction</span>
                    <p class="text-sm font-semibold">Belum ada fungsi barang yang ditemukan.</p>
                    <p class="text-xs">Klik tombol "Muat Standar Fungsi" untuk memuat 14 fungsi barang standar sekolah.</p>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <!-- 10 Items Per Page Pagination Component -->
        <div class="pt-2 border-t border-outline-variant/30">
          <M3Pagination
            :current-page="functions.current_page"
            :total-items="functions.total"
            :per-page="functions.per_page"
            @page-change="handlePageChange"
          />
        </div>

      </div>
    </div>

    <!-- Draggable, Maximizable, Persistent Modal Dialog -->
    <M3InteractiveModal
      v-model="isModalOpen"
      :title="isEditing ? 'Ubah Fungsi Barang' : 'Tambah Fungsi Barang Baru'"
      subtitle="Format kode 2 digit angka dimulai dari 01, 02, dst."
      max-width="max-w-lg"
    >
      <form @submit.prevent="handleSubmit" autocomplete="off" class="space-y-4 py-2">
        <!-- 1. Kode Fungsi (Starting from 01) -->
        <div>
          <label class="block text-xs font-semibold text-surface-foreground mb-1">
            Kode Fungsi Barang (2 Digit) <span class="text-error">*</span>
          </label>
          <M3TextField
            id="func_code"
            name="func_code_input"
            v-model="form.code"
            label="Contoh: 01, 02, 03..."
            leading-icon="tag"
            required
            autocomplete="off"
            :error-message="form.errors.code"
          />
          <p class="text-[11px] text-surface-on-variant mt-0.5">
            * Format 2 digit angka (01, 02, dst).
          </p>
        </div>

        <!-- 2. Nama Fungsi Barang -->
        <div>
          <label class="block text-xs font-semibold text-surface-foreground mb-1">
            Nama Fungsi Barang <span class="text-error">*</span>
          </label>
          <M3TextField
            id="func_name"
            name="func_name_input"
            v-model="form.name"
            label="Contoh: Peralatan Praktikum Siswa, Media Pembelajaran"
            leading-icon="construction"
            required
            autocomplete="off"
            :error-message="form.errors.name"
          />
        </div>

        <!-- Deskripsi Peruntukan -->
        <div>
          <label class="block text-xs font-semibold text-surface-foreground mb-1">
            Deskripsi / Peruntukan Operasional
          </label>
          <textarea
            v-model="form.description"
            rows="3"
            placeholder="Contoh: Digunakan untuk praktikum pemrograman dan jaringan di laboratorium..."
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
          {{ isEditing ? 'Simpan Perubahan' : 'Simpan Fungsi' }}
        </M3Button>
      </template>
    </M3InteractiveModal>
  </AuthenticatedLayout>
</template>
