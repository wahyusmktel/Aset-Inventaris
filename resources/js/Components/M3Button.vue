<script setup>
import { computed } from 'vue';

const props = defineProps({
  variant: {
    type: String,
    default: 'filled', // 'filled' | 'elevated' | 'tonal' | 'outlined' | 'text'
    validator: (v) => ['filled', 'elevated', 'tonal', 'outlined', 'text'].includes(v),
  },
  type: {
    type: String,
    default: 'button',
  },
  icon: {
    type: String,
    default: '',
  },
  trailingIcon: {
    type: String,
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  loading: {
    type: Boolean,
    default: false,
  },
  fullWidth: {
    type: Boolean,
    default: false,
  },
  size: {
    type: String,
    default: 'medium', // 'small' | 'medium' | 'large'
  },
});

const variantClasses = computed(() => {
  switch (props.variant) {
    case 'filled':
      return 'bg-primary text-primary-foreground shadow-none hover:shadow-m3-elevation-1 active:shadow-none hover:bg-primary/92 active:bg-primary/88';
    case 'elevated':
      return 'bg-surface-container-low text-primary shadow-m3-elevation-1 hover:shadow-m3-elevation-2 active:shadow-m3-elevation-1 hover:bg-primary/8';
    case 'tonal':
      return 'bg-secondary-container text-secondary-on-container shadow-none hover:shadow-m3-elevation-1 active:shadow-none hover:bg-secondary-container/90';
    case 'outlined':
      return 'bg-transparent text-primary border border-outline hover:bg-primary/8 active:bg-primary/12 focus:border-primary';
    case 'text':
      return 'bg-transparent text-primary hover:bg-primary/8 active:bg-primary/12';
    default:
      return 'bg-primary text-primary-foreground';
  }
});

const sizeClasses = computed(() => {
  switch (props.size) {
    case 'small':
      return 'h-9 px-4 text-xs tracking-[0.1px] gap-2';
    case 'large':
      return 'h-12 px-6 text-base tracking-[0.1px] gap-3';
    case 'medium':
    default:
      return 'h-10 px-6 text-sm tracking-[0.1px] gap-2';
  }
});
</script>

<template>
  <button
    :type="type"
    :disabled="disabled || loading"
    class="relative inline-flex items-center justify-center font-medium rounded-m3-full transition-all duration-200 select-none overflow-hidden focus:outline-none focus-visible:ring-2 focus-visible:ring-primary focus-visible:ring-offset-2 disabled:opacity-38 disabled:pointer-events-none disabled:shadow-none cursor-pointer"
    :class="[
      variantClasses,
      sizeClasses,
      fullWidth ? 'w-full' : '',
    ]"
  >
    <!-- Loading Spinner -->
    <svg
      v-if="loading"
      class="animate-spin -ml-1 mr-2 h-4 w-4"
      xmlns="http://www.w3.org/2000/svg"
      fill="none"
      viewBox="0 0 24 24"
    >
      <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
      <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
    </svg>

    <!-- Leading Icon -->
    <span v-if="icon && !loading" class="material-symbols-outlined text-[18px]">
      {{ icon }}
    </span>

    <!-- Label -->
    <span class="truncate">
      <slot />
    </span>

    <!-- Trailing Icon -->
    <span v-if="trailingIcon && !loading" class="material-symbols-outlined text-[18px]">
      {{ trailingIcon }}
    </span>
  </button>
</template>
