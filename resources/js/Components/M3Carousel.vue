<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const activeSlide = ref(0);
let timer = null;

const slides = [
  {
    title: 'Analisis Data & Pertumbuhan Cerdas',
    description: 'Pantau metrik bisnis, konversi, dan performa keuangan Anda secara real-time dengan dashboard interaktif.',
    tag: 'Dashboard AI',
    colorFrom: 'from-[#6750A4]',
    colorTo: 'to-[#4F378B]',
    accentBg: 'bg-[#EADDFF]',
    accentText: 'text-[#21005D]',
    icon: 'monitoring',
    svgType: 'chart',
  },
  {
    title: 'Keamanan & Enkripsi Standar Tinggi',
    description: 'Seluruh data dan privasi transaksi Anda terlindungi dengan enkripsi mutakhir dan autentikasi multi-lapis.',
    tag: 'Keamanan Tingkat Tinggi',
    colorFrom: 'from-[#00639B]',
    colorTo: 'to-[#004A75]',
    accentBg: 'bg-[#C2E7FF]',
    accentText: 'text-[#001D35]',
    icon: 'shield_lock',
    svgType: 'security',
  },
  {
    title: 'Kolaborasi & Manajemen Tanpa Batas',
    description: 'Kelola tim, proyek, dan hak akses dalam satu platform responsif berbasis Material Design 3.',
    tag: 'Efisiensi Kerja',
    colorFrom: 'from-[#7D5260]',
    colorTo: 'to-[#5E3A47]',
    accentBg: 'bg-[#FFD8E4]',
    accentText: 'text-[#31111D]',
    icon: 'hub',
    svgType: 'collaboration',
  },
];

const nextSlide = () => {
  activeSlide.value = (activeSlide.value + 1) % slides.length;
};

const prevSlide = () => {
  activeSlide.value = (activeSlide.value - 1 + slides.length) % slides.length;
};

const goToSlide = (index) => {
  activeSlide.value = index;
  restartTimer();
};

const startTimer = () => {
  timer = setInterval(nextSlide, 5000);
};

const stopTimer = () => {
  if (timer) clearInterval(timer);
};

const restartTimer = () => {
  stopTimer();
  startTimer();
};

onMounted(() => {
  startTimer();
});

onUnmounted(() => {
  stopTimer();
});
</script>

<template>
  <div
    class="relative w-full h-full min-h-[480px] lg:min-h-[620px] rounded-[28px] overflow-hidden select-none flex flex-col justify-between p-7 sm:p-9 text-white shadow-2xl transition-all duration-700"
    @mouseenter="stopTimer"
    @mouseleave="startTimer"
  >
    <!-- Background Gradient per Slide -->
    <div
      v-for="(slide, index) in slides"
      :key="'bg-' + index"
      class="absolute inset-0 bg-gradient-to-br transition-opacity duration-700 ease-in-out"
      :class="[slide.colorFrom, slide.colorTo, activeSlide === index ? 'opacity-100' : 'opacity-0 pointer-events-none']"
    >
      <!-- Subtle Decorative Background Circles -->
      <div class="absolute -right-16 -top-16 w-80 h-80 bg-white/10 rounded-full blur-3xl"></div>
      <div class="absolute -left-16 -bottom-16 w-72 h-72 bg-black/15 rounded-full blur-2xl"></div>
    </div>

    <!-- Top Badge Row -->
    <div class="relative z-10 flex items-center justify-between">
      <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/15 backdrop-blur-md border border-white/20 text-xs font-semibold tracking-wide">
        <span class="material-symbols-outlined text-[16px]">{{ slides[activeSlide].icon }}</span>
        <span>{{ slides[activeSlide].tag }}</span>
      </div>

      <!-- Slide Counter -->
      <span class="text-xs font-bold text-white/70 tracking-widest">
        0{{ activeSlide + 1 }} / 0{{ slides.length }}
      </span>
    </div>

    <!-- Center Dynamic Illustration Area -->
    <div class="relative z-10 my-auto py-6 flex items-center justify-center">
      <!-- Slide 1: Chart / Analytics Illustration -->
      <div v-if="activeSlide === 0" class="transition-all duration-500 transform animate-fade-in flex flex-col items-center">
        <div class="relative w-56 h-56 sm:w-64 sm:h-64 flex items-center justify-center">
          <!-- Outer glowing circle -->
          <div class="absolute inset-0 rounded-full bg-white/10 animate-pulse"></div>
          
          <!-- Modern 3D Floating Analytics Card Mockup -->
          <div class="relative z-10 w-52 sm:w-60 bg-white/20 backdrop-blur-xl border border-white/30 rounded-3xl p-5 shadow-2xl space-y-3">
            <div class="flex items-center justify-between">
              <div class="flex items-center gap-2">
                <div class="w-7 h-7 rounded-xl bg-emerald-400 text-emerald-950 flex items-center justify-center font-bold text-xs">
                  📈
                </div>
                <div>
                  <div class="text-xs font-bold text-white">Pertumbuhan</div>
                  <div class="text-[10px] text-white/70">Bulan ini</div>
                </div>
              </div>
              <span class="px-2 py-0.5 rounded-full bg-emerald-500/30 text-emerald-200 text-[10px] font-bold">+38.4%</span>
            </div>

            <!-- Bar Chart Simulation -->
            <div class="h-20 flex items-end justify-between gap-2 pt-2 px-1">
              <div class="w-full bg-white/25 rounded-t-lg h-[40%]"></div>
              <div class="w-full bg-white/35 rounded-t-lg h-[65%]"></div>
              <div class="w-full bg-white/50 rounded-t-lg h-[50%]"></div>
              <div class="w-full bg-white/70 rounded-t-lg h-[85%]"></div>
              <div class="w-full bg-emerald-400 rounded-t-lg h-[100%] shadow-lg shadow-emerald-400/50"></div>
            </div>
          </div>

          <!-- Floating Badge -->
          <div class="absolute -bottom-2 -left-2 bg-white text-primary rounded-2xl px-3.5 py-2 shadow-xl flex items-center gap-2 text-xs font-bold animate-bounce">
            <span>✨</span>
            <span>Real-time Data</span>
          </div>
        </div>
      </div>

      <!-- Slide 2: Security & Shield Illustration -->
      <div v-else-if="activeSlide === 1" class="transition-all duration-500 transform animate-fade-in flex flex-col items-center">
        <div class="relative w-56 h-56 sm:w-64 sm:h-64 flex items-center justify-center">
          <div class="absolute inset-0 rounded-full bg-white/10 animate-pulse"></div>

          <!-- Glowing Shield Element -->
          <div class="relative z-10 w-44 h-52 bg-white/20 backdrop-blur-xl border border-white/30 rounded-[36px] flex flex-col items-center justify-center shadow-2xl p-6 text-center space-y-3">
            <div class="w-16 h-16 rounded-full bg-white text-[#00639B] flex items-center justify-center shadow-lg shadow-black/10">
              <span class="material-symbols-outlined text-[36px]">lock</span>
            </div>
            <div class="text-xs font-bold text-white tracking-wider uppercase">SSL 256-Bit</div>
            <div class="inline-flex items-center gap-1 text-[11px] text-emerald-300 font-semibold">
              <span class="w-2 h-2 rounded-full bg-emerald-400 animate-ping"></span>
              <span>Terproteksi</span>
            </div>
          </div>

          <!-- Floating Chip -->
          <div class="absolute -top-1 -right-2 bg-white text-[#00639B] rounded-2xl px-3.5 py-2 shadow-xl flex items-center gap-2 text-xs font-bold">
            <span class="material-symbols-outlined text-[16px] text-emerald-600">verified_user</span>
            <span>Zero-Trust</span>
          </div>
        </div>
      </div>

      <!-- Slide 3: Collaboration & Hub Illustration -->
      <div v-else class="transition-all duration-500 transform animate-fade-in flex flex-col items-center">
        <div class="relative w-56 h-56 sm:w-64 sm:h-64 flex items-center justify-center">
          <div class="absolute inset-0 rounded-full bg-white/10 animate-pulse"></div>

          <!-- Connected Nodes Elements -->
          <div class="relative z-10 w-52 sm:w-56 bg-white/20 backdrop-blur-xl border border-white/30 rounded-3xl p-5 shadow-2xl space-y-3">
            <div class="text-xs font-bold text-white flex items-center justify-between">
              <span>Aktivitas Tim</span>
              <span class="text-[10px] text-white/70">Aktif</span>
            </div>

            <div class="space-y-2">
              <div class="flex items-center gap-2.5 p-2 rounded-xl bg-white/15">
                <div class="w-6 h-6 rounded-full bg-amber-400 text-amber-950 font-bold text-[10px] flex items-center justify-center">A</div>
                <div class="text-[11px] text-white font-medium truncate">Update Desain M3</div>
              </div>
              <div class="flex items-center gap-2.5 p-2 rounded-xl bg-white/15">
                <div class="w-6 h-6 rounded-full bg-purple-300 text-purple-950 font-bold text-[10px] flex items-center justify-center">B</div>
                <div class="text-[11px] text-white font-medium truncate">Sinkronisasi API</div>
              </div>
            </div>
          </div>

          <!-- Floating Notification -->
          <div class="absolute -bottom-2 -right-2 bg-white text-[#7D5260] rounded-2xl px-3.5 py-2 shadow-xl flex items-center gap-2 text-xs font-bold">
            <span>⚡</span>
            <span>Kolaborasi Instan</span>
          </div>
        </div>
      </div>
    </div>

    <!-- Bottom Content: Title, Description & Controls -->
    <div class="relative z-10 space-y-5">
      <div class="space-y-2">
        <h3 class="text-xl sm:text-2xl font-bold tracking-tight text-white leading-tight">
          {{ slides[activeSlide].title }}
        </h3>
        <p class="text-xs sm:text-sm text-white/80 leading-relaxed max-w-md">
          {{ slides[activeSlide].description }}
        </p>
      </div>

      <!-- Slide Indicators & Arrows -->
      <div class="flex items-center justify-between pt-2 border-t border-white/15">
        <!-- Interactive Pill Indicators -->
        <div class="flex items-center gap-2">
          <button
            v-for="(slide, index) in slides"
            :key="'dot-' + index"
            @click="goToSlide(index)"
            type="button"
            class="h-2 rounded-full transition-all duration-300 cursor-pointer"
            :class="activeSlide === index ? 'w-8 bg-white' : 'w-2 bg-white/40 hover:bg-white/70'"
            :title="slide.title"
          ></button>
        </div>

        <!-- Navigation Arrows -->
        <div class="flex items-center gap-1.5">
          <button
            @click="prevSlide(); restartTimer()"
            type="button"
            class="w-8 h-8 rounded-full bg-white/15 hover:bg-white/30 active:bg-white/40 backdrop-blur-md flex items-center justify-center text-white transition-colors cursor-pointer"
            title="Slide Sebelumnya"
          >
            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
          </button>
          <button
            @click="nextSlide(); restartTimer()"
            type="button"
            class="w-8 h-8 rounded-full bg-white/15 hover:bg-white/30 active:bg-white/40 backdrop-blur-md flex items-center justify-center text-white transition-colors cursor-pointer"
            title="Slide Berikutnya"
          >
            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<style scoped>
@keyframes fadeIn {
  from {
    opacity: 0;
    transform: translateY(8px) scale(0.97);
  }
  to {
    opacity: 1;
    transform: translateY(0) scale(1);
  }
}

.animate-fade-in {
  animation: fadeIn 0.4s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}
</style>
