<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import M3Button from '@/Components/M3Button.vue';
import M3TextField from '@/Components/M3TextField.vue';
import M3Pagination from '@/Components/M3Pagination.vue';
import M3InteractiveModal from '@/Components/M3InteractiveModal.vue';
import M3MapPicker from '@/Components/M3MapPicker.vue';
import { useToast } from '@/Composables/useToast';
import { useAlert } from '@/Composables/useAlert';
import { Head, router, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  schools: {
    type: Object,
    required: true,
  },
  filters: {
    type: Object,
    default: () => ({ search: '' }),
  },
});

const toast = useToast();
const alert = useAlert();

// Search & Filter State
const search = ref(props.filters.search || '');
let searchTimeout = null;

const handleSearch = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get(
      route('master-data.schools.index'),
      { search: search.value },
      { preserveState: true, preserveScroll: true, replace: true }
    );
  }, 400);
};

// Modal & Form State
const isModalOpen = ref(false);
const isEditing = ref(false);
const selectedSchool = ref(null);

const form = useForm({
  code: '',
  name: '',
  address: '',
  latitude: '-5.358241',
  longitude: '104.981242',
  principal_name: '',
  principal_nip: '',
  kaur_it_name: '',
  kaur_it_nip: '',
  is_active: false,
});

const openCreateModal = () => {
  isEditing.value = false;
  selectedSchool.value = null;
  form.reset();
  form.clearErrors();
  form.latitude = '-5.358241';
  form.longitude = '104.981242';
  form.is_active = props.schools.data.length === 0;
  isModalOpen.value = true;
};

const openEditModal = (school) => {
  isEditing.value = true;
  selectedSchool.value = school;
  form.clearErrors();
  form.code = school.code;
  form.name = school.name;
  form.address = school.address;
  form.latitude = school.latitude || '-5.358241';
  form.longitude = school.longitude || '104.981242';
  form.principal_name = school.principal_name;
  form.principal_nip = school.principal_nip || '';
  form.kaur_it_name = school.kaur_it_name || '';
  form.kaur_it_nip = school.kaur_it_nip || '';
  form.is_active = Boolean(school.is_active);
  isModalOpen.value = true;
};

const handleSubmit = () => {
  if (isEditing.value && selectedSchool.value) {
    form.put(route('master-data.schools.update', selectedSchool.value.id), {
      onSuccess: () => {
        isModalOpen.value = false;
        form.reset();
      },
    });
  } else {
    form.post(route('master-data.schools.store'), {
      onSuccess: () => {
        isModalOpen.value = false;
        form.reset();
      },
    });
  }
};

const handleActivate = async (school) => {
  if (school.is_active) {
    toast.info(`Lembaga '${school.name}' sudah berstatus aktif.`);
    return;
  }

  const isConfirmed = await alert.confirm({
    title: 'Aktifkan Lembaga Ini?',
    message: `Hanya diperbolehkan 1 lembaga berstatus aktif. Jika '${school.name}' diaktifkan, maka lembaga lain yang saat ini aktif akan otomatis dinonaktifkan.`,
    type: 'question',
    confirmText: 'Ya, Aktifkan',
    cancelText: 'Batal',
  });

  if (isConfirmed) {
    router.patch(
      route('master-data.schools.activate', school.id),
      {},
      {
        preserveScroll: true,
      }
    );
  }
};

const handleDelete = async (school) => {
  if (school.is_active) {
    toast.warning('Lembaga yang sedang aktif tidak dapat dihapus. Silakan aktifkan lembaga lain terlebih dahulu.');
    return;
  }

  const isConfirmed = await alert.confirm({
    title: 'Hapus Data Sekolah?',
    message: `Apakah Anda yakin ingin menghapus data '${school.name}'? Tindakan ini tidak dapat dibatalkan.`,
    type: 'error',
    confirmText: 'Ya, Hapus',
    cancelText: 'Batal',
  });

  if (isConfirmed) {
    router.delete(route('master-data.schools.destroy', school.id), {
      preserveScroll: true,
    });
  }
};

const handleLocationSelected = ({ address, lat, lng }) => {
  if (address) {
    form.address = address;
  }
  form.latitude = String(lat);
  form.longitude = String(lng);
};

const handlePageChange = (page) => {
  router.get(
    route('master-data.schools.index'),
    { page, search: search.value },
    { preserveState: true, preserveScroll: true }
  );
};
</script>

<template>
  <Head title="Data Sekolah - Master Data" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      
      <!-- Top Banner -->
      <div class="bg-surface-container rounded-m3-xl p-6 sm:p-8 border border-outline-variant/40 relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 relative z-10 max-w-xl text-center md:text-left">
          <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-m3-full bg-primary-container text-primary-on-container text-xs font-semibold">
            <span class="material-symbols-outlined text-[16px]">school</span>
            <span>Data Referensi &bull; Lembaga Pendidikan</span>
          </div>

          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-surface-foreground">
            Data Sekolah & Lembaga
          </h1>

          <p class="text-sm text-surface-on-variant leading-relaxed">
            Kelola data profil sekolah, Kepala Sekolah, Kaur IT (PIC Aset), alamat, dan koordinat GPS OpenStreetMap. <strong>Hanya ada 1 lembaga yang berstatus aktif</strong> sebagai identitas utama.
          </p>
        </div>

        <div class="relative z-10 flex items-center gap-3 shrink-0">
          <M3Button variant="filled" icon="add" size="large" @click="openCreateModal">
            Tambah Sekolah Baru
          </M3Button>
        </div>
      </div>

      <!-- Table Card -->
      <div class="bg-surface-container-lowest rounded-m3-xl p-6 border border-outline-variant/40 shadow-sm space-y-4">
        
        <!-- Search Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div class="relative w-full sm:w-80">
            <span class="material-symbols-outlined text-[20px] text-surface-on-variant absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none">
              search
            </span>
            <input
              type="text"
              v-model="search"
              @input="handleSearch"
              placeholder="Cari kode, nama, kepsek, kaur IT..."
              class="w-full h-11 pl-10 pr-4 rounded-m3-full border border-outline focus:border-primary focus:ring-0 bg-surface-container-low text-xs text-surface-foreground placeholder:text-surface-on-variant transition-colors"
            />
          </div>

          <div class="flex items-center gap-2 text-xs text-surface-on-variant">
            <span class="px-3 py-1 rounded-full bg-secondary-container text-secondary-on-container font-bold">
              Total: {{ schools.total }} Lembaga
            </span>
          </div>
        </div>

        <!-- Table Data -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="border-b border-outline-variant/40 text-surface-on-variant font-semibold uppercase tracking-wider bg-surface-container-low/50">
                <th class="py-3 px-3.5 w-32 rounded-l-m3-xs">Kode</th>
                <th class="py-3 px-3.5">Nama Lembaga & Alamat</th>
                <th class="py-3 px-3.5">Pimpinan & PIC Aset</th>
                <th class="py-3 px-3.5 text-center">Status Lembaga</th>
                <th class="py-3 px-3.5 text-right rounded-r-m3-xs">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
              <tr
                v-for="school in schools.data"
                :key="school.id"
                class="hover:bg-surface-container-low transition-colors"
                :class="school.is_active ? 'bg-primary-container/10 font-medium' : ''"
              >
                <!-- Code -->
                <td class="py-3.5 px-3.5 font-mono font-bold text-surface-foreground">
                  <span class="px-2.5 py-1 rounded-m3-xs bg-surface-container text-primary font-mono text-[11px] border border-outline-variant/40">
                    {{ school.code }}
                  </span>
                </td>

                <!-- Name & Address -->
                <td class="py-3.5 px-3.5 max-w-sm">
                  <div class="font-bold text-surface-foreground text-sm flex items-center gap-1.5">
                    <span>{{ school.name }}</span>
                    <span
                      v-if="school.is_active"
                      class="px-2 py-0.2 rounded-full text-[9px] font-bold bg-emerald-600 text-white uppercase tracking-wider"
                    >
                      Aktif
                    </span>
                  </div>
                  <div class="text-surface-on-variant text-[11px] mt-0.5 line-clamp-2" :title="school.address">
                    {{ school.address }}
                  </div>
                </td>

                <!-- Kepsek & Kaur IT -->
                <td class="py-3.5 px-3.5">
                  <div class="font-bold text-surface-foreground flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px] text-primary">account_circle</span>
                    <span>{{ school.principal_name }}</span>
                  </div>
                  <div class="text-[10px] text-surface-on-variant font-mono">
                    NIP: {{ school.principal_nip || '-' }}
                  </div>
                  <div class="text-[11px] text-primary font-semibold mt-1 flex items-center gap-1">
                    <span class="material-symbols-outlined text-[14px]">computer</span>
                    <span>Kaur IT: {{ school.kaur_it_name || '-' }}</span>
                  </div>
                </td>

                <!-- Status Aktif Toggle -->
                <td class="py-3.5 px-3.5 text-center">
                  <button
                    type="button"
                    @click="handleActivate(school)"
                    class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-m3-full text-xs font-semibold transition-all cursor-pointer shadow-sm"
                    :class="[
                      school.is_active
                        ? 'bg-emerald-600 text-white shadow-emerald-600/30'
                        : 'bg-surface-container-high text-surface-on-variant hover:bg-primary-container hover:text-primary-on-container'
                    ]"
                  >
                    <span class="material-symbols-outlined text-[16px]">
                      {{ school.is_active ? 'check_circle' : 'radio_button_unchecked' }}
                    </span>
                    <span>{{ school.is_active ? 'Aktif' : 'Aktifkan' }}</span>
                  </button>
                </td>

                <!-- Actions -->
                <td class="py-3.5 px-3.5 text-right">
                  <div class="flex items-center justify-end gap-1">
                    <button
                      type="button"
                      @click="openEditModal(school)"
                      class="p-2 text-surface-on-variant hover:text-primary hover:bg-surface-variant/40 rounded-m3-full transition-colors cursor-pointer"
                      title="Ubah Data Sekolah"
                    >
                      <span class="material-symbols-outlined text-[18px]">edit</span>
                    </button>

                    <button
                      type="button"
                      @click="handleDelete(school)"
                      class="p-2 text-surface-on-variant hover:text-error hover:bg-error-container/40 rounded-m3-full transition-colors cursor-pointer"
                      title="Hapus Data Sekolah"
                    >
                      <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                  </div>
                </td>
              </tr>

              <tr v-if="schools.data.length === 0">
                <td colspan="5" class="py-12 text-center text-surface-on-variant">
                  Belum ada data sekolah yang ditemukan.
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="pt-2 border-t border-outline-variant/30">
          <M3Pagination
            :current-page="schools.current_page"
            :total-items="schools.total"
            :per-page="schools.per_page"
            @page-change="handlePageChange"
          />
        </div>
      </div>
    </div>

    <!-- Modal Dialog -->
    <M3InteractiveModal
      v-model="isModalOpen"
      :title="isEditing ? 'Ubah Data Sekolah' : 'Tambah Data Sekolah Baru'"
      subtitle="Lengkapi data lembaga, Kepala Sekolah, Kaur IT, serta koordinat peta OpenStreetMap."
      max-width="max-w-3xl"
    >
      <form @submit.prevent="handleSubmit" autocomplete="off" class="space-y-4">
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              Kode Lembaga <span class="text-error">*</span>
            </label>
            <M3TextField
              id="school_code"
              name="school_code_input"
              v-model="form.code"
              label="Contoh: SMK-TELKOM-LPG"
              leading-icon="tag"
              required
              autocomplete="off"
              :error-message="form.errors.code"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              Nama Lembaga <span class="text-error">*</span>
            </label>
            <M3TextField
              id="school_name"
              name="school_name_input"
              v-model="form.name"
              label="Contoh: SMK Telkom Lampung"
              leading-icon="school"
              required
              autocomplete="off"
              :error-message="form.errors.name"
            />
          </div>
        </div>

        <!-- Kepala Sekolah & NIP -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              Nama Kepala Sekolah <span class="text-error">*</span>
            </label>
            <M3TextField
              id="principal_name"
              name="principal_name_input"
              v-model="form.principal_name"
              label="Contoh: Drs. H. Bambang Subagyo, M.Kom."
              leading-icon="person"
              required
              autocomplete="off"
              :error-message="form.errors.principal_name"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              NIP Kepala Sekolah
            </label>
            <M3TextField
              id="principal_nip"
              name="principal_nip_input"
              v-model="form.principal_nip"
              label="Contoh: 19750815 199903 1 002"
              leading-icon="badge"
              autocomplete="off"
              :error-message="form.errors.principal_nip"
            />
          </div>
        </div>

        <!-- Kaur IT / PIC Aset & NIP -->
        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              Nama Kaur IT (PIC Aset Sekolah)
            </label>
            <M3TextField
              id="kaur_it_name"
              name="kaur_it_name_input"
              v-model="form.kaur_it_name"
              label="Contoh: Rizky Pratama, S.Kom., M.T."
              leading-icon="computer"
              autocomplete="off"
              :error-message="form.errors.kaur_it_name"
            />
            <p class="text-[11px] text-surface-on-variant mt-0.5">
              * Pejabat penandatangan Berita Acara Finalisasi Aset.
            </p>
          </div>

          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              NIP Kaur IT (PIC Aset)
            </label>
            <M3TextField
              id="kaur_it_nip"
              name="kaur_it_nip_input"
              v-model="form.kaur_it_nip"
              label="Contoh: 19881210 201402 1 005"
              leading-icon="badge"
              autocomplete="off"
              :error-message="form.errors.kaur_it_nip"
            />
          </div>
        </div>

        <!-- Map Picker -->
        <div>
          <label class="block text-xs font-semibold text-surface-foreground mb-1">
            Alamat & Titik Koordinat Lembaga (OpenStreetMap) <span class="text-error">*</span>
          </label>
          <M3MapPicker
            :initial-lat="form.latitude"
            :initial-lng="form.longitude"
            :initial-address="form.address"
            @location-selected="handleLocationSelected"
          />
          <p v-if="form.errors.address" class="text-xs text-error mt-1">{{ form.errors.address }}</p>
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
          {{ isEditing ? 'Simpan Perubahan' : 'Simpan Sekolah' }}
        </M3Button>
      </template>
    </M3InteractiveModal>
  </AuthenticatedLayout>
</template>
