<script setup>
import { ref, watch, onMounted, onUnmounted } from 'vue';

const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false,
  },
  title: {
    type: String,
    default: 'Modal Dialog',
  },
  subtitle: {
    type: String,
    default: '',
  },
  maxWidth: {
    type: String,
    default: 'max-w-2xl', // 'max-w-md' | 'max-w-lg' | 'max-w-2xl' | 'max-w-4xl'
  },
});

const emit = defineEmits(['update:modelValue', 'close']);

const isMaximized = ref(false);
const modalRef = ref(null);

// Drag state
const isDragging = ref(false);
const position = ref({ x: 0, y: 0 });
const dragStart = ref({ x: 0, y: 0 });

const toggleMaximize = () => {
  isMaximized.value = !isMaximized.value;
  if (isMaximized.value) {
    position.value = { x: 0, y: 0 };
  }
};

const closeModal = () => {
  emit('update:modelValue', false);
  emit('close');
};

const onMouseDown = (e) => {
  if (isMaximized.value) return;
  // Ignore clicks on buttons inside header
  if (e.target.closest('button')) return;

  isDragging.value = true;
  dragStart.value = {
    x: e.clientX - position.value.x,
    y: e.clientY - position.value.y,
  };

  window.addEventListener('mousemove', onMouseMove);
  window.addEventListener('mouseup', onMouseUp);
};

const onMouseMove = (e) => {
  if (!isDragging.value) return;
  position.value = {
    x: e.clientX - dragStart.value.x,
    y: e.clientY - dragStart.value.y,
  };
};

const onMouseUp = () => {
  isDragging.value = false;
  window.removeEventListener('mousemove', onMouseMove);
  window.removeEventListener('mouseup', onMouseUp);
};

// Reset position when opened
watch(
  () => props.modelValue,
  (val) => {
    if (val) {
      position.value = { x: 0, y: 0 };
      isMaximized.value = false;
    }
  }
);

onUnmounted(() => {
  window.removeEventListener('mousemove', onMouseMove);
  window.removeEventListener('mouseup', onMouseUp);
});
</script>

<template>
  <teleport to="body">
    <transition
      enter-active-class="transition duration-200 ease-out"
      enter-from-class="opacity-0"
      enter-to-class="opacity-100"
      leave-active-class="transition duration-150 ease-in"
      leave-from-class="opacity-100"
      leave-to-class="opacity-0"
    >
      <!-- Persistent Backdrop (Does NOT close modal on click) -->
      <div
        v-if="modelValue"
        class="fixed inset-0 z-50 flex items-center justify-center p-4 sm:p-6 bg-black/50 backdrop-blur-sm overflow-hidden"
      >
        <!-- Modal Card Container -->
        <div
          ref="modalRef"
          class="bg-surface-container-lowest border border-outline-variant/40 rounded-m3-xl shadow-m3-elevation-4 flex flex-col transition-all overflow-hidden"
          :class="[
            isMaximized
              ? 'fixed inset-3 sm:inset-5 w-auto h-auto rounded-2xl !translate-x-0 !translate-y-0'
              : `w-full ${maxWidth} max-h-[90vh]`,
            isDragging ? 'select-none cursor-grabbing' : ''
          ]"
          :style="!isMaximized ? { transform: `translate(${position.x}px, ${position.y}px)` } : {}"
        >
          <!-- Draggable Header -->
          <div
            @mousedown="onMouseDown"
            class="px-6 py-4 border-b border-outline-variant/30 flex items-center justify-between bg-surface-container-low select-none transition-colors"
            :class="!isMaximized ? 'cursor-grab active:cursor-grabbing hover:bg-surface-container' : ''"
          >
            <div class="flex items-center gap-3">
              <div class="w-8 h-8 rounded-m3-sm bg-primary-container text-primary-on-container flex items-center justify-center">
                <span class="material-symbols-outlined text-[20px]">drag_indicator</span>
              </div>
              <div>
                <h3 class="text-base font-bold text-surface-foreground leading-tight">
                  {{ title }}
                </h3>
                <p v-if="subtitle" class="text-xs text-surface-on-variant mt-0.5">
                  {{ subtitle }}
                </p>
              </div>
            </div>

            <!-- Action Controls (Maximize / Minimize & Close) -->
            <div class="flex items-center gap-1.5">
              <!-- Maximize / Restore Button -->
              <button
                type="button"
                @click="toggleMaximize"
                class="p-1.5 text-surface-on-variant hover:text-surface-foreground hover:bg-surface-variant/40 rounded-m3-full transition-colors cursor-pointer"
                :title="isMaximized ? 'Kecilkan Ukuran' : 'Maksimalkan Layar'"
              >
                <span class="material-symbols-outlined text-[18px]">
                  {{ isMaximized ? 'fullscreen_exit' : 'fullscreen' }}
                </span>
              </button>

              <!-- Dedicated Close Button ('X') -->
              <button
                type="button"
                @click="closeModal"
                class="p-1.5 text-surface-on-variant hover:text-error hover:bg-error-container/40 rounded-m3-full transition-colors cursor-pointer"
                title="Tutup Modal"
              >
                <span class="material-symbols-outlined text-[20px]">close</span>
              </button>
            </div>
          </div>

          <!-- Modal Body Content Slot (Scrollable) -->
          <div class="p-6 overflow-y-auto flex-1 text-sm text-surface-foreground">
            <slot />
          </div>

          <!-- Modal Footer Slot -->
          <div v-if="$slots.footer" class="px-6 py-3.5 bg-surface-container-low border-t border-outline-variant/30 flex items-center justify-end gap-3">
            <slot name="footer" :close="closeModal" />
          </div>
        </div>
      </div>
    </transition>
  </teleport>
</template>
