<script setup>
import { useToast } from '@/Composables/useToast';

const { toasts, removeToast } = useToast();

const getIcon = (type) => {
  switch (type) {
    case 'success':
      return 'check_circle';
    case 'error':
      return 'error';
    case 'warning':
      return 'warning';
    case 'info':
    default:
      return 'info';
  }
};

const getTypeClasses = (type) => {
  switch (type) {
    case 'success':
      return 'bg-emerald-50 text-emerald-900 border-emerald-300 shadow-emerald-500/10';
    case 'error':
      return 'bg-red-50 text-red-900 border-red-300 shadow-red-500/10';
    case 'warning':
      return 'bg-amber-50 text-amber-900 border-amber-300 shadow-amber-500/10';
    case 'info':
    default:
      return 'bg-primary-container text-primary-on-container border-primary/30 shadow-purple-500/10';
  }
};

const getIconColor = (type) => {
  switch (type) {
    case 'success':
      return 'text-emerald-600';
    case 'error':
      return 'text-red-600';
    case 'warning':
      return 'text-amber-600';
    case 'info':
    default:
      return 'text-primary';
  }
};
</script>

<template>
  <div class="fixed top-5 right-5 z-50 flex flex-col gap-3 max-w-sm w-full pointer-events-none">
    <transition-group
      enter-active-class="transition duration-300 ease-out"
      enter-from-class="transform translate-y-2 opacity-0 scale-95"
      enter-to-class="transform translate-y-0 opacity-100 scale-100"
      leave-active-class="transition duration-200 ease-in"
      leave-from-class="transform opacity-100 scale-100"
      leave-to-class="transform translate-x-4 opacity-0 scale-90"
    >
      <div
        v-for="toast in toasts"
        :key="toast.id"
        class="pointer-events-auto flex items-start gap-3 p-4 rounded-2xl border shadow-lg backdrop-blur-md transition-all"
        :class="getTypeClasses(toast.type)"
      >
        <span class="material-symbols-outlined text-[24px] shrink-0 mt-0.5" :class="getIconColor(toast.type)">
          {{ getIcon(toast.type) }}
        </span>

        <div class="flex-1 min-w-0">
          <h4 v-if="toast.title" class="text-xs font-bold leading-tight mb-0.5">
            {{ toast.title }}
          </h4>
          <p class="text-xs font-medium leading-relaxed break-words">
            {{ toast.message }}
          </p>
        </div>

        <button
          @click="removeToast(toast.id)"
          type="button"
          class="shrink-0 p-1 text-gray-400 hover:text-gray-700 rounded-full hover:bg-black/5 transition-colors cursor-pointer"
          title="Tutup Notifikasi"
        >
          <span class="material-symbols-outlined text-[18px]">close</span>
        </button>
      </div>
    </transition-group>
  </div>
</template>
