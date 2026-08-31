<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import M3Button from '@/Components/M3Button.vue';
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';

const props = defineProps({
  finalization: {
    type: Object,
    default: null,
  },
  period: {
    type: Object,
    default: null,
  },
  school: {
    type: Object,
    default: null,
  },
  isCutoff: {
    type: Boolean,
    default: false,
  },
  hasFinalized: {
    type: Boolean,
    default: false,
  },
  statistics: {
    type: Object,
    default: () => ({
      total_items: 0,
      total_units: 0,
      total_good: 0,
      total_damaged: 0,
    }),
  },
});

const form = useForm({
  confirm_statement: false,
  statement_notes: '',
});

const isSubmitting = ref(false);

const handleFinalize = () => {
  form.post(route('data-finalization.store'), {
    onStart: () => { isSubmitting.value = true; },
    onFinish: () => { isSubmitting.value = false; },
  });
};

const handleDownloadPdf = () => {
  window.location.href = route('data-finalization.download');
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
  <Head title="Finalisasi Data & Berita Acara - SMK Telkom Lampung" />

  <AuthenticatedLayout>
    <div class="max-w-4xl mx-auto space-y-6">
      
      <!-- Cutoff / Finalization Status Banner -->
      <div
        v-if="isCutoff && !hasFinalized"
        class="bg-red-50 border-l-4 border-error p-4 rounded-m3-md shadow-xs flex items-start gap-3"
      >
        <span class="material-symbols-outlined text-[24px] text-error shrink-0">timer_off</span>
        <div class="text-xs text-red-900 leading-relaxed">
          <strong class="font-bold block text-sm">Batas Waktu Cut-off Telah Berakhir!</strong>
          Periode pendataan inventaris telah melewati batas waktu yang ditetapkan. Fitur penambahan dan perubahan data barang telah dinonaktifkan secara otomatis. Silakan lakukan finalisasi data untuk menerbitkan Berita Acara resmi.
        </div>
      </div>

      <!-- Finalized Success Header Banner -->
      <div
        v-else-if="hasFinalized"
        class="bg-emerald-50 border-l-4 border-emerald-600 p-4 rounded-m3-md shadow-xs flex items-center justify-between gap-4 flex-wrap"
      >
        <div class="flex items-center gap-3">
          <div class="w-10 h-10 rounded-full bg-emerald-100 text-emerald-700 flex items-center justify-center shrink-0">
            <span class="material-symbols-outlined text-[22px]">lock</span>
          </div>
          <div>
            <span class="text-xs font-bold text-emerald-800 uppercase tracking-wide">Data Inventaris Telah Dikunci (Final)</span>
            <div class="text-xs text-emerald-950 font-medium">
              Berita Acara No. {{ finalization?.document_number }} telah disahkan pada {{ formatDate(finalization?.signed_at) }} WIB
            </div>
          </div>
        </div>

        <button
          type="button"
          @click="handleDownloadPdf"
          class="h-10 px-4 rounded-m3-full bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold inline-flex items-center gap-2 shadow-xs transition-all cursor-pointer"
        >
          <span class="material-symbols-outlined text-[18px]">download</span>
          <span>Unduh Berita Acara PDF</span>
        </button>
      </div>

      <!-- Main Review & Finalization Card -->
      <div class="bg-surface-container-lowest rounded-m3-xl p-6 sm:p-10 border border-outline-variant/40 shadow-sm space-y-6">
        
        <!-- Header Info -->
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

        <div class="text-center space-y-1">
          <h4 class="text-base sm:text-lg font-bold text-surface-foreground uppercase underline decoration-2 underline-offset-4">
            Berita Acara Penyelesaian & Finalisasi Pendataan
          </h4>
          <p class="text-xs font-mono text-surface-on-variant">
            Nomor: {{ finalization ? finalization.document_number : 'DRAFT/BA-FIN/INV/' + new Date().getFullYear() }}
          </p>
        </div>

        <!-- Summary Statistics Grid -->
        <div class="space-y-2">
          <h5 class="text-xs font-bold text-surface-foreground">
            Rekapitulasi Fisik Aset yang Selesai Didata:
          </h5>

          <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
            <div class="p-3.5 rounded-m3-md bg-surface-container border border-outline-variant/30 text-center">
              <span class="text-[10px] text-surface-on-variant font-medium block">Total Varian Barang</span>
              <span class="text-lg font-black text-surface-foreground mt-0.5 block">
                {{ hasFinalized ? finalization.total_items_recorded : statistics.total_items }} Item
              </span>
            </div>

            <div class="p-3.5 rounded-m3-md bg-primary-container/40 border border-primary/20 text-center">
              <span class="text-[10px] text-primary font-medium block">Total Kuantitas Fisik</span>
              <span class="text-lg font-black text-primary mt-0.5 block">
                {{ hasFinalized ? finalization.total_units_recorded : statistics.total_units }} Unit
              </span>
            </div>

            <div class="p-3.5 rounded-m3-md bg-emerald-50 border border-emerald-200 text-center">
              <span class="text-[10px] text-emerald-700 font-medium block">Kondisi Baik</span>
              <span class="text-lg font-black text-emerald-800 mt-0.5 block">
                {{ hasFinalized ? finalization.total_good_condition : statistics.total_good }} Unit
              </span>
            </div>

            <div class="p-3.5 rounded-m3-md bg-red-50 border border-red-200 text-center">
              <span class="text-[10px] text-error font-medium block">Kondisi Rusak</span>
              <span class="text-lg font-black text-error mt-0.5 block">
                {{ hasFinalized ? finalization.total_damaged_condition : statistics.total_damaged }} Unit
              </span>
            </div>
          </div>
        </div>

        <!-- Legal Statement Text -->
        <div class="text-xs text-surface-foreground leading-relaxed text-justify space-y-2 bg-surface-container-low/50 p-4 rounded-m3-md border border-outline-variant/30">
          <p>
            Dengan menandatangani Berita Acara ini, saya menyatakan bahwa proses inventarisasi dan pendataan fisik barang sarana prasarana sekolah telah 
            <strong>SELESAI</strong> dan diverifikasi sesuai dengan kondisi lapangan nyata.
          </p>
          <p>
            Setelah finalisasi disahkan, data barang akan <strong>DIKUNCI</strong> dari penambahan, pengubahan, atau penghapusan demi menjaga integritas pembukuan aset sekolah.
          </p>
        </div>

        <!-- 3 Official Signatures Box Preview -->
        <div class="space-y-3 pt-2">
          <h5 class="text-xs font-bold text-surface-foreground text-center">
            Pihak Penandatangan Berita Acara Resmi:
          </h5>

          <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center text-xs">
            <!-- 1. Petugas Pendata -->
            <div class="p-4 rounded-m3-md bg-surface-container-low border border-outline-variant/30 space-y-2">
              <span class="text-[10px] text-surface-on-variant font-semibold block uppercase">1. Petugas Pendata (Anggota)</span>
              <div class="h-16 flex items-center justify-center">
                <span class="px-2.5 py-1 rounded bg-primary/10 text-primary text-[10px] font-bold">
                  {{ hasFinalized ? '✓ TERVERIFIKASI' : 'Menunggu Finalisasi' }}
                </span>
              </div>
              <div>
                <strong class="text-surface-foreground block text-xs underline">{{ $page.props.auth.user.name }}</strong>
                <span class="text-[10px] text-surface-on-variant">NIP: {{ $page.props.auth.user.nip || '-' }}</span>
              </div>
            </div>

            <!-- 2. Kaur IT / PIC Aset -->
            <div class="p-4 rounded-m3-md bg-surface-container-low border border-outline-variant/30 space-y-2">
              <span class="text-[10px] text-surface-on-variant font-semibold block uppercase">2. Kaur IT / PIC Aset</span>
              <div class="h-16 flex items-center justify-center">
                <span class="text-[11px] text-surface-on-variant italic">Mengetahui</span>
              </div>
              <div>
                <strong class="text-surface-foreground block text-xs underline">
                  {{ school && school.kaur_it_name ? school.kaur_it_name : 'Rizky Pratama, S.Kom., M.T.' }}
                </strong>
                <span class="text-[10px] text-surface-on-variant">
                  NIP: {{ school && school.kaur_it_nip ? school.kaur_it_nip : '19881210 201402 1 005' }}
                </span>
              </div>
            </div>

            <!-- 3. Kepala Sekolah -->
            <div class="p-4 rounded-m3-md bg-surface-container-low border border-outline-variant/30 space-y-2">
              <span class="text-[10px] text-surface-on-variant font-semibold block uppercase">3. Kepala Sekolah</span>
              <div class="h-16 flex items-center justify-center">
                <span class="text-[11px] text-surface-on-variant italic">Menyetujui</span>
              </div>
              <div>
                <strong class="text-surface-foreground block text-xs underline">
                  {{ school && school.principal_name ? school.principal_name : 'Drs. H. Bambang Subagyo, M.Kom.' }}
                </strong>
                <span class="text-[10px] text-surface-on-variant">
                  NIP: {{ school && school.principal_nip ? school.principal_nip : '19750815 199903 1 002' }}
                </span>
              </div>
            </div>
          </div>
        </div>

        <!-- Action Form: Finalize or Download PDF -->
        <div class="pt-4 border-t border-outline-variant/30">
          <!-- State 1: Finalization Form -->
          <div v-if="!hasFinalized" class="space-y-4 bg-primary/5 p-5 rounded-m3-lg border border-primary/20">
            <!-- Notes -->
            <div>
              <label class="block text-xs font-semibold text-surface-foreground mb-1">
                Catatan / Keterangan Berita Acara (Opsional)
              </label>
              <textarea
                v-model="form.statement_notes"
                rows="2"
                placeholder="Contoh: Seluruh barang lab komputer dan kelas telah diverifikasi fisik lengkap..."
                class="w-full p-3 text-xs rounded-m3-xs border border-outline focus:border-primary focus:ring-0 bg-transparent text-surface-foreground placeholder:text-surface-on-variant"
              ></textarea>
            </div>

            <!-- Checklist -->
            <div class="flex items-start gap-3">
              <input
                type="checkbox"
                id="confirm_checkbox"
                v-model="form.confirm_statement"
                class="w-5 h-5 rounded text-primary focus:ring-primary/40 mt-0.5 cursor-pointer"
              />
              <label for="confirm_checkbox" class="text-xs font-semibold text-surface-foreground cursor-pointer select-none leading-relaxed">
                Saya mengonfirmasi bahwa seluruh hasil pendataan inventaris barang telah valid dan siap untuk dikunci serta diterbitkan Berita Acara Finalisasi resmi.
              </label>
            </div>

            <div v-if="form.errors.confirm_statement" class="text-xs text-error font-semibold">
              {{ form.errors.confirm_statement }}
            </div>

            <div class="flex items-center justify-end gap-3 pt-2">
              <M3Button
                type="button"
                variant="filled"
                size="large"
                icon="lock"
                :disabled="!form.confirm_statement || form.processing"
                :loading="form.processing"
                @click="handleFinalize"
              >
                Kunci Data & Terbitkan Berita Acara PDF
              </M3Button>
            </div>
          </div>

          <!-- State 2: Already Finalized Action -->
          <div v-else class="flex flex-col sm:flex-row items-center justify-between gap-4 p-5 rounded-m3-lg bg-emerald-50/70 border border-emerald-200">
            <div class="text-xs text-emerald-900">
              <strong>Data telah dikunci permanen untuk periode ini.</strong> Anda dapat mengunduh ulang salinan Berita Acara PDF resmi kapan saja.
            </div>

            <button
              type="button"
              @click="handleDownloadPdf"
              class="h-10 px-4 rounded-m3-full bg-emerald-700 hover:bg-emerald-800 text-white text-xs font-bold inline-flex items-center gap-2 shadow-xs transition-all cursor-pointer shrink-0"
            >
              <span class="material-symbols-outlined text-[18px]">download</span>
              <span>Unduh Berita Acara PDF</span>
            </button>
          </div>
        </div>

      </div>
    </div>
  </AuthenticatedLayout>
</template>
