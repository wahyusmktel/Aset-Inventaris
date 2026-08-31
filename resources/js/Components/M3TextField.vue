<script setup>
import { computed, ref } from 'vue';

defineOptions({
  inheritAttrs: false
});

const props = defineProps({
  modelValue: {
    type: [String, Number],
    default: '',
  },
  label: {
    type: String,
    required: true,
  },
  type: {
    type: String,
    default: 'text',
  },
  name: {
    type: String,
    default: '',
  },
  id: {
    type: String,
    default: () => `m3-input-${Math.random().toString(36).substr(2, 9)}`,
  },
  leadingIcon: {
    type: String,
    default: '',
  },
  trailingIcon: {
    type: String,
    default: '',
  },
  errorMessage: {
    type: String,
    default: '',
  },
  helperText: {
    type: String,
    default: '',
  },
  required: {
    type: Boolean,
    default: false,
  },
  autocomplete: {
    type: String,
    default: 'off',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue', 'clickTrailingIcon']);

const isFocused = ref(false);
const inputRef = ref(null);

const hasValue = computed(() => {
  return props.modelValue !== '' && props.modelValue !== null && props.modelValue !== undefined;
});

const isFloating = computed(() => {
  return isFocused.value || hasValue.value;
});

const hasError = computed(() => {
  return Boolean(props.errorMessage);
});
</script>

<template>
  <div class="relative w-full flex flex-col pt-1 pb-1">
    <!-- M3 Outlined Text Field Container -->
    <div
      class="relative flex items-center h-14 w-full rounded-m3-xs transition-all duration-200"
      :class="[
        hasError
          ? 'border-2 border-error'
          : isFocused
          ? 'border-2 border-primary'
          : 'border border-outline hover:border-surface-foreground hover:border-opacity-80',
        disabled ? 'opacity-38 border-outline-variant bg-surface-container-low cursor-not-allowed' : 'bg-transparent'
      ]"
    >
      <!-- Leading Icon -->
      <div v-if="leadingIcon" class="pl-4 pr-1 flex items-center justify-center text-surface-on-variant select-none pointer-events-none" :class="{ 'text-error': hasError, 'text-primary': isFocused && !hasError }">
        <span class="material-symbols-outlined text-[20px]">{{ leadingIcon }}</span>
      </div>

      <!-- Input Element with Autofill Prevention -->
      <input
        :id="id"
        ref="inputRef"
        :type="type"
        :name="name || id"
        :value="modelValue"
        :disabled="disabled"
        :required="required"
        :autocomplete="autocomplete"
        autocorrect="off"
        autocapitalize="off"
        spellcheck="false"
        v-bind="$attrs"
        @input="emit('update:modelValue', $event.target.value)"
        @focus="isFocused = true"
        @blur="isFocused = false"
        class="peer w-full h-full bg-transparent px-4 text-surface-foreground text-base tracking-normal outline-none border-none focus:ring-0 placeholder-transparent disabled:cursor-not-allowed"
        :class="{ 'pl-3': leadingIcon, 'pr-12': trailingIcon }"
        placeholder=" "
      />

      <!-- M3 Floating Label (Floating notch cut out effect) -->
      <label
        :for="id"
        class="absolute pointer-events-none transition-all duration-150 ease-out select-none px-1 bg-surface-container-lowest"
        :class="[
          leadingIcon ? (isFloating ? 'left-3 -top-2.5 text-xs font-medium' : 'left-11 top-4 text-base') : (isFloating ? 'left-3 -top-2.5 text-xs font-medium' : 'left-4 top-4 text-base'),
          hasError
            ? 'text-error'
            : isFocused
            ? 'text-primary'
            : 'text-surface-on-variant'
        ]"
      >
        {{ label }}
      </label>

      <!-- Trailing Icon (e.g. Password visibility toggle) -->
      <div v-if="trailingIcon" class="absolute right-3.5 flex items-center">
        <button
          type="button"
          tabindex="-1"
          @click="emit('clickTrailingIcon')"
          class="p-1 rounded-full text-surface-on-variant hover:text-surface-foreground hover:bg-surface-variant/40 focus:outline-none transition-colors"
          :class="{ 'text-error': hasError }"
        >
          <span class="material-symbols-outlined text-[20px]">{{ trailingIcon }}</span>
        </button>
      </div>
    </div>

    <!-- Supporting Text / Error Text -->
    <div class="px-4 pt-1 flex items-center min-h-[18px]">
      <p v-if="hasError" class="text-xs text-error font-normal leading-none">
        {{ errorMessage }}
      </p>
      <p v-else-if="helperText" class="text-xs text-surface-on-variant font-normal leading-none">
        {{ helperText }}
      </p>
    </div>
  </div>
</template>
