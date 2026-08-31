<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  modelValue: {
    type: [String, Number, null],
    default: '',
  },
  options: {
    type: Array,
    default: () => [], // [{ id, code, name }]
  },
  label: {
    type: String,
    default: 'Pilih Opsi',
  },
  placeholder: {
    type: String,
    default: '-- Pilih --',
  },
  leadingIcon: {
    type: String,
    default: '',
  },
  disabled: {
    type: Boolean,
    default: false,
  },
  required: {
    type: Boolean,
    default: false,
  },
});

const emit = defineEmits(['update:modelValue', 'change']);

const isOpen = ref(false);
const searchQuery = ref('');
const dropdownRef = ref(null);

const selectedOption = computed(() => {
  return props.options.find((opt) => opt.id === props.modelValue) || null;
});

const filteredOptions = computed(() => {
  if (!searchQuery.value) return props.options;
  const q = searchQuery.value.toLowerCase();
  return props.options.filter((opt) => {
    const codeMatch = opt.code ? opt.code.toLowerCase().includes(q) : false;
    const nameMatch = opt.name ? opt.name.toLowerCase().includes(q) : false;
    return codeMatch || nameMatch;
  });
});

const toggleDropdown = () => {
  if (props.disabled) return;
  isOpen.value = !isOpen.value;
  if (isOpen.value) {
    searchQuery.value = '';
  }
};

const selectOption = (opt) => {
  emit('update:modelValue', opt.id);
  emit('change', opt);
  isOpen.value = false;
};

const clearSelection = (e) => {
  e.stopPropagation();
  emit('update:modelValue', '');
  emit('change', null);
};

const handleClickOutside = (e) => {
  if (dropdownRef.value && !dropdownRef.value.contains(e.target)) {
    isOpen.value = false;
  }
};

onMounted(() => {
  window.addEventListener('click', handleClickOutside);
});

onUnmounted(() => {
  window.removeEventListener('click', handleClickOutside);
});
</script>

<template>
  <div ref="dropdownRef" class="relative w-full">
    <!-- Trigger Button -->
    <div
      @click="toggleDropdown"
      class="w-full min-h-[48px] px-3.5 py-2.5 rounded-m3-xs border transition-all flex items-center justify-between gap-2 bg-surface-container-lowest"
      :class="[
        isOpen ? 'border-primary ring-1 ring-primary' : 'border-outline hover:border-surface-foreground/60',
        disabled ? 'opacity-50 pointer-events-none bg-surface-container-low' : 'cursor-pointer'
      ]"
    >
      <div class="flex items-center gap-2.5 min-w-0 flex-1">
        <span v-if="leadingIcon" class="material-symbols-outlined text-[18px] text-surface-on-variant shrink-0">
          {{ leadingIcon }}
        </span>

        <div class="truncate text-xs">
          <template v-if="selectedOption">
            <span class="font-bold text-surface-foreground">{{ selectedOption.name }}</span>
            <span v-if="selectedOption.code" class="text-[10px] text-primary font-mono ml-1.5 font-semibold">
              [{{ selectedOption.code }}]
            </span>
          </template>
          <span v-else class="text-surface-on-variant/80">
            {{ placeholder }}
          </span>
        </div>
      </div>

      <div class="flex items-center gap-1 shrink-0 text-surface-on-variant">
        <button
          v-if="selectedOption && !disabled"
          type="button"
          @click="clearSelection"
          class="p-0.5 hover:text-error rounded-full hover:bg-black/5 transition-colors"
          title="Hapus Pilihan"
        >
          <span class="material-symbols-outlined text-[16px]">close</span>
        </button>
        <span class="material-symbols-outlined text-[20px] transition-transform duration-200" :class="isOpen ? 'rotate-180' : ''">
          arrow_drop_down
        </span>
      </div>
    </div>

    <!-- Dropdown Menu Box -->
    <transition
      enter-active-class="transition duration-150 ease-out"
      enter-from-class="transform scale-95 opacity-0 -translate-y-1"
      enter-to-class="transform scale-100 opacity-100 translate-y-0"
      leave-active-class="transition duration-100 ease-in"
      leave-from-class="transform scale-100 opacity-100 translate-y-0"
      leave-to-class="transform scale-95 opacity-0 -translate-y-1"
    >
      <div
        v-if="isOpen"
        class="absolute left-0 right-0 top-full mt-1.5 z-50 bg-surface-container-lowest border border-outline-variant/60 rounded-m3-md shadow-m3-elevation-3 overflow-hidden flex flex-col max-h-60"
      >
        <!-- Search Input Box inside dropdown -->
        <div class="p-2 border-b border-outline-variant/40 bg-surface-container-low sticky top-0 z-10">
          <div class="relative flex items-center">
            <span class="material-symbols-outlined text-[16px] text-surface-on-variant absolute left-2.5 pointer-events-none">
              search
            </span>
            <input
              type="text"
              v-model="searchQuery"
              placeholder="Ketik untuk mencari..."
              @click.stop
              class="w-full h-8 pl-8 pr-3 text-xs bg-surface-container-lowest border border-outline focus:border-primary focus:ring-0 rounded-m3-xs text-surface-foreground placeholder:text-surface-on-variant"
              autofocus
            />
          </div>
        </div>

        <!-- Options List -->
        <div class="overflow-y-auto flex-1 divide-y divide-outline-variant/20">
          <button
            v-for="opt in filteredOptions"
            :key="opt.id"
            type="button"
            @click="selectOption(opt)"
            class="w-full text-left px-3.5 py-2.5 text-xs hover:bg-primary-container/40 flex items-center justify-between gap-2 transition-colors cursor-pointer"
            :class="opt.id === modelValue ? 'bg-primary/10 text-primary font-bold' : 'text-surface-foreground'"
          >
            <div class="flex items-center gap-2 truncate">
              <span v-if="opt.code" class="px-1.5 py-0.5 rounded bg-surface-container font-mono text-[10px] text-primary shrink-0 border border-outline-variant/40">
                {{ opt.code }}
              </span>
              <span class="truncate">{{ opt.name }}</span>
            </div>
            <span v-if="opt.id === modelValue" class="material-symbols-outlined text-[18px] text-primary shrink-0">
              check
            </span>
          </button>

          <div v-if="filteredOptions.length === 0" class="p-4 text-center text-xs text-surface-on-variant">
            Tidak ada opsi yang sesuai dengan pencarian.
          </div>
        </div>
      </div>
    </transition>
  </div>
</template>
