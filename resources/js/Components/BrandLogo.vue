<script setup>
defineProps({
  name: {
    type: String,
    default: 'lejty.pl'
  }
});
</script>

<template>
  <div class="flex items-center gap-2.5">
    <!-- Purple Target/Stopwatch Icon -->
    <div class="w-9 h-9 rounded-full bg-[#5D28D0] flex items-center justify-center shadow-md shadow-purple-500/20 text-white relative">
      <!-- Outer small crown/top notch -->
      <div class="absolute -top-1 w-2.5 h-1 bg-[#5D28D0] rounded-t-sm"></div>
      
      <!-- Center geometric pattern -->
      <svg class="w-5 h-5" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5" stroke-linecap="round" stroke-linejoin="round">
        <circle cx="12" cy="12" r="9" />
        <polygon points="12 8 8 12 12 16 16 12" fill="white" stroke="none" />
        <circle cx="12" cy="12" r="1.5" fill="#5D28D0" />
      </svg>
    </div>

    <!-- Brand Name -->
    <span class="text-xl font-bold tracking-tight text-[#1E1B4B]">
      {{ name }}
    </span>
  </div>
</template>
