<script setup>
import { ref } from 'vue';
import { useAlert } from '@/Composables/useAlert';
import M3Button from '@/Components/M3Button.vue';

const { alertState } = useAlert();
const isConfirming = ref(false);

const handleConfirm = async () => {
  if (alertState.value.onConfirm) {
    isConfirming.value = true;
    try {
      await alertState.value.onConfirm();
    } finally {
      isConfirming.value = false;
    }
  }
};

const handleCancel = () => {
  if (alertState.value.onCancel) {
    alertState.value.onCancel();
  }
};

const getIcon = (type) => {
  switch (type) {
    case 'warning':
      return 'warning';
    case 'error':
      return 'dangerous';
    case 'success':
      return 'check_circle';
    case 'question':
      return 'help';
    case 'info':
    default:
      return 'info';
  }
};

const getIconStyles = (type) => {
  switch (type) {
    case 'warning':
      return 'bg-amber-100 text-amber-700 border-amber-300';
    case 'error':
      return 'bg-red-100 text-red-700 border-red-300';
    case 'success':
      return 'bg-emerald-100 text-emerald-700 border-emerald-300';
    case 'question':
      return 'bg-blue-100 text-blue-700 border-blue-300';
    case 'info':
    default:
      return 'bg-purple-100 text-purple-700 border-purple-300';
  }
};
</script>

<template>
  <teleport to="body">
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0 scale-95"
      enter-to-class="opacity-100 scale-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100 scale-100"
      leave-to-class="opacity-0 scale-95"
    >
      <div
        v-if="alertState.isOpen"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/55 backdrop-blur-sm"
      >
        <!-- Persistent Alert Card Container -->
        <div class="w-full max-w-md bg-surface-container-lowest border border-outline-variant/40 rounded-m3-xl shadow-m3-elevation-5 p-6 sm:p-8 flex flex-col items-center text-center space-y-4 transition-all">
          
          <!-- Alert Animated Emblem Icon -->
          <div
            class="w-16 h-16 rounded-full border-2 flex items-center justify-center shadow-inner animate-bounce"
            :class="getIconStyles(alertState.type)"
          >
            <span class="material-symbols-outlined text-[36px]">
              {{ getIcon(alertState.type) }}
            </span>
          </div>

          <!-- Alert Title & Message -->
          <div class="space-y-1.5">
            <h3 class="text-xl font-extrabold text-surface-foreground tracking-tight">
              {{ alertState.title }}
            </h3>
            <p class="text-xs sm:text-sm text-surface-on-variant leading-relaxed">
              {{ alertState.message }}
            </p>
          </div>

          <!-- Action Buttons Row -->
          <div class="pt-3 w-full flex items-center justify-center gap-3">
            <M3Button
              v-if="alertState.showCancel"
              type="button"
              variant="outlined"
              size="medium"
              :disabled="isConfirming"
              @click="handleCancel"
            >
              {{ alertState.cancelText }}
            </M3Button>

            <M3Button
              type="button"
              variant="filled"
              size="medium"
              :loading="isConfirming"
              :disabled="isConfirming"
              @click="handleConfirm"
            >
              {{ alertState.confirmText }}
            </M3Button>
          </div>

        </div>
      </div>
    </transition>
  </teleport>
</template>
