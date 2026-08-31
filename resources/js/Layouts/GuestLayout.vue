<script setup>
import M3Carousel from '@/Components/M3Carousel.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
  <div class="min-h-screen bg-gradient-to-br from-[#F6F2FA] via-[#EEF2F9] to-[#F2EBF9] flex flex-col justify-between p-4 sm:p-6 lg:p-8 font-sans antialiased text-surface-foreground relative overflow-hidden selection:bg-primary-container selection:text-primary-on-container">
    
    <!-- Ambient Floating Blurred Glowing Orbs for Smooth Translucent Glass Effect -->
    <div class="absolute -top-24 -left-24 w-96 h-96 bg-primary/10 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute top-1/3 -right-20 w-[420px] h-[420px] bg-[#6750A4]/8 rounded-full blur-3xl pointer-events-none"></div>
    <div class="absolute -bottom-28 left-1/3 w-[460px] h-[460px] bg-[#00639B]/8 rounded-full blur-3xl pointer-events-none"></div>

    <!-- Header Logo / Brand -->
    <header class="w-full max-w-7xl mx-auto flex items-center justify-between py-2 relative z-10">
      <Link :href="route('login')" class="flex items-center gap-3 group">
        <div class="w-10 h-10 rounded-2xl bg-gradient-to-tr from-primary to-[#7F67BE] flex items-center justify-center text-primary-foreground shadow-md shadow-primary/25 group-hover:scale-105 transition-transform duration-200">
          <span class="material-symbols-outlined text-[24px]">widgets</span>
        </div>
        <div class="flex flex-col">
          <span class="text-lg font-bold tracking-tight text-surface-foreground leading-tight">Material 3</span>
          <span class="text-[11px] font-medium text-surface-on-variant">SPA Template</span>
        </div>
      </Link>

      <!-- Right Header Actions -->
      <div class="flex items-center gap-2">
        <span class="hidden sm:inline-flex items-center gap-1.5 px-3.5 py-1.5 rounded-full bg-white/60 backdrop-blur-md border border-white/60 text-xs font-medium text-surface-on-variant shadow-sm">
          <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
          <span>Sistem Aktif</span>
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
          <div class="w-full h-full bg-white/75 backdrop-blur-2xl border border-white/90 rounded-[28px] sm:rounded-[32px] p-6 sm:p-8 lg:p-10 shadow-[0_20px_50px_rgba(103,80,164,0.1)] flex flex-col justify-between transition-all duration-300">
            <slot />
          </div>
        </div>

      </div>
    </main>

    <!-- Footer -->
    <footer class="w-full max-w-7xl mx-auto py-3 flex flex-col sm:flex-row items-center justify-between text-xs text-surface-on-variant gap-2 relative z-10">
      <p>&copy; {{ new Date().getFullYear() }} Material Design 3 Template. Dibuat dengan Laravel + Vue 3 SPA.</p>
      <div class="flex items-center gap-4">
        <a href="#" class="hover:text-primary transition-colors">Privasi</a>
        <a href="#" class="hover:text-primary transition-colors">Ketentuan</a>
        <a href="#" class="hover:text-primary transition-colors">Pusat Bantuan</a>
      </div>
    </footer>
  </div>
</template>
