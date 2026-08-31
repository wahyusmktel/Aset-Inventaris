<script setup>
defineProps({
  name: {
    type: String,
    default: 'SMK Telkom Lampung',
  },
  subtitle: {
    type: String,
    default: 'Aset & Inventaris Sekolah',
  },
});
</script>

<template>
  <div class="flex items-center gap-3">
    <!-- Telkom Schools Official Monogram Logo -->
    <div class="w-10 h-10 rounded-2xl bg-white p-1 flex items-center justify-center shadow-md shadow-red-500/15 border border-outline-variant/40 shrink-0">
      <img
        src="/images/telkom-schools-logo.png"
        alt="Logo Telkom Schools"
        class="w-full h-full object-contain"
      />
    </div>

    <!-- Brand Text -->
    <div class="flex flex-col">
      <span class="text-base font-extrabold tracking-tight text-surface-foreground leading-tight">
        {{ name }}
      </span>
      <span class="text-[11px] font-semibold text-primary">
        {{ subtitle }}
      </span>
    </div>
  </div>
</template>
