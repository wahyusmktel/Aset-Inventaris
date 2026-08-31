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
  users: {
    type: Object,
    required: true,
  },
  statistics: {
    type: Object,
    default: () => ({ total_users: 0, total_super_admins: 0, total_anggota: 0 }),
  },
  filters: {
    type: Object,
    default: () => ({ search: '', role: '' }),
  },
});

const alert = useAlert();

// Search & Filter State
const search = ref(props.filters.search || '');
const filterRole = ref(props.filters.role || '');
let searchTimeout = null;

const applyFilters = () => {
  if (searchTimeout) clearTimeout(searchTimeout);
  searchTimeout = setTimeout(() => {
    router.get(
      route('user-management.users.index'),
      { search: search.value, role: filterRole.value },
      { preserveState: true, preserveScroll: true, replace: true }
    );
  }, 400);
};

// Modal & Form State
const isModalOpen = ref(false);
const isEditing = ref(false);
const selectedUser = ref(null);

const form = useForm({
  name: '',
  email: '',
  password: '',
  role: 'anggota',
  nip: '',
  phone: '',
  is_active: true,
});

const openCreateModal = () => {
  isEditing.value = false;
  selectedUser.value = null;
  form.reset();
  form.clearErrors();
  form.role = 'anggota';
  form.is_active = true;
  isModalOpen.value = true;
};

const openEditModal = (user) => {
  isEditing.value = true;
  selectedUser.value = user;
  form.clearErrors();
  form.name = user.name;
  form.email = user.email;
  form.password = '';
  form.role = user.role;
  form.nip = user.nip || '';
  form.phone = user.phone || '';
  form.is_active = Boolean(user.is_active);
  isModalOpen.value = true;
};

const handleSubmit = () => {
  if (isEditing.value && selectedUser.value) {
    form.put(route('user-management.users.update', selectedUser.value.id), {
      onSuccess: () => {
        isModalOpen.value = false;
        form.reset();
      },
    });
  } else {
    form.post(route('user-management.users.store'), {
      onSuccess: () => {
        isModalOpen.value = false;
        form.reset();
      },
    });
  }
};

const handleAssignRole = async (user, newRole) => {
  const isConfirmed = await alert.confirm({
    title: 'Ubah Role Pengguna?',
    message: `Apakah Anda yakin ingin mengubah hak akses pengguna "${user.name}" menjadi ${newRole === 'super_admin' ? 'Super Admin' : 'Anggota Tim'}?`,
    type: 'question',
    confirmText: 'Ya, Ubah Role',
    cancelText: 'Batal',
  });

  if (isConfirmed) {
    router.patch(
      route('user-management.users.assign-role', user.id),
      { role: newRole },
      { preserveScroll: true }
    );
  }
};

const handleDelete = async (user) => {
  const isConfirmed = await alert.confirm({
    title: 'Hapus Akun Pengguna?',
    message: `Apakah Anda yakin ingin menghapus pengguna "${user.name}" (${user.email})? Tindakan ini tidak dapat dibatalkan.`,
    type: 'error',
    confirmText: 'Ya, Hapus Akun',
    cancelText: 'Batal',
  });

  if (isConfirmed) {
    router.delete(route('user-management.users.destroy', user.id), {
      preserveScroll: true,
    });
  }
};
</script>

<template>
  <Head title="Manajemen Pengguna & Role - Super Admin" />

  <AuthenticatedLayout>
    <div class="space-y-6">
      
      <!-- Header Banner -->
      <div class="bg-surface-container rounded-m3-xl p-6 sm:p-8 border border-outline-variant/40 relative overflow-hidden flex flex-col md:flex-row items-center justify-between gap-6">
        <div class="space-y-2 relative z-10 max-w-xl text-center md:text-left">
          <div class="inline-flex items-center gap-2 px-3.5 py-1 rounded-m3-full bg-primary-container text-primary-on-container text-xs font-semibold">
            <span class="material-symbols-outlined text-[16px]">manage_accounts</span>
            <span>Super Admin &bull; Akses & Manajemen Akun</span>
          </div>

          <h1 class="text-2xl sm:text-3xl font-extrabold tracking-tight text-surface-foreground">
            Manajemen Pengguna & Assign Role
          </h1>

          <p class="text-sm text-surface-on-variant leading-relaxed">
            Kelola akun petugas, tetapkan role (<strong>Super Admin</strong> atau <strong>Anggota Tim</strong>), dan pantau status tanda tangan Pakta Integritas serta Finalisasi data inventaris.
          </p>
        </div>

        <div class="relative z-10 flex items-center gap-3 shrink-0">
          <M3Button variant="filled" icon="person_add" size="large" @click="openCreateModal">
            Tambah Pengguna Baru
          </M3Button>
        </div>
      </div>

      <!-- Statistics KPI Cards -->
      <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="bg-surface-container-lowest p-4 sm:p-5 rounded-m3-lg border border-outline-variant/40 shadow-xs flex items-center justify-between">
          <div>
            <div class="text-[11px] font-medium text-surface-on-variant">Total Pengguna Terdaftar</div>
            <div class="text-xl sm:text-2xl font-black text-surface-foreground mt-0.5">{{ statistics.total_users }} Akun</div>
          </div>
          <div class="w-10 h-10 rounded-m3-md bg-primary-container text-primary flex items-center justify-center">
            <span class="material-symbols-outlined text-[22px]">group</span>
          </div>
        </div>

        <div class="bg-surface-container-lowest p-4 sm:p-5 rounded-m3-lg border border-outline-variant/40 shadow-xs flex items-center justify-between">
          <div>
            <div class="text-[11px] font-medium text-surface-on-variant">Super Administrator</div>
            <div class="text-xl sm:text-2xl font-black text-primary mt-0.5">{{ statistics.total_super_admins }} Akun</div>
          </div>
          <div class="w-10 h-10 rounded-m3-md bg-primary-container text-primary flex items-center justify-center">
            <span class="material-symbols-outlined text-[22px]">shield_person</span>
          </div>
        </div>

        <div class="bg-surface-container-lowest p-4 sm:p-5 rounded-m3-lg border border-outline-variant/40 shadow-xs flex items-center justify-between">
          <div>
            <div class="text-[11px] font-medium text-surface-on-variant">Anggota Tim Pendata</div>
            <div class="text-xl sm:text-2xl font-black text-slate-800 mt-0.5">{{ statistics.total_anggota }} Petugas</div>
          </div>
          <div class="w-10 h-10 rounded-m3-md bg-slate-100 text-slate-800 flex items-center justify-center">
            <span class="material-symbols-outlined text-[22px]">badge</span>
          </div>
        </div>
      </div>

      <!-- Users Table Card -->
      <div class="bg-surface-container-lowest rounded-m3-xl p-6 border border-outline-variant/40 shadow-sm space-y-4">
        
        <!-- Filter Toolbar -->
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3">
          <div class="relative w-full sm:w-80">
            <span class="material-symbols-outlined text-[20px] text-surface-on-variant absolute left-3.5 top-1/2 -translate-y-1/2 pointer-events-none">
              search
            </span>
            <input
              type="text"
              v-model="search"
              @input="applyFilters"
              placeholder="Cari nama, email, NIP..."
              class="w-full h-11 pl-10 pr-4 rounded-m3-full border border-outline focus:border-primary focus:ring-0 bg-surface-container-low text-xs text-surface-foreground placeholder:text-surface-on-variant"
            />
          </div>

          <select
            v-model="filterRole"
            @change="applyFilters"
            class="h-11 px-4 rounded-m3-full border border-outline focus:border-primary focus:ring-0 bg-surface-container-low text-xs text-surface-foreground cursor-pointer"
          >
            <option value="">Semua Role Pengguna</option>
            <option value="super_admin">Super Admin</option>
            <option value="anggota">Anggota Tim</option>
          </select>
        </div>

        <!-- Data Table -->
        <div class="overflow-x-auto">
          <table class="w-full text-left text-xs">
            <thead>
              <tr class="border-b border-outline-variant/40 text-surface-on-variant font-semibold uppercase tracking-wider bg-surface-container-low/50">
                <th class="py-3 px-3.5 rounded-l-m3-xs">Pengguna</th>
                <th class="py-3 px-3.5">NIP & Telepon</th>
                <th class="py-3 px-3.5 text-center">Role / Hak Akses</th>
                <th class="py-3 px-3.5 text-center">Pakta Integritas</th>
                <th class="py-3 px-3.5 text-center">Finalisasi</th>
                <th class="py-3 px-3.5 text-right rounded-r-m3-xs">Aksi</th>
              </tr>
            </thead>
            <tbody class="divide-y divide-outline-variant/20">
              <tr v-for="user in users.data" :key="user.id" class="hover:bg-surface-container-low transition-colors">
                <!-- User Info -->
                <td class="py-3 px-3.5">
                  <div class="flex items-center gap-3">
                    <div class="w-8 h-8 rounded-full bg-primary text-primary-foreground font-bold flex items-center justify-center text-xs shadow-xs shrink-0">
                      {{ user.name.charAt(0).toUpperCase() }}
                    </div>
                    <div>
                      <div class="font-bold text-surface-foreground text-xs">{{ user.name }}</div>
                      <div class="text-[10px] text-surface-on-variant font-mono">{{ user.email }}</div>
                    </div>
                  </div>
                </td>

                <!-- NIP & Phone -->
                <td class="py-3 px-3.5 font-mono text-[11px]">
                  <div>{{ user.nip || '-' }}</div>
                  <div class="text-[10px] text-surface-on-variant">{{ user.phone || '-' }}</div>
                </td>

                <!-- Role Badge & Quick Assign -->
                <td class="py-3 px-3.5 text-center">
                  <span
                    class="px-2.5 py-1 rounded-full text-[10px] font-bold inline-flex items-center gap-1 shadow-2xs cursor-pointer border"
                    :class="user.role === 'super_admin' ? 'bg-primary-container text-primary border-primary/20' : 'bg-slate-100 text-slate-800 border-slate-200'"
                    @click="handleAssignRole(user, user.role === 'super_admin' ? 'anggota' : 'super_admin')"
                    title="Klik untuk mengubah role pengguna"
                  >
                    <span class="material-symbols-outlined text-[13px]">{{ user.role === 'super_admin' ? 'shield' : 'person' }}</span>
                    <span>{{ user.role === 'super_admin' ? 'Super Admin' : 'Anggota Tim' }}</span>
                    <span class="material-symbols-outlined text-[12px] opacity-70">sync_alt</span>
                  </span>
                </td>

                <!-- Pakta Integritas Status -->
                <td class="py-3 px-3.5 text-center">
                  <template v-if="user.role === 'super_admin'">
                    <span class="text-surface-on-variant/60 italic text-[10px]">Super Admin</span>
                  </template>
                  <template v-else-if="user.integrity_pact && user.integrity_pact.is_agreed">
                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                      ✓ Ditandatangani
                    </span>
                  </template>
                  <template v-else>
                    <span class="px-2 py-0.5 rounded-full bg-amber-100 text-amber-800 text-[10px] font-bold">
                      Belum TTD
                    </span>
                  </template>
                </td>

                <!-- Finalisasi Status -->
                <td class="py-3 px-3.5 text-center">
                  <template v-if="user.role === 'super_admin'">
                    <span class="text-surface-on-variant/60 italic text-[10px]">-</span>
                  </template>
                  <template v-else-if="user.data_finalization && user.data_finalization.is_finalized">
                    <span class="px-2 py-0.5 rounded-full bg-emerald-100 text-emerald-800 text-[10px] font-bold">
                      ✓ Selesai Final
                    </span>
                  </template>
                  <template v-else>
                    <span class="px-2 py-0.5 rounded-full bg-slate-100 text-slate-700 text-[10px] font-bold">
                      Sedang Pendataan
                    </span>
                  </template>
                </td>

                <!-- Actions -->
                <td class="py-3 px-3.5 text-right">
                  <div class="flex items-center justify-end gap-1">
                    <button
                      type="button"
                      @click="openEditModal(user)"
                      class="p-2 text-surface-on-variant hover:text-primary hover:bg-surface-variant/40 rounded-m3-full transition-colors cursor-pointer"
                      title="Ubah Pengguna"
                    >
                      <span class="material-symbols-outlined text-[18px]">edit</span>
                    </button>

                    <button
                      v-if="user.id !== $page.props.auth.user.id"
                      type="button"
                      @click="handleDelete(user)"
                      class="p-2 text-surface-on-variant hover:text-error hover:bg-error-container/40 rounded-m3-full transition-colors cursor-pointer"
                      title="Hapus Pengguna"
                    >
                      <span class="material-symbols-outlined text-[18px]">delete</span>
                    </button>
                  </div>
                </td>
              </tr>
            </tbody>
          </table>
        </div>

        <div class="pt-2 border-t border-outline-variant/30">
          <M3Pagination
            :current-page="users.current_page"
            :total-items="users.total"
            :per-page="users.per_page"
            @page-change="(p) => router.get(route('user-management.users.index'), { page: p, search, role: filterRole })"
          />
        </div>
      </div>
    </div>

    <!-- Modal Form -->
    <M3InteractiveModal
      v-model="isModalOpen"
      :title="isEditing ? 'Ubah Akun Pengguna' : 'Tambah Pengguna Baru'"
      subtitle="Kelola identitas, kata sandi, dan hak akses peran (Role)."
      max-width="max-w-lg"
    >
      <form @submit.prevent="handleSubmit" autocomplete="off" class="space-y-4 py-2">
        <div>
          <label class="block text-xs font-semibold text-surface-foreground mb-1">
            Nama Lengkap <span class="text-error">*</span>
          </label>
          <M3TextField
            id="user_name"
            name="user_name_input"
            v-model="form.name"
            label="Contoh: Ahmad Fauzi, S.Kom."
            leading-icon="person"
            required
            autocomplete="off"
            :error-message="form.errors.name"
          />
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              Email Pengguna <span class="text-error">*</span>
            </label>
            <M3TextField
              id="user_email"
              name="user_email_input"
              type="email"
              v-model="form.email"
              label="email@smktelkom.sch.id"
              leading-icon="mail"
              required
              autocomplete="off"
              :error-message="form.errors.email"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              {{ isEditing ? 'Ubah Kata Sandi (Opsional)' : 'Kata Sandi *' }}
            </label>
            <M3TextField
              id="user_password"
              name="user_password_input"
              type="password"
              v-model="form.password"
              label="Minimal 8 karakter"
              leading-icon="lock"
              :required="!isEditing"
              autocomplete="new-password"
              :error-message="form.errors.password"
            />
          </div>
        </div>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              NIP / ID Petugas
            </label>
            <M3TextField
              id="user_nip"
              name="user_nip_input"
              v-model="form.nip"
              label="Contoh: 19950712 202102 1 008"
              leading-icon="badge"
              autocomplete="off"
              :error-message="form.errors.nip"
            />
          </div>

          <div>
            <label class="block text-xs font-semibold text-surface-foreground mb-1">
              No. Telepon / WhatsApp
            </label>
            <M3TextField
              id="user_phone"
              name="user_phone_input"
              v-model="form.phone"
              label="0812xxxxxxxx"
              leading-icon="call"
              autocomplete="off"
              :error-message="form.errors.phone"
            />
          </div>
        </div>

        <!-- Role Selector -->
        <div>
          <label class="block text-xs font-semibold text-surface-foreground mb-1.5">
            Hak Akses / Peran (Role) <span class="text-error">*</span>
          </label>
          <div class="grid grid-cols-2 gap-3">
            <label
              class="p-3 rounded-m3-xs border flex items-center gap-2.5 cursor-pointer transition-colors"
              :class="form.role === 'super_admin' ? 'border-primary bg-primary/10 text-primary font-bold' : 'border-outline text-surface-foreground'"
            >
              <input type="radio" value="super_admin" v-model="form.role" class="w-4 h-4 text-primary" />
              <div class="text-xs leading-tight">
                <span class="block">Super Admin</span>
                <span class="text-[10px] font-normal text-surface-on-variant">Akses penuh semua fitur</span>
              </div>
            </label>

            <label
              class="p-3 rounded-m3-xs border flex items-center gap-2.5 cursor-pointer transition-colors"
              :class="form.role === 'anggota' ? 'border-emerald-600 bg-emerald-50 text-emerald-800 font-bold' : 'border-outline text-surface-foreground'"
            >
              <input type="radio" value="anggota" v-model="form.role" class="w-4 h-4 text-emerald-600" />
              <div class="text-xs leading-tight">
                <span class="block">Anggota Tim</span>
                <span class="text-[10px] font-normal text-surface-on-variant">Pendataan & inventaris</span>
              </div>
            </label>
          </div>
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
          {{ isEditing ? 'Simpan Perubahan' : 'Simpan Pengguna' }}
        </M3Button>
      </template>
    </M3InteractiveModal>
  </AuthenticatedLayout>
</template>
