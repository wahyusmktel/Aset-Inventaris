<script setup>
import { computed } from 'vue';

const props = defineProps({
  currentPage: {
    type: Number,
    default: 1,
  },
  totalItems: {
    type: Number,
    required: true,
  },
  perPage: {
    type: Number,
    default: 10,
  },
});

const emit = defineEmits(['update:currentPage', 'page-change']);

const totalPages = computed(() => Math.max(1, Math.ceil(props.totalItems / props.perPage)));

const startItem = computed(() => (props.totalItems === 0 ? 0 : (props.currentPage - 1) * props.perPage + 1));
const endItem = computed(() => Math.min(props.currentPage * props.perPage, props.totalItems));

const setPage = (page) => {
  if (page < 1 || page > totalPages.value || page === props.currentPage) return;
  emit('update:currentPage', page);
  emit('page-change', page);
};

const pagesToShow = computed(() => {
  const pages = [];
  const total = totalPages.value;
  const current = props.currentPage;

  if (total <= 5) {
    for (let i = 1; i <= total; i++) pages.push(i);
  } else {
    pages.push(1);
    if (current > 3) pages.push('...');
    const start = Math.max(2, current - 1);
    const end = Math.min(total - 1, current + 1);
    for (let i = start; i <= end; i++) pages.push(i);
    if (current < total - 2) pages.push('...');
    pages.push(total);
  }

  return pages;
});
</script>

<template>
  <div class="flex flex-col sm:flex-row items-center justify-between gap-4 py-3 px-2 select-none">
    <!-- Item Count Summary -->
    <div class="text-xs text-surface-on-variant font-medium">
      Menampilkan <span class="font-bold text-surface-foreground">{{ startItem }}</span> -
      <span class="font-bold text-surface-foreground">{{ endItem }}</span> dari
      <span class="font-bold text-surface-foreground">{{ totalItems }}</span> data ({{ perPage }}/hal)
    </div>

    <!-- Pagination Controls -->
    <div class="flex items-center gap-1">
      <!-- Previous Page Button -->
      <button
        type="button"
        :disabled="currentPage <= 1"
        @click="setPage(currentPage - 1)"
        class="h-9 w-9 rounded-m3-full flex items-center justify-center text-surface-on-variant hover:bg-surface-variant/40 hover:text-surface-foreground disabled:opacity-30 disabled:pointer-events-none transition-colors cursor-pointer"
        title="Halaman Sebelumnya"
      >
        <span class="material-symbols-outlined text-[18px]">chevron_left</span>
      </button>

      <!-- Page Numbers -->
      <template v-for="(page, idx) in pagesToShow" :key="idx">
        <span v-if="page === '...'" class="px-2 text-xs text-surface-on-variant select-none">
          ...
        </span>
        <button
          v-else
          type="button"
          @click="setPage(page)"
          class="h-9 min-w-[36px] px-2.5 rounded-m3-full text-xs font-semibold transition-all cursor-pointer"
          :class="[
            currentPage === page
              ? 'bg-primary text-primary-foreground shadow-m3-elevation-1'
              : 'text-surface-on-variant hover:bg-surface-variant/40 hover:text-surface-foreground'
          ]"
        >
          {{ page }}
        </button>
      </template>

      <!-- Next Page Button -->
      <button
        type="button"
        :disabled="currentPage >= totalPages"
        @click="setPage(currentPage + 1)"
        class="h-9 w-9 rounded-m3-full flex items-center justify-center text-surface-on-variant hover:bg-surface-variant/40 hover:text-surface-foreground disabled:opacity-30 disabled:pointer-events-none transition-colors cursor-pointer"
        title="Halaman Berikutnya"
      >
        <span class="material-symbols-outlined text-[18px]">chevron_right</span>
      </button>
    </div>
  </div>
</template>
