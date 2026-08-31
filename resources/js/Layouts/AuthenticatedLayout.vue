<script setup>
import { ref, computed, watch } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import M3ToastContainer from '@/Components/M3ToastContainer.vue';
import M3SweetAlert from '@/Components/M3SweetAlert.vue';
import { useToast } from '@/Composables/useToast';
import { Link, usePage } from '@inertiajs/vue3';

const showingSidebar = ref(false);
const isReferenceOpen = ref(true); // Data Referensi
const isInventoryOpen = ref(true); // Inventaris
const page = usePage();
const toast = useToast();

const isSuperAdmin = computed(() => page.props.auth.user?.role === 'super_admin');
const isAnggota = computed(() => page.props.auth.user?.role === 'anggota');

// Auto-trigger toasts on server flash messages
watch(
  () => page.props.flash,
  (flash) => {
    if (flash?.success) toast.success(flash.success);
    if (flash?.error) toast.error(flash.error);
    if (flash?.warning) toast.warning(flash.warning);
    if (flash?.info) toast.info(flash.info);
  },
  { deep: true, immediate: true }
);

const getBreadcrumbTitle = () => {
  if (route().current('inventory.items.*')) return 'Inventaris Barang';
  if (route().current('integrity-pact.*')) return 'Pakta Integritas';
  if (route().current('data-finalization.*')) return 'Finalisasi Data & Berita Acara';
  if (route().current('user-management.*')) return 'Manajemen Pengguna & Role';
  if (route().current('inventory-period.*')) return 'Pengaturan Periode & Cutoff';
  if (route().current('master-data.schools.*')) return 'Data Sekolah';
  if (route().current('master-data.item-categories.*')) return 'Kategori Barang';
  if (route().current('master-data.buildings.*')) return 'Data Gedung';
  if (route().current('master-data.rooms.*')) return 'Data Ruangan';
  if (route().current('master-data.item-functions.*')) return 'Fungsi Barang';
  return 'Beranda';
};

const getBreadcrumbParent = () => {
  if (route().current('inventory.*')) return 'Inventaris';
  if (route().current('master-data.*')) return 'Data Referensi';
  if (route().current('user-management.*') || route().current('inventory-period.*')) return 'Super Admin';
  if (route().current('integrity-pact.*') || route().current('data-finalization.*')) return 'Tata Kelola';
  return 'Menu';
};
</script>

<template>
  <div class="min-h-screen bg-surface flex font-sans antialiased text-surface-foreground selection:bg-primary-container selection:text-primary-on-container">
    <!-- Global Toast & SweetAlert Handlers -->
    <M3ToastContainer />
    <M3SweetAlert />

    <!-- Mobile Navigation Drawer Backdrop -->
    <div
      v-if="showingSidebar"
      @click="showingSidebar = false"
      class="fixed inset-0 z-40 bg-black/40 backdrop-blur-sm lg:hidden transition-opacity"
    ></div>

    <!-- M3 Navigation Drawer (Sidebar) -->
    <aside
      class="fixed inset-y-0 left-0 z-50 w-72 bg-surface-container-low border-r border-outline-variant/40 flex flex-col justify-between transition-transform duration-300 ease-in-out lg:static lg:translate-x-0"
      :class="showingSidebar ? 'translate-x-0 shadow-m3-elevation-3' : '-translate-x-full'"
    >
      <div class="overflow-y-auto flex-1">
        <!-- Drawer Header -->
        <div class="h-20 flex items-center justify-between px-6 border-b border-outline-variant/30 sticky top-0 bg-surface-container-low z-10">
          <Link :href="route('dashboard')" class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-2xl bg-white p-1 flex items-center justify-center border border-outline-variant/40 shadow-xs shrink-0">
              <img src="/images/telkom-schools-logo.png" alt="Logo Telkom Schools" class="w-full h-full object-contain" />
            </div>
            <div>
              <span class="text-base font-bold text-surface-foreground tracking-tight block">Aset & Inventaris</span>
              <span class="text-[11px] text-primary font-bold block">SMK Telkom Lampung</span>
            </div>
          </Link>
          <button
            @click="showingSidebar = false"
            class="p-2 text-surface-on-variant hover:text-surface-foreground hover:bg-surface-variant/40 rounded-m3-full lg:hidden cursor-pointer"
          >
            <span class="material-symbols-outlined text-[20px]">close</span>
          </button>
        </div>

        <!-- User Role Tag in Sidebar (Telkom Theme) -->
        <div class="px-5 pt-4 pb-2">
          <div
            class="p-3 rounded-m3-md flex items-center gap-3 border shadow-2xs"
            :class="isSuperAdmin ? 'bg-primary-container/40 border-primary/20' : 'bg-slate-100 border-slate-200'"
          >
            <div
              class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold shrink-0"
              :class="isSuperAdmin ? 'bg-primary text-white' : 'bg-slate-700 text-white'"
            >
              <span class="material-symbols-outlined text-[18px]">
                {{ isSuperAdmin ? 'shield_person' : 'badge' }}
              </span>
            </div>
            <div class="overflow-hidden">
              <div class="font-bold text-xs text-surface-foreground truncate">{{ $page.props.auth.user.name }}</div>
              <div
                class="text-[10px] font-bold uppercase tracking-wider"
                :class="isSuperAdmin ? 'text-primary' : 'text-slate-700'"
              >
                {{ isSuperAdmin ? 'Super Administrator' : 'Anggota Tim Pendata' }}
              </div>
            </div>
          </div>
        </div>

        <!-- M3 Navigation Items -->
        <nav class="p-3 space-y-1.5">
          <!-- 1. Beranda / Dashboard -->
          <Link
            :href="route('dashboard')"
            class="flex items-center gap-4 px-4 py-3 text-sm font-medium rounded-m3-full transition-all duration-150"
            :class="route().current('dashboard') ? 'bg-primary-container text-primary font-bold shadow-none' : 'text-surface-on-variant hover:bg-surface-variant/40 hover:text-surface-foreground'"
          >
            <span class="material-symbols-outlined text-[22px]">dashboard</span>
            <span>Beranda</span>
          </Link>

          <!-- 2. INVENTARIS BARANG (Accessible by Both) -->
          <div class="space-y-1">
            <button
              type="button"
              @click="isInventoryOpen = !isInventoryOpen"
              class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold rounded-m3-full transition-colors cursor-pointer"
              :class="route().current('inventory.*') ? 'text-primary bg-primary-container/20' : 'text-surface-on-variant hover:bg-surface-variant/30 hover:text-surface-foreground'"
            >
              <div class="flex items-center gap-4">
                <span class="material-symbols-outlined text-[22px]">inventory</span>
                <span>Inventaris</span>
              </div>
              <span
                class="material-symbols-outlined text-[20px] transition-transform duration-200"
                :class="isInventoryOpen ? 'rotate-180' : ''"
              >
                expand_more
              </span>
            </button>

            <!-- Submenu Items -->
            <div v-show="isInventoryOpen" class="pl-11 pr-2 py-1 space-y-1">
              <Link
                :href="route('inventory.items.index')"
                class="flex items-center justify-between px-3.5 py-2 text-xs font-medium rounded-m3-md transition-colors"
                :class="route().current('inventory.items.*') ? 'bg-primary text-primary-foreground font-bold shadow-xs' : 'text-surface-on-variant hover:bg-surface-variant/40 hover:text-surface-foreground'"
              >
                <span>Inventaris Barang</span>
                <span class="material-symbols-outlined text-[16px]">add_box</span>
              </Link>
            </div>
          </div>

          <!-- 3. PAKTA INTEGRITAS & FINALISASI (ANGGOTA MENU) -->
          <template v-if="isAnggota">
            <div class="pt-2">
              <span class="px-4 text-[10px] font-bold text-surface-on-variant/80 uppercase tracking-wider block mb-1">
                Tata Kelola Pendataan
              </span>

              <!-- Pakta Integritas -->
              <Link
                :href="route('integrity-pact.show')"
                class="flex items-center gap-4 px-4 py-2.5 text-xs font-medium rounded-m3-full transition-all duration-150"
                :class="route().current('integrity-pact.*') ? 'bg-primary-container text-primary font-bold shadow-none' : 'text-surface-on-variant hover:bg-surface-variant/40 hover:text-surface-foreground'"
              >
                <span class="material-symbols-outlined text-[20px] text-emerald-600">verified_user</span>
                <span>Pakta Integritas</span>
              </Link>

              <!-- Finalisasi Data & Berita Acara -->
              <Link
                :href="route('data-finalization.index')"
                class="flex items-center gap-4 px-4 py-2.5 text-xs font-medium rounded-m3-full transition-all duration-150"
                :class="route().current('data-finalization.*') ? 'bg-primary-container text-primary font-bold shadow-none' : 'text-surface-on-variant hover:bg-surface-variant/40 hover:text-surface-foreground'"
              >
                <span class="material-symbols-outlined text-[20px] text-primary">description</span>
                <span>Finalisasi & Berita Acara</span>
              </Link>
            </div>
          </template>

          <!-- 4. SUPER ADMIN ONLY MENUS -->
          <template v-if="isSuperAdmin">
            <!-- DATA REFERENSI -->
            <div class="space-y-1 pt-1">
              <button
                type="button"
                @click="isReferenceOpen = !isReferenceOpen"
                class="w-full flex items-center justify-between px-4 py-3 text-sm font-semibold rounded-m3-full transition-colors cursor-pointer"
                :class="route().current('master-data.*') ? 'text-primary bg-primary-container/20' : 'text-surface-on-variant hover:bg-surface-variant/30 hover:text-surface-foreground'"
              >
                <div class="flex items-center gap-4">
                  <span class="material-symbols-outlined text-[22px]">database</span>
                  <span>Data Referensi</span>
                </div>
                <span
                  class="material-symbols-outlined text-[20px] transition-transform duration-200"
                  :class="isReferenceOpen ? 'rotate-180' : ''"
                >
                  expand_more
                </span>
              </button>

              <!-- Sub-menu Data Referensi -->
              <div v-show="isReferenceOpen" class="pl-11 pr-2 py-1 space-y-1">
                <Link
                  :href="route('master-data.schools.index')"
                  class="flex items-center justify-between px-3.5 py-2 text-xs font-medium rounded-m3-md transition-colors"
                  :class="route().current('master-data.schools.*') ? 'bg-primary text-primary-foreground font-bold shadow-xs' : 'text-surface-on-variant hover:bg-surface-variant/40 hover:text-surface-foreground'"
                >
                  <span>Data Sekolah</span>
                  <span class="material-symbols-outlined text-[16px]">school</span>
                </Link>

                <Link
                  :href="route('master-data.item-categories.index')"
                  class="flex items-center justify-between px-3.5 py-2 text-xs font-medium rounded-m3-md transition-colors"
                  :class="route().current('master-data.item-categories.*') ? 'bg-primary text-primary-foreground font-bold shadow-xs' : 'text-surface-on-variant hover:bg-surface-variant/40 hover:text-surface-foreground'"
                >
                  <span>Kategori Barang</span>
                  <span class="material-symbols-outlined text-[16px]">category</span>
                </Link>

                <Link
                  :href="route('master-data.buildings.index')"
                  class="flex items-center justify-between px-3.5 py-2 text-xs font-medium rounded-m3-md transition-colors"
                  :class="route().current('master-data.buildings.*') ? 'bg-primary text-primary-foreground font-bold shadow-xs' : 'text-surface-on-variant hover:bg-surface-variant/40 hover:text-surface-foreground'"
                >
                  <span>Data Gedung</span>
                  <span class="material-symbols-outlined text-[16px]">domain</span>
                </Link>

                <Link
                  :href="route('master-data.rooms.index')"
                  class="flex items-center justify-between px-3.5 py-2 text-xs font-medium rounded-m3-md transition-colors"
                  :class="route().current('master-data.rooms.*') ? 'bg-primary text-primary-foreground font-bold shadow-xs' : 'text-surface-on-variant hover:bg-surface-variant/40 hover:text-surface-foreground'"
                >
                  <span>Data Ruangan</span>
                  <span class="material-symbols-outlined text-[16px]">meeting_room</span>
                </Link>

                <Link
                  :href="route('master-data.item-functions.index')"
                  class="flex items-center justify-between px-3.5 py-2 text-xs font-medium rounded-m3-md transition-colors"
                  :class="route().current('master-data.item-functions.*') ? 'bg-primary text-primary-foreground font-bold shadow-xs' : 'text-surface-on-variant hover:bg-surface-variant/40 hover:text-surface-foreground'"
                >
                  <span>Fungsi Barang</span>
                  <span class="material-symbols-outlined text-[16px]">construction</span>
                </Link>
              </div>
            </div>

            <!-- SUPER ADMIN CONTROLS -->
            <div class="pt-2">
              <span class="px-4 text-[10px] font-bold text-surface-on-variant/80 uppercase tracking-wider block mb-1">
                Admin Control
              </span>

              <!-- User Management -->
              <Link
                :href="route('user-management.users.index')"
                class="flex items-center gap-4 px-4 py-2.5 text-xs font-medium rounded-m3-full transition-all duration-150"
                :class="route().current('user-management.*') ? 'bg-primary-container text-primary font-bold shadow-none' : 'text-surface-on-variant hover:bg-surface-variant/40 hover:text-surface-foreground'"
              >
                <span class="material-symbols-outlined text-[20px] text-primary">manage_accounts</span>
                <span>Manajemen Pengguna</span>
              </Link>

              <!-- Period & Cutoff Settings -->
              <Link
                :href="route('inventory-period.index')"
                class="flex items-center gap-4 px-4 py-2.5 text-xs font-medium rounded-m3-full transition-all duration-150"
                :class="route().current('inventory-period.*') ? 'bg-primary-container text-primary font-bold shadow-none' : 'text-surface-on-variant hover:bg-surface-variant/40 hover:text-surface-foreground'"
              >
                <span class="material-symbols-outlined text-[20px] text-amber-600">timer</span>
                <span>Batas Waktu Cut-off</span>
              </Link>
            </div>
          </template>
        </nav>
      </div>

      <!-- Drawer Footer -->
      <div class="p-4 border-t border-outline-variant/30 bg-surface-container-low">
        <Link
          :href="route('logout')"
          method="post"
          as="button"
          class="w-full flex items-center justify-center gap-3 py-3 px-4 rounded-m3-full text-xs font-bold text-error hover:bg-error-container/40 transition-colors cursor-pointer"
        >
          <span class="material-symbols-outlined text-[20px]">logout</span>
          <span>Keluar dari Aplikasi</span>
        </Link>
      </div>
    </aside>

    <!-- Main Content Area -->
    <div class="flex-1 flex flex-col min-w-0 overflow-hidden">
      <!-- M3 Top App Bar -->
      <header class="h-20 bg-surface-container-low border-b border-outline-variant/30 px-4 sm:px-8 flex items-center justify-between sticky top-0 z-30 shadow-xs">
        <div class="flex items-center gap-4">
          <!-- Mobile Menu Button -->
          <button
            @click="showingSidebar = true"
            class="p-2 text-surface-on-variant hover:text-surface-foreground hover:bg-surface-variant/40 rounded-m3-full lg:hidden cursor-pointer"
          >
            <span class="material-symbols-outlined text-[24px]">menu</span>
          </button>

          <!-- Breadcrumbs -->
          <div>
            <div class="flex items-center gap-1.5 text-[11px] text-surface-on-variant font-medium">
              <span>{{ getBreadcrumbParent() }}</span>
              <span class="material-symbols-outlined text-[14px]">chevron_right</span>
              <span class="text-primary font-bold">{{ getBreadcrumbTitle() }}</span>
            </div>
            <h1 class="text-lg sm:text-xl font-extrabold text-surface-foreground tracking-tight">
              {{ getBreadcrumbTitle() }}
            </h1>
          </div>
        </div>

        <!-- User Profile Dropdown -->
        <div class="flex items-center gap-3">
          <!-- Role Pill Badge (Telkom Theme) -->
          <span
            class="hidden sm:inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold shadow-2xs border"
            :class="isSuperAdmin ? 'bg-primary-container text-primary border-primary/20' : 'bg-slate-100 text-slate-800 border-slate-200'"
          >
            <span class="material-symbols-outlined text-[16px]">
              {{ isSuperAdmin ? 'shield' : 'person' }}
            </span>
            <span>{{ isSuperAdmin ? 'Super Admin' : 'Anggota Tim' }}</span>
          </span>

          <Dropdown align="right" width="48">
            <template #trigger>
              <button
                type="button"
                class="inline-flex items-center gap-2 p-1.5 rounded-m3-full hover:bg-surface-variant/40 transition duration-150 ease-in-out cursor-pointer"
              >
                <div class="w-9 h-9 rounded-full bg-primary text-primary-foreground font-bold flex items-center justify-center text-xs shadow-xs">
                  {{ $page.props.auth.user.name.charAt(0).toUpperCase() }}
                </div>
                <span class="material-symbols-outlined text-[18px] text-surface-on-variant hidden sm:inline-block">
                  expand_more
                </span>
              </button>
            </template>

            <template #content>
              <div class="px-4 py-2 text-xs border-b border-outline-variant/30">
                <p class="font-bold text-surface-foreground">{{ $page.props.auth.user.name }}</p>
                <p class="text-[10px] text-surface-on-variant font-mono">{{ $page.props.auth.user.email }}</p>
              </div>

              <DropdownLink :href="route('profile.edit')">
                <div class="flex items-center gap-2">
                  <span class="material-symbols-outlined text-[18px]">manage_accounts</span>
                  <span>Profil Akun</span>
                </div>
              </DropdownLink>

              <DropdownLink :href="route('logout')" method="post" as="button">
                <div class="flex items-center gap-2 text-error">
                  <span class="material-symbols-outlined text-[18px]">logout</span>
                  <span>Keluar</span>
                </div>
              </DropdownLink>
            </template>
          </Dropdown>
        </div>
      </header>

      <!-- Main Page Canvas -->
      <main class="flex-1 overflow-y-auto p-4 sm:p-8">
        <slot />
      </main>
    </div>
  </div>
</template>
