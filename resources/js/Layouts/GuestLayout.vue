<script setup>
import M3Carousel from '@/Components/M3Carousel.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-[#FFF5F5] via-[#F8FAFC] to-[#F1F5F9] flex flex-col justify-between p-4 sm:p-6 lg:p-8 font-sans antialiased text-surface-foreground relative overflow-hidden selection:bg-primary-container selection:text-primary-on-container">
    
    <!-- Ambient Floating Glowing Orbs (Telkom Red & Slate) -->
    <div class="absolute -top-24 -left-24 w-96 h-96 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/3 -right-20 w-[420px] h-[420px] bg-primary/8 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-28 left-1/3 w-[460px] h-[460px] bg-slate-900/5 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Header Logo / Brand -->
    <header class="w-full max-w-7xl mx-auto flex items-center justify-between py-2 relative z-10">
      <Link :href="route('login')" class="flex items-center gap-3.5 group">
        <div class="w-11 h-11 rounded-2xl bg-white p-1.5 flex items-center justify-center shadow-md shadow-red-500/15 border border-outline-variant/50 group-hover:scale-105 transition-transform duration-200 shrink-0">
          <img
            src="/images/telkom-schools-logo.png"
            alt="Telkom Schools Logo"
            class="w-full h-full object-contain"
          />
        </div>
        <div class="flex flex-col">
          <div class="flex items-center gap-1.5">
            <span class="text-base sm:text-lg font-extrabold tracking-tight text-surface-foreground leading-tight">
              Aset & Inventaris Sekolah
            </span>
          </div>
          <span class="text-[11px] font-bold text-primary tracking-wide">
            SMK Telkom Lampung &bull; Yayasan Pendidikan Telkom
          </span>
        </div>
      </Link>

      <!-- Right Header Actions -->
      <div class="flex items-center gap-2">
        <span class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white/80 backdrop-blur-md border border-outline-variant/40 text-xs font-semibold text-surface-foreground shadow-xs">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span>Portal Aktif (T.A 2026/2027)</span>
        </span>
      </div>
    </header>

    <!-- Main Container: Left Carousel Slide, Right Smooth Transparent Form -->
    <main class="w-full max-w-7xl mx-auto my-auto py-6 sm:py-8 relative z-10">
      <div class="grid grid-cols-1 lg:grid-cols-12 gap-6 lg:gap-8 items-stretch">
        
        <!-- Left Column: Interactive Illustration Carousel (55% on Large screens) -->
        <div class="lg:col-span-7 flex">
          <M3Carousel />
        </div>

        <!-- Right Column: Smooth Transparent Login Form (45% on Large screens) -->
        <div class="lg:col-span-5 flex items-center justify-center">
          <div class="w-full h-full bg-white/85 backdrop-blur-2xl border border-white/95 rounded-[28px] sm:rounded-[32px] p-6 sm:p-8 lg:p-10 shadow-[0_20px_50px_rgba(229,35,32,0.08)] flex flex-col justify-between transition-all duration-300">
            <slot />
          </div>
        </div>

      </div>
    </main>

    <!-- Footer -->
    <footer class="w-full max-w-7xl mx-auto py-3 flex flex-col sm:flex-row items-center justify-between text-xs text-surface-on-variant gap-2 relative z-10 border-t border-outline-variant/30">
      <p>&copy; {{ new Date().getFullYear() }} <strong>SMK Telkom Lampung</strong> - Yayasan Pendidikan Telkom. Hak Cipta Dilindungi.</p>
      <div class="flex items-center gap-4">
        <span class="text-surface-on-variant font-medium">Jl. Jenderal Sudirman No. 88, Pringsewu, Lampung</span>
      </div>
    </footer>
  </div>
</template>
