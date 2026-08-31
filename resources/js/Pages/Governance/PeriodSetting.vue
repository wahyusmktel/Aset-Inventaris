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
  <Head title="Pengaturan Rentang Waktu & Cutoff - Super Admin" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      
      <!-- Top Banner -->
      <div class="bg-surface-container rounded-m3-xl p-6 sm:p-8 border border-outline-variant/40 relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 relative z-10 max-w-xl text-center md:text-left">
          <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-m3-full bg-primary-container text-primary-on-container text-xs font-semibold">
            <span class="material-symbols-outlined text-[16px]">timer</span>
            <span>Super Admin &bull; Kontrol Jadwal & Batas Cutoff</span>
          </div>

          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-surface-foreground">
            Rentang Waktu & Batas Cutoff Pendataan
          </h1>

          <p class="text-sm text-surface-on-variant leading-relaxed">
            Atur batas waktu pendataan inventaris sekolah. Saat batas <strong>Cutoff</strong> tercapai, anggota tim pendataan otomatis dikunci dari penambahan/pengubahan barang dan diarahkan ke proses <strong>Finalisasi Data</strong>.
          </p>
        </div>

        <div class="relative z-10 flex items-center gap-3 shrink-0">
          <M3Button variant="filled" icon="add_alarm" size="large" @click="openCreateModal">
            Buat Periode Baru
          </M3Button>
        </div>
      </div>

      <!-- Active Period Summary Card -->
      <div v-if="activePeriod" class="p-5 rounded-m3-xl bg-primary/10 border border-primary/20 flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <div class="flex items-center gap-3.5">
          <div class="w-12 h-12 rounded-m3-md bg-primary text-primary-foreground flex items-center justify-center shrink-0 shadow-sm">
            <span class="material-symbols-outlined text-[26px]">alarm_on</span>
          </div>
          <div>
            <span class="px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold uppercase">
              Periode Aktif Saat Ini
            </span>
            <h3 class="text-base font-bold text-surface-foreground mt-0.5">{{ activePeriod.name }}</h3>
            <p class="text-xs text-surface-on-variant mt-0.5">
              Dimulai: <strong>{{ formatDate(activePeriod.start_date) }}</strong> &bull; 
              Batas Cutoff: <strong class="text-primary font-bold">{{ formatDate(activePeriod.cutoff_date) }}</strong>
            </p>
          </div>
        </div>

        <M3Button variant="tonal" size="medium" icon="edit" @click="openEditModal(activePeriod)">
          Ubah Batas Waktu
        </M3Button>
      </div>

      <!-- Periods Table Card -->
      <div class="bg-surface-container-lowest rounded-m3-xl p-6 border border-outline-variant/40 shadow-sm space-y-4">
        <div class="flex items-center justify-between pb-3 border-b border-outline-variant/30">
          <h3 class="text-sm font-bold text-surface-foreground">Riwayat Seluruh Periode Pendataan</h3>
          <span class="text-xs text-surface-on-variant">Total: {{ periods.total }} Periode</span>
        </div>

        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="border-b border-outline-variant/40 text-surface-on-variant font-semibold uppercase tracking-wider bg-surface-container-low/50">
                <th class="py-3 px-3.5 rounded-l-m3-xs">Nama Periode</th>
                <th class="py-3 px-3.5">Waktu Mulai</th>
                <th class="py-3 px-3.5">Batas Akhir (Cutoff)</th>
                <th class="py-3 px-3.5 text-center">Status</th>
                <th class="py-3 px-3.5">Catatan</th>
                <th class="py-3 px-3.5 text-right rounded-r-m3-xs">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
              <tr v-for="period in periods.data" :key="period.id" class="hover:bg-surface-container-low transition-colors">
                <td class="py-3.5 px-3.5 font-bold text-surface-foreground">
                  {{ period.name }}
                </td>
                <td class="py-3.5 px-3.5 text-surface-on-variant font-mono">
                  {{ formatDate(period.start_date) }}
                </td>
                <td class="py-3.5 px-3.5 font-mono">
                  <span class="px-2 py-0.5 rounded bg-surface-container font-bold text-primary">
                    {{ formatDate(period.cutoff_date) }}
                  </span>
                </td>
                <td class="py-3.5 px-3.5 text-center">
                  <span
                    class="px-2.5 py-0.5 rounded-full text-[10px] font-bold"
                    :class="period.is_active ? 'bg-emerald-100 text-emerald-800' : 'bg-surface-container text-surface-on-variant'"
                  >
                    {{ period.is_active ? 'Aktif' : 'Non-Aktif' }}
                  </span>
                </td>
                <td class="py-3.5 px-3.5 text-surface-on-variant max-w-xs truncate">
                  {{ period.notes || '-' }}
                </td>
                <td class="py-3.5 px-3.5 text-right">
                  <div class="flex items-center justify-end gap-1">
                    <button
                      type="button"
                      @click="openEditModal(period)"
                      class="p-2 text-surface-on-variant hover:text-primary hover:bg-surface-variant/40 rounded-m3-full transition-colors cursor-pointer"
                      title="Ubah Periode"
                    >
                      <span class="material-symbols-outlined text-[18px]">edit</span>
                    </button>

                    <button
                      type="button"
                      @click="handleDelete(period)"
                      class="p-2 text-surface-on-variant hover:text-error hover:bg-error-container/40 rounded-m3-full transition-colors cursor-pointer"
                      title="Hapus Periode"
                    >
                      <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="periods.data.length === 0">
                <td colspan="6" class="py-12 text-center text-surface-on-variant">
                  Belum ada periode pendataan yang dibuat.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="pt-2 border-t border-outline-variant/30">
          <M3Pagination
            :current-page="periods.current_page"
            :total-items="periods.total"
            :per-page="periods.per_page"
            @page-change="(p) => router.get(route('inventory-period.index'), { page: p })"
          />
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
