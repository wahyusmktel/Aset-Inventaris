<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import M3Button from '@/Components/M3Button.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  pact: {
    type: Object,
    default: null,
  },
  school: {
    type: Object,
    default: null,
  },
  hasSigned: {
    type: Boolean,
    default: false,
  },
});

const form = useForm({
  is_agreed: false,
});

const isSubmitting = ref(false);

const handleSignPact = () => {
  form.post(route('integrity-pact.store'), {
    onStart: () => { isSubmitting.value = true; },
    onFinish: () => { isSubmitting.value = false; },
  });
};

const handleDownloadPdf = () => {
  window.location.href = route('integrity-pact.download');
};

const formatDate = (dateStr) => {
  if (!dateStr) return '-';
  const d = new Date(dateStr);
  return d.toLocaleDateString('id-ID', {
    day: 'numeric',
    month: 'long',
    year: 'numeric',
    hour: '2-digit',
    minute: '2-digit',
  });
};
</script>

<template>
  <Head title="Pakta Integritas Tim Pendataan - SMK Telkom Lampung" />

  <AuthenticatedLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      
      <!-- Top Notice Banner for Unsigned Members -->
      <div
        v-if="!hasSigned"
        class="bg-amber-50 border-l-4 border-amber-500 p-4 rounded-m3-md shadow-xs flex items-start gap-3"
      >
        <span class="material-symbols-outlined text-[24px] text-amber-600 shrink-0">verified_user</span>
        <div class="text-xs text-amber-900 leading-relaxed">
          <strong class="font-bold block text-sm">Pemberitahuan Wajib: Tanda Tangan Pakta Integritas</strong>
          Sebagai anggota tim pendataan dan inventarisasi aset sekolah, Anda wajib menyetujui dan menandatangani Pakta Integritas secara digital sebelum dapat melakukan pencatatan barang.
        </div>
      </div>

      <!-- Signed Success Header Banner -->
      <div
        v-else
        class="bg-emerald-50 border-l-4 border-emerald-600 p-4 rounded-m3-md shadow-xs flex items-center justify-between gap-4 flex-wrap"
      >
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-[22px]">verified</span>
          </div>
          <div>
            <span class="text-xs font-bold text-emerald-800 uppercase tracking-wide">Status: Terverifikasi & Sah</span>
            <div class="text-xs text-emerald-950 font-medium">
              Pakta Integritas telah ditandatangani pada {{ formatDate(pact?.signed_at) }} WIB
            </div>
          </div>
        </div>

        <button
          type="button"
          @click="handleDownloadPdf"
          class="h-10 px-4 rounded-m3-full bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold inline-flex items-center gap-2 shadow-xs transition-all cursor-pointer"
        >
          <span class="material-symbols-outlined text-[18px]">download</span>
          <span>Unduh Dokumen PDF</span>
        </button>
      </div>

      <!-- Official Document Layout Container -->
      <div class="bg-surface-container-lowest rounded-m3-xl p-6 sm:p-10 border border-outline-variant/40 shadow-sm space-y-6">
        
        <!-- Official Document Header (Kop Dokumen) -->
        <div class="text-center pb-4 border-b-2 border-surface-foreground/80 space-y-1">
          <h2 class="text-sm sm:text-base font-bold text-surface-foreground uppercase tracking-wider">
            Yayasan Pendidikan Telkom
          </h2>
          <h3 class="text-lg sm:text-xl font-extrabold text-primary uppercase">
            {{ school ? school.name : 'SMK TELKOM LAMPUNG' }}
          </h3>
          <p class="text-[11px] text-surface-on-variant italic">
            {{ school ? school.address : 'Jl. Jenderal Sudirman No. 88, Pringsewu, Lampung' }}
          </p>
        </div>

        <!-- Title of Legal Document -->
        <div class="text-center space-y-1">
          <h4 class="text-base sm:text-lg font-bold text-surface-foreground uppercase underline decoration-2 underline-offset-4">
            Surat Pernyataan & Pakta Integritas
          </h4>
          <p class="text-xs font-mono text-surface-on-variant">
            Nomor: {{ pact ? pact.document_number : 'DRAFT/PI-INV/SMKTELKOM/' + new Date().getFullYear() }}
          </p>
        </div>

        <p class="text-xs text-surface-foreground font-medium">
          Yang bertanda tangan di bawah ini:
        </p>

        <!-- Signer Identity Table -->
        <div class="bg-surface-container-low/60 rounded-m3-lg p-4 border border-outline-variant/30 text-xs">
          <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
            <div>
              <span class="text-surface-on-variant block text-[11px]">Nama Lengkap Petugas:</span>
              <span class="font-bold text-surface-foreground text-sm">{{ $page.props.auth.user.name }}</span>
            </div>
            <div>
              <span class="text-surface-on-variant block text-[11px]">NIP / ID Petugas:</span>
              <span class="font-bold text-surface-foreground">{{ $page.props.auth.user.nip || '-' }}</span>
            </div>
            <div>
              <span class="text-surface-on-variant block text-[11px]">Email Resmi:</span>
              <span class="font-medium text-surface-foreground">{{ $page.props.auth.user.email }}</span>
            </div>
            <div>
              <span class="text-surface-on-variant block text-[11px]">Unit Kerja / Sekolah:</span>
              <span class="font-bold text-primary">{{ school ? school.name : 'SMK Telkom Lampung' }}</span>
            </div>
          </div>
        </div>

        <!-- Pakta Integritas Clauses -->
        <div class="space-y-3 text-xs text-surface-foreground leading-relaxed text-justify">
          <p>Dengan ini menyatakan dengan sadar, sungguh-sungguh, dan penuh rasa tanggung jawab bahwa saya berjanji:</p>

          <ol class="list-decimal pl-5 space-y-2 text-surface-on-variant">
            <li>
              <strong class="text-surface-foreground">Integritas & Kejujuran:</strong> 
              Melaksanakan seluruh tugas pendataan dan inventarisasi fisik barang/aset sekolah dengan jujur, tertib, cermat, transparan, dan penuh rasa tanggung jawab.
            </li>
            <li>
              <strong class="text-surface-foreground">Faktual & Tanpa Manipulasi:</strong> 
              Melaporkan seluruh kondisi fisik barang secara faktual (Kondisi Baik maupun Rusak) sesuai kenyataan di lapangan tanpa melakukan perubahan, rekayasa, atau manipulasi data dalam bentuk apa pun.
            </li>
            <li>
              <strong class="text-surface-foreground">Kerahasiaan Data:</strong> 
              Menjaga kerahasiaan data inventaris dan tidak menyalahgunakan informasi atau kewenangan pendataan aset untuk kepentingan pribadi maupun pihak ketiga.
            </li>
            <li>
              <strong class="text-surface-foreground">Pemeliharaan & Keamanan:</strong> 
              Menjaga dan memelihara keutuhan barang-barang sekolah yang sedang didata serta segera melaporkan kepada Koordinator Sarpras / PIC Aset apabila menemukan potensi kehilangan atau kerusakan.
            </li>
            <li>
              <strong class="text-surface-foreground">Kepatuhan Hukum:</strong> 
              Bersedia menerima sanksi administratif, sanksi disiplin, dan/atau tuntutan hukum sesuai dengan peraturan perundang-undangan yang berlaku apabila saya terbukti melanggar isi Pakta Integritas ini.
            </li>
          </ol>
        </div>

        <!-- Signature Area -->
        <div class="pt-4 border-t border-outline-variant/30">
          
          <!-- State 1: Form Signing Checklist (If Not Signed Yet) -->
          <div v-if="!hasSigned" class="space-y-4 bg-primary/5 p-5 rounded-m3-lg border border-primary/20">
            <div class="flex items-start gap-3">
              <input
                type="checkbox"
                id="agree_checkbox"
                v-model="form.is_agreed"
                class="w-5 h-5 rounded text-primary focus:ring-primary/40 mt-0.5 cursor-pointer"
              />
              <label for="agree_checkbox" class="text-xs font-semibold text-surface-foreground cursor-pointer select-none leading-relaxed">
                Saya dengan sadar, jujur, dan sungguh-sungguh menyetujui seluruh klausul pakta integritas di atas sebagai petugas tim pendataan inventaris barang pada {{ school ? school.name : 'SMK Telkom Lampung' }}.
              </label>
            </div>

            <div v-if="form.errors.is_agreed" class="text-xs text-error font-semibold">
              {{ form.errors.is_agreed }}
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
              <M3Button
                type="button"
                variant="filled"
                size="large"
                icon="draw"
                :disabled="!form.is_agreed || form.processing"
                :loading="form.processing"
                @click="handleSignPact"
              >
                Tanda Tangani Pakta Integritas & Terbitkan PDF
              </M3Button>
            </div>
          </div>

          <!-- State 2: Already Signed Digital Verification Card -->
          <div v-else class="flex flex-col sm:flex-row items-center justify-between gap-4 p-5 rounded-m3-lg bg-emerald-50/70 border border-emerald-200">
            <div class="space-y-1 text-xs">
              <div class="font-bold text-emerald-900 flex items-center gap-1.5">
                <span class="material-symbols-outlined text-[18px] text-emerald-700">verified_user</span>
                <span>Tanda Tangan Digital Sah (Digital Verification)</span>
              </div>
              <div class="text-[11px] text-emerald-800">
                Tertanda: <strong>{{ $page.props.auth.user.name }}</strong> (NIP: {{ $page.props.auth.user.nip || '-' }})
              </div>
              <div class="text-[10px] font-mono text-emerald-700/90 break-all">
                Hash SHA-256: {{ pact?.digital_signature_hash }}
              </div>
            </div>

            <button
              type="button"
              @click="handleDownloadPdf"
              class="h-10 px-4 rounded-m3-full bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold inline-flex items-center gap-2 shadow-xs transition-all cursor-pointer shrink-0"
            >
              <span class="material-symbols-outlined text-[18px]">download</span>
              <span>Unduh Arsip PDF</span>
            </button>
          </div>

        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>
