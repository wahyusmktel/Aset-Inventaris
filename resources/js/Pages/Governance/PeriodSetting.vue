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
  periods: {
    type: Object,
    required: true,
  },
  activePeriod: {
    type: Object,
    default: null,
  },
});

const alert = useAlert();

// Modal & Form State
const isModalOpen = ref(false);
const isEditing = ref(false);
const selectedPeriod = ref(null);
const isClearingInventory = ref(false);
const isClearingAll = ref(false);

const form = useForm({
  name: '',
  start_date: '',
  cutoff_date: '',
  is_active: true,
  notes: '',
});

const openCreateModal = () => {
  isEditing.value = false;
  selectedPeriod.value = null;
  form.reset();
  form.clearErrors();
  // Default dates: now and 14 days later
  const now = new Date();
  const cutoff = new Date(Date.now() + 14 * 24 * 60 * 60 * 1000);
  form.start_date = now.toISOString().slice(0, 16);
  form.cutoff_date = cutoff.toISOString().slice(0, 16);
  form.is_active = true;
  isModalOpen.value = true;
};

const openEditModal = (period) => {
  isEditing.value = true;
  selectedPeriod.value = period;
  form.clearErrors();
  form.name = period.name;
  form.start_date = period.start_date ? new Date(period.start_date).toISOString().slice(0, 16) : '';
  form.cutoff_date = period.cutoff_date ? new Date(period.cutoff_date).toISOString().slice(0, 16) : '';
  form.is_active = Boolean(period.is_active);
  form.notes = period.notes || '';
  isModalOpen.value = true;
};

const handleSubmit = () => {
  if (isEditing.value && selectedPeriod.value) {
    form.put(route('inventory-period.update', selectedPeriod.value.id), {
      onSuccess: () => {
        isModalOpen.value = false;
        form.reset();
      },
    });
  } else {
    form.post(route('inventory-period.store'), {
      onSuccess: () => {
        isModalOpen.value = false;
        form.reset();
      },
    });
  }
};

const handleDelete = async (period) => {
  const isConfirmed = await alert.confirm({
    title: 'Hapus Periode Pendataan?',
    message: `Apakah Anda yakin ingin menghapus "${period.name}"? Pengaturan batas cutoff pada periode ini akan dihapus.`,
    type: 'error',
    confirmText: 'Ya, Hapus',
    cancelText: 'Batal',
  });

  if (isConfirmed) {
    router.delete(route('inventory-period.destroy', period.id), {
      preserveScroll: true,
    });
  }
};

const handleClearInventory = async () => {
  const isConfirmed = await alert.confirm({
    title: 'Kosongkan Seluruh Data Inventaris?',
    message: 'Tindakan ini akan menghapus seluruh data barang inventaris yang telah didata, file foto aset, serta mereset berita acara finalisasi. Data TIDAK DAPAT dipulihkan!',
    type: 'error',
    confirmText: 'Ya, Kosongkan Data Inventaris',
    cancelText: 'Batal',
  });

  if (isConfirmed) {
    isClearingInventory.value = true;
    router.post(
      route('system.reset.inventory'),
      {},
      {
        preserveScroll: true,
        onFinish: () => {
          isClearingInventory.value = false;
        },
      }
    );
  }
};

const handleClearAllTransactional = async () => {
  const isConfirmed = await alert.confirm({
    title: 'Kosongkan Seluruh Riwayat Pendataan (Reset Penuh)?',
    message: 'PERINGATAN KRUSIAL: Tindakan ini akan mengosongkan seluruh data barang inventaris, pakta integritas yang ditandatangani seluruh anggota, serta seluruh berita acara finalisasi. Master data sekolah, ruangan, dan akun pengguna akan tetap aman.',
    type: 'error',
    confirmText: 'Ya, Reset Penuh Transaksional',
    cancelText: 'Batal',
  });

  if (isConfirmed) {
    isClearingAll.value = true;
    router.post(
      route('system.reset.all-transactional'),
      {},
      {
        preserveScroll: true,
        onFinish: () => {
          isClearingAll.value = false;
        },
      }
    );
  }
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
  <Head title="Pengaturan Periode & Batas Cut-Off - Super Admin" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      
      <!-- Banner Header -->
      <div class="bg-surface-container rounded-m3-xl p-6 sm:p-8 border border-outline-variant/40 flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 max-w-xl text-center md:text-left">
          <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-m3-full bg-primary-container text-primary text-xs font-semibold">
            <span class="material-symbols-outlined text-[16px]">alarm_on</span>
            <span>Super Admin &bull; Kontrol Batas Waktu & Tata Kelola</span>
          </div>

          <h1 class="text-2xl sm:text-3xl font-black tracking-tight text-surface-foreground">
            Pengaturan Periode & Cut-Off
          </h1>

          <p class="text-sm text-surface-on-variant leading-relaxed">
            Atur batas waktu (<em>deadline</em>) pendataan inventaris sekolah. Jika batas waktu telah tercapai, seluruh anggota tim tidak dapat menambah/mengedit data barang lagi.
          </p>
        </div>

        <div class="flex items-center gap-3 shrink-0">
          <M3Button variant="filled" icon="add" size="large" @click="openCreateModal">
            Tambah Periode Baru
          </M3Button>
        </div>
      </div>

      <!-- Active Period Spotlight Card -->
      <div
        v-if="activePeriod"
        class="bg-gradient-to-r from-slate-900 via-primary to-slate-900 rounded-m3-xl p-6 sm:p-7 text-white shadow-m3-elevation-2 relative overflow-hidden"
      >
        <div class="relative z-10 flex flex-col lg:flex-row items-start lg:items-center justify-between gap-6">
          <div class="space-y-2 max-w-xl">
            <div class="inline-flex items-center gap-2 px-3 py-0.5 rounded-full bg-emerald-500/20 text-emerald-300 border border-emerald-400/30 text-xs font-bold">
              <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
              <span>Periode Berjalan (Aktif)</span>
            </div>

            <h2 class="text-xl sm:text-2xl font-black tracking-tight">
              {{ activePeriod.name }}
            </h2>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs text-white/90 pt-1">
              <div class="flex items-center gap-2 bg-white/10 p-2.5 rounded-m3-sm backdrop-blur-sm">
                <span class="material-symbols-outlined text-[18px] text-emerald-300">play_circle</span>
                <div>
                  <span class="text-[10px] text-white/70 block">Tanggal Mulai:</span>
                  <span class="font-bold">{{ formatDate(activePeriod.start_date) }}</span>
                </div>
              </div>

              <div class="flex items-center gap-2 bg-white/10 p-2.5 rounded-m3-sm backdrop-blur-sm">
                <span class="material-symbols-outlined text-[18px] text-amber-300">schedule</span>
                <div>
                  <span class="text-[10px] text-white/70 block">Batas Akhir (Cutoff):</span>
                  <span class="font-bold">{{ formatDate(activePeriod.cutoff_date) }}</span>
                </div>
              </div>
            </div>
          </div>

          <div class="flex items-center gap-2">
            <M3Button
              variant="tonal"
              size="medium"
              icon="edit"
              @click="openEditModal(activePeriod)"
            >
              Ubah Pengaturan
            </M3Button>
          </div>
        </div>
      </div>

      <!-- Periods Table Card -->
      <div class="bg-surface-container-lowest rounded-m3-xl p-6 border border-outline-variant/40 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
          <div>
            <h2 class="text-base font-bold text-surface-foreground">Riwayat Periode Pendataan</h2>
            <p class="text-xs text-surface-on-variant">Daftar seluruh periode pendataan dan status keaktifan</p>
          </div>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="border-b border-outline-variant/40 text-surface-on-variant font-semibold uppercase tracking-wider bg-surface-container-low/50">
                <th class="py-3 px-3.5 rounded-l-m3-xs">Nama Periode</th>
                <th class="py-3 px-3.5">Tanggal Mulai</th>
                <th class="py-3 px-3.5">Batas Cutoff</th>
                <th class="py-3 px-3.5 text-center">Status</th>
                <th class="py-3 px-3.5 text-right rounded-r-m3-xs">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
              <tr v-for="period in periods.data" :key="period.id" class="hover:bg-surface-container-low transition-colors">
                <td class="py-3 px-3.5 font-bold text-surface-foreground">
                  <div>{{ period.name }}</div>
                  <div class="text-[10px] text-surface-on-variant font-normal">{{ period.notes || 'Tidak ada catatan' }}</div>
                </td>

                <td class="py-3 px-3.5 font-mono text-[11px] text-surface-foreground">
                  {{ formatDate(period.start_date) }}
                </td>

                <td class="py-3 px-3.5 font-mono text-[11px] font-bold text-primary">
                  {{ formatDate(period.cutoff_date) }}
                </td>

                <td class="py-3 px-3.5 text-center">
                  <span
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold inline-flex items-center gap-1 shadow-2xs"
                    :class="period.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-surface-container text-surface-on-variant'"
                  >
                    <span class="w-1.5 h-1.5 rounded-full" :class="period.is_active ? 'bg-emerald-600' : 'bg-surface-on-variant'"></span>
                    <span>{{ period.is_active ? 'Aktif' : 'Non-Aktif' }}</span>
                  </span>
                </td>

                <td class="py-3 px-3.5 text-right">
                  <div class="flex items-center justify-end gap-1">
                    <button
                      type="button"
                      @click="openEditModal(period)"
                      class="w-8 h-8 rounded-full flex items-center justify-center text-primary hover:bg-primary-container transition-colors cursor-pointer"
                      title="Ubah Periode"
                    >
                      <span class="material-symbols-outlined text-[18px]">edit</span>
                    </button>

                    <button
                      type="button"
                      @click="handleDelete(period)"
                      class="w-8 h-8 rounded-full flex items-center justify-center text-error hover:bg-red-100 transition-colors cursor-pointer"
                      title="Hapus Periode"
                    >
                      <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="periods.data.length === 0">
                <td colspan="5" class="py-8 text-center text-surface-on-variant">
                  Belum ada periode pendataan. Klik "Tambah Periode Baru" untuk memulai.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div v-if="periods.data.length > 0" class="pt-2">
          <M3Pagination
            :current-page="periods.current_page"
            :total-items="periods.total"
            :per-page="periods.per_page"
            @page-change="(p) => router.get(route('inventory-period.index'), { page: p })"
          />
        </div>
      </div>

      <!-- Super Admin Danger Zone / Kosongkan Data Section -->
      <div class="bg-red-50/50 rounded-m3-xl p-6 border-2 border-red-200/80 shadow-xs space-y-4">
        <div class="flex items-start gap-3 pb-3 border-b border-red-200/60">
          <div class="w-10 h-10 rounded-m3-md bg-red-100 text-error flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-[24px]">warning</span>
          </div>
          <div>
            <h3 class="text-sm font-black text-red-950 uppercase tracking-wide">
              Zona Pengosongan & Reset Data Sistem (Super Admin Only)
            </h3>
            <p class="text-xs text-red-800 leading-relaxed mt-0.5">
              Gunakan fungsi di bawah ini dengan sangat hati-hati untuk mengosongkan data pendataan sebelum memulai periode inventarisasi baru.
            </p>
          </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-4 pt-1">
          <!-- Option 1: Kosongkan Data Inventaris -->
          <div class="bg-white p-4 rounded-m3-lg border border-red-200 flex flex-col justify-between space-y-3">
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-error">inventory_2</span>
                <h4 class="text-xs font-bold text-surface-foreground">Kosongkan Data Inventaris Barang</h4>
              </div>
              <p class="text-[11px] text-surface-on-variant leading-relaxed">
                Menghapus seluruh rekaman barang inventaris fisik, file foto yang diunggah, serta mereset berita acara finalisasi. Akun pengguna dan pakta integritas tetap tersimpan.
              </p>
            </div>

            <button
              type="button"
              @click="handleClearInventory"
              :disabled="isClearingInventory"
              class="h-10 px-4 rounded-m3-md bg-red-600 hover:bg-red-700 active:bg-red-800 text-white font-bold text-xs inline-flex items-center justify-center gap-2 transition-all cursor-pointer disabled:opacity-50"
            >
              <span class="material-symbols-outlined text-[18px]">delete_sweep</span>
              <span>{{ isClearingInventory ? 'Sedang Mengosongkan...' : 'Kosongkan Barang Inventaris' }}</span>
            </button>
          </div>

          <!-- Option 2: Reset Penuh Seluruh Transaksional -->
          <div class="bg-white p-4 rounded-m3-lg border border-red-200 flex flex-col justify-between space-y-3">
            <div class="space-y-1">
              <div class="flex items-center gap-2">
                <span class="material-symbols-outlined text-[18px] text-error">restart_alt</span>
                <h4 class="text-xs font-bold text-surface-foreground">Reset Penuh Riwayat Pendataan</h4>
              </div>
              <p class="text-[11px] text-surface-on-variant leading-relaxed">
                Mengosongkan seluruh data barang, foto aset, berita acara, serta seluruh persetujuan pakta integritas anggota. Data referensi sekolah & master data tetap aman.
              </p>
            </div>

            <button
              type="button"
              @click="handleClearAllTransactional"
              :disabled="isClearingAll"
              class="h-10 px-4 rounded-m3-md bg-slate-900 hover:bg-slate-800 active:bg-black text-white font-bold text-xs inline-flex items-center justify-center gap-2 transition-all cursor-pointer disabled:opacity-50"
            >
              <span class="material-symbols-outlined text-[18px]">history_toggle_off</span>
              <span>{{ isClearingAll ? 'Mereset Sistem...' : 'Reset Penuh Transaksional' }}</span>
            </button>
          </div>
        </div>
      </div>

    </div>

    <!-- Modal Form -->
    <M3InteractiveModal
      v-model="isModalOpen"
      :title="isEditing ? 'Ubah Periode Pendataan' : 'Buat Periode Pendataan Baru'"
      subtitle="Tentukan tanggal mulai dan batas akhir (cutoff) pendataan aset."
      max-width="max-w-lg"
    >
      <form @submit.prevent="handleSubmit" autocomplete="off" class="space-y-4 py-2">
        <div>
          <label class="block text-xs font-semibold text-surface-foreground mb-1">
            Nama Periode Pendataan <span class="text-error">*</span>
          </label>
          <M3TextField
            id="period_name"
            name="period_name_input"
            v-model="form.name"
            label="Contoh: Pendataan Inventaris Semester Ganjil 2026/2027"
            leading-icon="event"
            required
            autocomplete="off"
            :error-message="form.errors.name"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              Tanggal & Waktu Mulai <span class="text-error">*</span>
            </label>
            <input
              type="datetime-local"
              v-model="form.start_date"
              required
              class="w-full h-14 px-3 text-xs rounded-m3-xs border border-outline focus:border-primary focus:ring-0 bg-transparent text-surface-foreground"
            />
            <p v-if="form.errors.start_date" class="text-xs text-error mt-1">{{ form.errors.start_date }}</p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              Batas Waktu Cut-off <span class="text-error">*</span>
            </label>
            <input
              type="datetime-local"
              v-model="form.cutoff_date"
              required
              class="w-full h-14 px-3 text-xs rounded-m3-xs border border-outline focus:border-primary focus:ring-0 bg-transparent text-surface-foreground font-bold"
            />
            <p v-if="form.errors.cutoff_date" class="text-xs text-error mt-1">{{ form.errors.cutoff_date }}</p>
          </div>
        </div>

        <div class="flex items-center gap-2 pt-1">
          <input
            type="checkbox"
            id="period_active_checkbox"
            v-model="form.is_active"
            class="w-4 h-4 rounded text-primary focus:ring-primary/40 cursor-pointer"
          />
          <label for="period_active_checkbox" class="text-xs font-semibold text-surface-foreground cursor-pointer select-none">
            Jadikan sebagai <strong>Periode Aktif Utama</strong>
          </label>
        </div>

        <div>
          <label class="block text-xs font-semibold text-surface-foreground mb-1">
            Catatan / Instruksi Periode
          </label>
          <textarea
            v-model="form.notes"
            rows="2"
            placeholder="Keterangan pengadaan atau instruksi khusus untuk tim surveyor..."
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
          :disabled="form.processing"
          icon="save"
          @click="handleSubmit"
        >
          {{ isEditing ? 'Simpan Perubahan' : 'Simpan Periode' }}
        </M3Button>
      </template>
    </M3InteractiveModal>
  </AuthenticatedLayout>
</template>
