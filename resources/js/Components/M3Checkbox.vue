<script setup>
import { computed } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  id: {
    type: String,
    default: () => `m3-check-${Math.random().toString(36).substr(2, 9)}`,
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue']);

const isChecked = computed({
  get: () => props.modelValue,
  set: (val) => emit('update:modelValue', val),
});
</script>

<template>
  <label
    :for="id"
    class="relative inline-flex items-center gap-3 cursor-pointer select-none group py-1"
    :class="{ 'opacity-38 cursor-not-allowed pointer-events-none': disabled }"
  >
    <!-- Checkbox Target with M3 Ripple Circle -->
    <div class="relative flex items-center justify-center w-10 h-10 -ml-2 rounded-full group-hover:bg-primary/8 transition-colors">
      <input
        :id="id"
        type="checkbox"
        v-model="isChecked"
        :disabled="disabled"
        class="sr-only peer"
      />
      <!-- M3 Box Outline / Filled Box -->
      <div
        class="w-[18px] h-[18px] rounded-[3px] flex items-center justify-center transition-all duration-200 border-2"
        :class="[
          isChecked
            ? 'bg-primary border-primary'
            : 'border-surface-on-variant bg-transparent group-hover:border-surface-foreground'
        ]"
      >
        <!-- Checkmark icon -->
        <svg
          v-if="isChecked"
          class="w-3.5 h-3.5 text-primary-foreground stroke-current stroke-[2.5]"
          viewBox="0 0 24 24"
          fill="none"
        >
          <path d="M5 13l4 4L19 7" stroke-linecap="round" stroke-linejoin="round" />
        </svg>
      </div>
    </div>

    <!-- Label Text -->
    <span class="text-sm text-surface-foreground font-normal">
      <slot />
    </span>
  </label>
</template>
