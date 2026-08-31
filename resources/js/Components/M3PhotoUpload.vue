<script setup>
import { ref, watch } from 'vue';
import { compressImage } from '@/Utils/imageCompressor';

const props = defineProps({
  modelValue: {
    type: [File, null],
    default: null,
  },
  existingPhotoUrl: {
    type: String,
    default: '',
  },
});

const emit = defineEmits(['update:modelValue', 'change']);

const fileInputRef = ref(null);
const isDragging = ref(false);
const previewUrl = ref(props.existingPhotoUrl || '');
const isCompressing = ref(false);
const fileSizeKb = ref(null);

watch(
  () => props.existingPhotoUrl,
  (val) => {
    if (val && !props.modelValue) {
      previewUrl.value = val;
    }
  }
);

const handleFileSelect = async (file) => {
  if (!file || !file.type.startsWith('image/')) return;

  isCompressing.value = true;
  try {
    const compressed = await compressImage(file, {
      maxSizeBytes: 1024 * 1024, // 1MB limit
      quality: 0.85,
    });

    fileSizeKb.value = (compressed.size / 1024).toFixed(1);
    previewUrl.value = URL.createObjectURL(compressed);
    emit('update:modelValue', compressed);
    emit('change', compressed);
  } catch (err) {
    console.error('Error compressing image:', err);
  } finally {
    isCompressing.value = false;
  }
};

const onInputChange = (e) => {
  const file = e.target.files[0];
  if (file) handleFileSelect(file);
};

const onDrop = (e) => {
  isDragging.value = false;
  const file = e.dataTransfer.files[0];
  if (file) handleFileSelect(file);
};

const removePhoto = () => {
  previewUrl.value = '';
  fileSizeKb.value = null;
  if (fileInputRef.value) fileInputRef.value.value = '';
  emit('update:modelValue', null);
  emit('change', null);
};

const triggerFileInput = () => {
  if (fileInputRef.value) fileInputRef.value.click();
};
</script>

<template>
  <div class="space-y-2 w-full">
    <input
      ref="fileInputRef"
      type="file"
      accept="image/jpeg,image/png,image/webp,image/jpg"
      class="hidden"
      @change="onInputChange"
    />

    <!-- Preview State -->
    <div
      v-if="previewUrl"
      class="relative w-full h-44 sm:h-52 rounded-m3-md border-2 border-primary/40 bg-surface-container-low overflow-hidden flex items-center justify-center group"
    >
      <img
        :src="previewUrl"
        alt="Foto Barang"
        class="w-full h-full object-contain p-2"
      />

      <!-- Overlay Action Bar -->
      <div class="absolute inset-0 bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-2">
        <button
          type="button"
          @click="triggerFileInput"
          class="px-3 py-1.5 rounded-full bg-white text-surface-foreground text-xs font-bold shadow-md hover:bg-surface-container flex items-center gap-1 cursor-pointer"
        >
          <span class="material-symbols-outlined text-[16px]">edit</span>
          <span>Ganti Foto</span>
        </button>

        <button
          type="button"
          @click="removePhoto"
          class="px-3 py-1.5 rounded-full bg-error text-white text-xs font-bold shadow-md hover:bg-red-700 flex items-center gap-1 cursor-pointer"
        >
          <span class="material-symbols-outlined text-[16px]">delete</span>
          <span>Hapus</span>
        </button>
      </div>

      <!-- File Size Badge -->
      <div
        v-if="fileSizeKb"
        class="absolute bottom-2 left-2 px-2.5 py-0.5 rounded-full bg-black/70 text-white text-[10px] font-mono flex items-center gap-1"
      >
        <span class="material-symbols-outlined text-[12px] text-emerald-400">check_circle</span>
        <span>Ukuran: {{ fileSizeKb }} KB (Auto-compressed &lt; 1MB)</span>
      </div>
    </div>

    <!-- Drag & Drop Upload Box -->
    <div
      v-else
      @click="triggerFileInput"
      @dragover.prevent="isDragging = true"
      @dragleave.prevent="isDragging = false"
      @drop.prevent="onDrop"
      class="w-full h-36 sm:h-40 rounded-m3-md border-2 border-dashed transition-all flex flex-col items-center justify-center p-4 text-center cursor-pointer select-none"
      :class="[
        isDragging
          ? 'border-primary bg-primary-container/30 scale-[0.99]'
          : 'border-outline-variant hover:border-primary hover:bg-surface-container-low/60 bg-surface-container-lowest'
      ]"
    >
      <div v-if="isCompressing" class="flex flex-col items-center gap-2">
        <svg class="animate-spin h-6 w-6 text-primary" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
          <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
          <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
        </svg>
        <span class="text-xs font-semibold text-primary">Mengompresi foto &lt; 1MB...</span>
      </div>

      <template v-else>
        <div class="w-10 h-10 rounded-full bg-primary-container text-primary flex items-center justify-center mb-2">
          <span class="material-symbols-outlined text-[24px]">cloud_upload</span>
        </div>
        <div class="text-xs font-bold text-surface-foreground">
          Klik atau Drag & Drop foto barang ke sini
        </div>
        <p class="text-[11px] text-surface-on-variant mt-0.5">
          Mendukung file JPG, PNG, WEBP (Otomatis dikompresi sistem di bawah 1MB)
        </p>
      </template>
    </div>
  </div>
</template>
