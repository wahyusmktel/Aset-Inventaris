<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const activeSlide = ref(0);
let timer = null;

const slides = [
  {
    title: 'Digitalisasi Aset Laboratorium & Sarpras Sekolah',
    description: 'Pencatatan akurat sarana praktikum kejuruan RPL, Fiber Optic TJAT, Studio Animasi 3D, Lab TKJ, dan seluruh fasilitas penunjang kampus SMK Telkom Lampung.',
    tag: 'Kejuruan & Sarpras',
    colorFrom: 'from-[#E52320]',
    colorTo: 'to-[#991B1B]',
    accentBg: 'bg-[#FFE8E9]',
    accentText: 'text-[#660007]',
    icon: 'devices',
  },
  {
    title: 'Pakta Integritas & Berita Acara Sah 3 Tanda Tangan',
    description: 'Menjamin akuntabilitas dan keabsahan fisik barang dengan penandatanganan digital oleh Tim Surveyor, Kaur IT (PIC Aset), dan Kepala Sekolah.',
    tag: 'Legal & Akuntabel',
    colorFrom: 'from-[#1E293B]',
    colorTo: 'to-[#0F172A]',
    accentBg: 'bg-primary-container',
    accentText: 'text-primary-on-container',
    icon: 'verified_user',
  },
  {
    title: 'Monitoring Cut-off & Laporan Eksekutif Excel',
    description: 'Pantau hitung mundur batas waktu pengadaan, tingkat kelaikan unit siap pakai, dan ekspor laporan inventaris berstandar Yayasan Pendidikan Telkom.',
    tag: 'Tata Kelola Disiplin',
    colorFrom: 'from-[#BE123C]',
    colorTo: 'to-[#881337]',
    accentBg: 'bg-[#FFE4E6]',
    accentText: 'text-[#881337]',
    icon: 'timer',
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
    class="relative w-full h-full min-h-[480px] lg:min-h-[600px] rounded-[28px] overflow-hidden select-none flex flex-col justify-between p-7 sm:p-9 text-white shadow-2xl transition-all duration-700"
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
      <div class="absolute -left-16 -bottom-16 w-72 h-72 bg-black/20 rounded-full blur-2xl"></div>
    </div>

    <!-- Top Badge Row -->
    <div class="relative z-10 flex items-center justify-between">
      <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/15 backdrop-blur-md border border-white/20 text-xs font-semibold tracking-wide">
        <span class="material-symbols-outlined text-[16px]">{{ slides[activeSlide].icon }}</span>
        <span>{{ slides[activeSlide].tag }}</span>
      </div>

      <!-- Telkom Schools Brand Watermark Badge -->
      <div class="flex items-center gap-2 px-3 py-1 rounded-full bg-white/10 backdrop-blur-md text-[11px] font-bold text-white/90">
        <span>Telkom Schools</span>
      </div>
    </div>

    <!-- Center Interactive Graphic Illustration (Telkom Schools Themed) -->
    <div class="relative z-10 my-auto py-8 flex items-center justify-center">
      <!-- Slide 1: Lab & Devices -->
      <div v-if="activeSlide === 0" class="w-full max-w-sm flex flex-col items-center justify-center space-y-4">
        <div class="w-24 h-24 rounded-3xl bg-white/15 backdrop-blur-xl border border-white/30 flex items-center justify-center shadow-2xl shadow-black/20">
          <span class="material-symbols-outlined text-[48px] text-white">inventory_2</span>
        </div>
        <div class="grid grid-cols-2 gap-3 w-full">
          <div class="p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-center">
            <span class="text-[10px] text-white/80 font-medium block">Lab Komputer</span>
            <span class="text-xs font-bold text-white mt-0.5 block">RPL, TKJ, Animasi</span>
          </div>
          <div class="p-3 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-center">
            <span class="text-[10px] text-white/80 font-medium block">Telekomunikasi</span>
            <span class="text-xs font-bold text-white mt-0.5 block">Fiber Optic (TJAT)</span>
          </div>
        </div>
      </div>

      <!-- Slide 2: Legal & Verification -->
      <div v-else-if="activeSlide === 1" class="w-full max-w-sm flex flex-col items-center justify-center space-y-4">
        <div class="w-24 h-24 rounded-3xl bg-white/15 backdrop-blur-xl border border-white/30 flex items-center justify-center shadow-2xl shadow-black/20">
          <span class="material-symbols-outlined text-[48px] text-white">draw</span>
        </div>
        <div class="p-3.5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-center w-full">
          <span class="text-xs font-bold text-white block">Tanda Tangan Digital & Berita Acara</span>
          <span class="text-[11px] text-white/80 mt-1 block">Surveyor &bull; Kaur IT &bull; Kepala Sekolah</span>
        </div>
      </div>

      <!-- Slide 3: Time & Cutoff -->
      <div v-else class="w-full max-w-sm flex flex-col items-center justify-center space-y-4">
        <div class="w-24 h-24 rounded-3xl bg-white/15 backdrop-blur-xl border border-white/30 flex items-center justify-center shadow-2xl shadow-black/20">
          <span class="material-symbols-outlined text-[48px] text-white">alarm_on</span>
        </div>
        <div class="p-3.5 rounded-2xl bg-white/10 backdrop-blur-md border border-white/20 text-center w-full">
          <span class="text-xs font-bold text-white block">Sistem Otomatisasi Batas Cut-Off</span>
          <span class="text-[11px] text-white/80 mt-1 block">Pelaporan Cepat & Ekspor Excel Presisi</span>
        </div>
      </div>
    </div>

    <!-- Bottom Text & Slide Controls -->
    <div class="relative z-10 space-y-4">
      <div class="space-y-2">
        <h2 class="text-xl sm:text-2xl font-black text-white leading-tight tracking-tight">
          {{ slides[activeSlide].title }}
        </h2>
        <p class="text-xs sm:text-sm text-white/80 leading-relaxed max-w-md">
          {{ slides[activeSlide].description }}
        </p>
      </div>

      <!-- Navigation Bullets & Arrows -->
      <div class="flex items-center justify-between pt-2">
        <!-- Bullets -->
        <div class="flex items-center gap-2">
          <button
            v-for="(_, index) in slides"
            :key="'bullet-' + index"
            type="button"
            @click="goToSlide(index)"
            class="h-2 rounded-full transition-all duration-300 cursor-pointer"
            :class="activeSlide === index ? 'w-8 bg-white' : 'w-2 bg-white/40 hover:bg-white/60'"
            :title="'Slide ' + (index + 1)"
          ></button>
        </div>

        <!-- Arrows -->
        <div class="flex items-center gap-1.5">
          <button
            type="button"
            @click="prevSlide"
            class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/25 active:bg-white/35 backdrop-blur-md flex items-center justify-center transition-colors cursor-pointer"
            title="Sebelumnya"
          >
            <span class="material-symbols-outlined text-[18px]">chevron_left</span>
          </button>
          <button
            type="button"
            @click="nextSlide"
            class="w-9 h-9 rounded-full bg-white/10 hover:bg-white/25 active:bg-white/35 backdrop-blur-md flex items-center justify-center transition-colors cursor-pointer"
            title="Berikutnya"
          >
            <span class="material-symbols-outlined text-[18px]">chevron_right</span>
          </button>
        </div>
      </div>
    </div>
  </div>
</template>
