const puppeteer = require('puppeteer');
const fs = require('fs');
const path = require('path');

const ARTIFACT_DIR = 'C:\\Users\\TJ\\.gemini\\antigravity\\brain\\38551030-7caf-4c5b-b116-c6f8af356632';
const OUTPUT_PDF_PROJECT = path.join(__dirname, 'Panduan_Pendataan_Inventaris_SMK_Telkom_Lampung.pdf');
const OUTPUT_PDF_ARTIFACT = path.join(ARTIFACT_DIR, 'Panduan_Pendataan_Inventaris_SMK_Telkom_Lampung.pdf');

function getBase64Image(filePath) {
  if (fs.existsSync(filePath)) {
    const bitmap = fs.readFileSync(filePath);
    const ext = path.extname(filePath).replace('.', '');
    return `data:image/${ext};base64,${bitmap.toString('base64')}`;
  }
  return '';
}

const logoBase64 = getBase64Image(path.join(__dirname, 'public/images/telkom-schools-logo.png'));
const imgLogin = getBase64Image(path.join(ARTIFACT_DIR, 'screenshot_login.png'));
const imgDashboard = getBase64Image(path.join(ARTIFACT_DIR, 'screenshot_dashboard.png'));
const imgInventory = getBase64Image(path.join(ARTIFACT_DIR, 'screenshot_inventory.png'));
const imgModal = getBase64Image(path.join(ARTIFACT_DIR, 'screenshot_modal_input.png'));
const imgPakta = getBase64Image(path.join(ARTIFACT_DIR, 'screenshot_pakta_integritas.png'));

const htmlContent = `
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <title>Buku Panduan Pendataan Inventaris - SMK Telkom Lampung</title>
  <style>
    @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=Inter:wght@400;500;600;700&display=swap');

    @page {
      size: A4;
      margin: 18mm 16mm 18mm 16mm;
      @bottom-right {
        content: counter(page);
      }
    }

    * {
      box-sizing: border-box;
      margin: 0;
      padding: 0;
    }

    body {
      font-family: 'Plus Jakarta Sans', 'Inter', sans-serif;
      color: #0F172A;
      background: #FFFFFF;
      font-size: 10.5pt;
      line-height: 1.6;
    }

    .page-break {
      page-break-before: always;
    }

    /* Cover Page */
    .cover-container {
      height: 100vh;
      display: flex;
      flex-direction: column;
      justify-content: space-between;
      padding: 20px 0;
    }

    .cover-header {
      display: flex;
      align-items: center;
      gap: 16px;
      border-bottom: 2px solid #E2E8F0;
      padding-bottom: 16px;
    }

    .cover-logo {
      height: 60px;
      object-fit: contain;
    }

    .cover-brand-text h3 {
      font-size: 13pt;
      font-weight: 800;
      color: #E52320;
      letter-spacing: 0.5px;
    }

    .cover-brand-text p {
      font-size: 9pt;
      color: #64748B;
      font-weight: 600;
    }

    .cover-hero {
      margin: 40px 0;
      background: linear-gradient(135deg, #E52320 0%, #B91C1C 60%, #991B1B 100%);
      padding: 40px 32px;
      border-radius: 20px;
      color: #FFFFFF;
      box-shadow: 0 10px 25px rgba(229, 35, 32, 0.2);
    }

    .cover-badge {
      display: inline-block;
      background: rgba(255, 255, 255, 0.2);
      color: #FFFFFF;
      font-size: 9pt;
      font-weight: 700;
      padding: 6px 14px;
      border-radius: 30px;
      margin-bottom: 16px;
      text-transform: uppercase;
      letter-spacing: 1px;
    }

    .cover-title {
      font-size: 24pt;
      font-weight: 800;
      line-height: 1.25;
      margin-bottom: 12px;
    }

    .cover-subtitle {
      font-size: 12pt;
      font-weight: 500;
      color: rgba(255, 255, 255, 0.9);
      line-height: 1.5;
    }

    .cover-meta {
      background: #F8FAFC;
      border: 1px solid #E2E8F0;
      border-radius: 16px;
      padding: 20px 24px;
    }

    .meta-grid {
      display: grid;
      grid-template-columns: repeat(2, 1fr);
      gap: 12px;
    }

    .meta-item {
      font-size: 9.5pt;
    }

    .meta-item span.label {
      display: block;
      color: #64748B;
      font-weight: 600;
      font-size: 8.5pt;
      text-transform: uppercase;
    }

    .meta-item span.value {
      color: #0F172A;
      font-weight: 700;
    }

    /* Content Styling */
    .chapter-header {
      border-bottom: 2.5px solid #E52320;
      padding-bottom: 8px;
      margin-bottom: 18px;
      margin-top: 10px;
      display: flex;
      justify-content: space-between;
      align-items: flex-end;
    }

    .chapter-title {
      font-size: 15pt;
      font-weight: 800;
      color: #0F172A;
    }

    .chapter-badge {
      font-size: 9pt;
      font-weight: 700;
      color: #E52320;
      background: #FEE2E2;
      padding: 3px 10px;
      border-radius: 12px;
    }

    h3 {
      font-size: 12pt;
      font-weight: 700;
      color: #E52320;
      margin: 16px 0 8px 0;
    }

    p {
      margin-bottom: 10px;
      color: #334155;
      text-align: justify;
    }

    .step-box {
      background: #FFFFFF;
      border: 1px solid #E2E8F0;
      border-left: 4px solid #E52320;
      border-radius: 12px;
      padding: 14px 18px;
      margin: 14px 0;
      box-shadow: 0 2px 6px rgba(0,0,0,0.02);
    }

    .step-box.info {
      border-left-color: #0284C7;
      background: #F0F9FF;
    }

    .step-box.success {
      border-left-color: #10B981;
      background: #F0FDF4;
    }

    .step-box.warning {
      border-left-color: #F59E0B;
      background: #FFFBEB;
    }

    .step-title {
      font-size: 11pt;
      font-weight: 700;
      color: #0F172A;
      margin-bottom: 4px;
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .step-number {
      background: #E52320;
      color: #FFFFFF;
      width: 22px;
      height: 22px;
      border-radius: 50%;
      display: inline-flex;
      align-items: center;
      justify-content: center;
      font-size: 9pt;
      font-weight: 800;
    }

    .step-desc {
      font-size: 9.5pt;
      color: #475569;
    }

    .img-container {
      margin: 14px 0;
      text-align: center;
      background: #F8FAFC;
      padding: 8px;
      border: 1px solid #E2E8F0;
      border-radius: 14px;
    }

    .img-container img {
      max-width: 100%;
      height: auto;
      max-height: 240px;
      border-radius: 8px;
      box-shadow: 0 4px 12px rgba(0,0,0,0.06);
      object-fit: contain;
    }

    .img-caption {
      font-size: 8.5pt;
      color: #64748B;
      font-weight: 600;
      margin-top: 6px;
      font-style: italic;
    }

    /* Table */
    table {
      width: 100%;
      border-collapse: collapse;
      margin: 14px 0;
      font-size: 9pt;
    }

    th {
      background: #E52320;
      color: #FFFFFF;
      font-weight: 700;
      text-align: left;
      padding: 8px 10px;
    }

    td {
      padding: 8px 10px;
      border-bottom: 1px solid #E2E8F0;
      color: #334155;
    }

    tr:nth-child(even) td {
      background: #F8FAFC;
    }

    .badge-good {
      background: #D1FAE5;
      color: #065F46;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 8px;
      font-size: 8pt;
    }

    .badge-bad {
      background: #FEE2E2;
      color: #991B1B;
      font-weight: 700;
      padding: 2px 8px;
      border-radius: 8px;
      font-size: 8pt;
    }

    ul, ol {
      margin-left: 20px;
      margin-bottom: 12px;
      color: #334155;
      font-size: 9.5pt;
    }

    li {
      margin-bottom: 4px;
    }

    .toc-item {
      display: flex;
      justify-content: space-between;
      padding: 8px 0;
      border-bottom: 1px dashed #CBD5E1;
      font-size: 10pt;
    }

    .toc-title {
      font-weight: 600;
      color: #0F172A;
    }

    .toc-dots {
      color: #94A3B8;
    }

    .toc-page {
      font-weight: 700;
      color: #E52320;
    }
  </style>
</head>
<body>

  <!-- COVER PAGE -->
  <div class="cover-container">
    <div class="cover-header">
      <img src="${logoBase64}" class="cover-logo" alt="Telkom Schools">
      <div class="cover-brand-text">
        <h3>SMK TELKOM LAMPUNG</h3>
        <p>Yayasan Pendidikan Telkom • Sistem Informasi Manajemen Aset & Inventaris</p>
      </div>
    </div>

    <div class="cover-hero">
      <div class="cover-badge">Buku Panduan Operasional</div>
      <h1 class="cover-title">PANDUAN PENDATAAN INVENTARIS BARANG</h1>
      <p class="cover-subtitle">Petunjuk Teknis Lengkap bagi Petugas & Anggota Tim Surveyor Pendataan Aset Sekolah Berbasis Web & Mobile Android.</p>
    </div>

    <div class="cover-meta">
      <div class="meta-grid">
        <div class="meta-item">
          <span class="label">Penyusun Dokumen</span>
          <span class="value">Tim Pengelola Sarpras & IT SMK Telkom Lampung</span>
        </div>
        <div class="meta-item">
          <span class="label">Target Pengguna</span>
          <span class="value">Role Anggota (Tim Surveyor Lapangan)</span>
        </div>
        <div class="meta-item">
          <span class="label">Alamat Portal Web</span>
          <span class="value">https://aset.smktelkom-lpg.id</span>
        </div>
        <div class="meta-item">
          <span class="label">Versi & Tahun Ajaran</span>
          <span class="value">Versi 1.0 (T.A. 2026/2027)</span>
        </div>
      </div>
    </div>
  </div>

  <!-- PAGE BREAK: DAFTAR ISI & KETENTUAN UMUM -->
  <div class="page-break"></div>

  <div class="chapter-header">
    <div class="chapter-title">DAFTAR ISI PANDUAN</div>
    <div class="chapter-badge">Ikhtisar Dokumen</div>
  </div>

  <div style="margin-bottom: 24px;">
    <div class="toc-item"><span class="toc-title">1. Ketentuan Umum & Tanggung Jawab Anggota</span><span class="toc-page">Hal. 2</span></div>
    <div class="toc-item"><span class="toc-title">2. Langkah 1: Masuk ke Aplikasi (Login)</span><span class="toc-page">Hal. 3</span></div>
    <div class="toc-item"><span class="toc-title">3. Langkah 2: Persetujuan Pakta Integritas Digital</span><span class="toc-page">Hal. 4</span></div>
    <div class="toc-item"><span class="toc-title">4. Langkah 3: Memahami Dashboard & Batas Waktu Cut-Off</span><span class="toc-page">Hal. 5</span></div>
    <div class="toc-item"><span class="toc-title">5. Langkah 4: Panduan Input Data Barang (Web & Mobile)</span><span class="toc-page">Hal. 6</span></div>
    <div class="toc-item"><span class="toc-title">6. Langkah 5: Penggunaan Aplikasi Mobile (Kamera & Barcode)</span><span class="toc-page">Hal. 7</span></div>
    <div class="toc-item"><span class="toc-title">7. Langkah 6: Mengelola & Memeriksa Detail Barang Sendiri</span><span class="toc-page">Hal. 8</span></div>
    <div class="toc-item"><span class="toc-title">8. Langkah 7: Ekspor Laporan Excel & Finalisasi Berita Acara</span><span class="toc-page">Hal. 9</span></div>
  </div>

  <div class="chapter-header">
    <div class="chapter-title">BAB 1: KETENTUAN UMUM PENDATAAN ASET</div>
    <div class="chapter-badge">Aturan Baku</div>
  </div>

  <p>Pendataan inventaris merupakan langkah awal strategis dalam mengidentifikasi, mengelompokkan, dan memvalidasi seluruh sarana dan prasarana fisik yang berada di lingkungan <strong>SMK Telkom Lampung</strong> untuk selanjutnya ditetapkan sebagai <strong>Aset Tetap Sekolah</strong> di bawah naungan <strong>Yayasan Pendidikan Telkom</strong>.</p>

  <div class="step-box info">
    <div class="step-title">📌 Hak Akses Khusus Role Anggota (Petugas Surveyor):</div>
    <div class="step-desc">
      <ul>
        <li><strong>Wajib Menandatangani Pakta Integritas:</strong> Akses ke menu inventaris baru terbuka setelah menandatangani pakta integritas digital.</li>
        <li><strong>Pencatatan Mandiri:</strong> Anggota dapat mencatat barang baru tanpa batasan jumlah pada seluruh ruangan/laboratorium yang ditugaskan.</li>
        <li><strong>Perlindungan Data (Ownership Guard):</strong> Anggota hanya berhak <em>mengedit</em> dan <em>menghapus</em> barang yang <strong>ia tambahkan sendiri</strong>. Barang yang diinput oleh petugas lain hanya dapat dilihat (<em>view-only</em>).</li>
        <li><strong>Kepatuhan Batas Cut-Off:</strong> Penginputan dan pengubahan data hanya dapat dilakukan selama periode aktif pendataan belum melewati batas waktu cut-off.</li>
      </ul>
    </div>
  </div>

  <!-- PAGE BREAK: LANGKAH 1 - LOGIN -->
  <div class="page-break"></div>

  <div class="chapter-header">
    <div class="chapter-title">BAB 2: LANGKAH 1 - MASUK KE APLIKASI (LOGIN)</div>
    <div class="chapter-badge">Langkah 1</div>
  </div>

  <p>Setiap petugas surveyor diberikan akun resmi oleh Administrator Utama (Super Admin). Ikuti langkah berikut untuk masuk ke portal:</p>

  <div class="step-box">
    <div class="step-title"><span class="step-number">1</span> Buka Alamat Website Portal Resmi</div>
    <div class="step-desc">Gunakan browser Chrome / Edge di komputer atau HP Anda, lalu akses: <strong>https://aset.smktelkom-lpg.id</strong></div>
  </div>

  <div class="step-box">
    <div class="step-title"><span class="step-number">2</span> Masukkan Kredensial Akun</div>
    <div class="step-desc">
      Ketikkan <strong>Alamat Email Resmi</strong> (misal: <code>anggota@smktelkom.sch.id</code>) dan <strong>Kata Sandi</strong> Anda. Klik tombol merah <strong>"Masuk ke Aplikasi"</strong>.
    </div>
  </div>

  <div class="img-container">
    <img src="${imgLogin}" alt="Tampilan Login">
    <div class="img-caption">Gambar 1: Halaman Portal Masuk Resmi SIM-ASET SMK Telkom Lampung</div>
  </div>

  <div class="step-box warning">
    <div class="step-title">⚠️ Catatan Keamanan:</div>
    <div class="step-desc">Jangan membagikan kata sandi Anda kepada siapa pun. Setiap data barang yang dicatat akan secara otomatis merekam nama dan akun Anda sebagai rekam jejak audit (<em>audit trail</em>).</div>
  </div>

  <!-- PAGE BREAK: LANGKAH 2 - PAKTA INTEGRITAS -->
  <div class="page-break"></div>

  <div class="chapter-header">
    <div class="chapter-title">BAB 3: LANGKAH 2 - PAKTA INTEGRITAS DIGITAL</div>
    <div class="chapter-badge">Langkah 2 (Wajib)</div>
  </div>

  <p>Saat pertama kali masuk ke sistem, sistem akan secara otomatis menampilkan halaman <strong>Surat Pernyataan & Pakta Integritas</strong>. Ini merupakan syarat mutlak sebelum Anda diizinkan mencatat barang inventaris.</p>

  <div class="img-container">
    <img src="${imgPakta}" alt="Pakta Integritas">
    <div class="img-caption">Gambar 2: Dokumen Pakta Integritas Digital Tim Pendataan Inventaris</div>
  </div>

  <div class="step-box success">
    <div class="step-title"><span class="step-number">1</span> Baca 5 Poin Komitmen Integritas</div>
    <div class="step-desc">Pahami kewajiban untuk melakukan pendataan secara jujur, mengecek fisik barang langsung di ruangan, tidak memanipulasi data, dan menjaga keutuhan sarana sekolah.</div>
  </div>

  <div class="step-box success">
    <div class="step-title"><span class="step-number">2</span> Centang Persetujuan & Simpan</div>
    <div class="step-desc">Centang kotak <em>"Saya telah membaca dan menyetujui seluruh isi Pakta Integritas..."</em> kemudian klik tombol <strong>"Simpan & Tanda Tangani Pakta Integritas"</strong>.</div>
  </div>

  <div class="step-box info">
    <div class="step-title"><span class="step-number">3</span> Unduh Dokumen PDF Resmi</div>
    <div class="step-desc">Setelah ditandatangani, Anda dapat mengklik tombol <strong>"Unduh Dokumen PDF"</strong> untuk mengunduh salinan berkas bertanda tangan digital resmi dengan hash keamanan SHA-256.</div>
  </div>

  <!-- PAGE BREAK: LANGKAH 3 - DASHBOARD & CUTOFF -->
  <div class="page-break"></div>

  <div class="chapter-header">
    <div class="chapter-title">BAB 4: LANGKAH 3 - DASHBOARD & HITUNG MUNDUR CUT-OFF</div>
    <div class="chapter-badge">Langkah 3</div>
  </div>

  <p>Halaman Beranda menyajikan ringkasan visual mengenai kondisi dan capaian pendataan seluruh sekolah secara langsung.</p>

  <div class="img-container">
    <img src="${imgDashboard}" alt="Dashboard Beranda">
    <div class="img-caption">Gambar 3: Tampilan Dashboard & Statistik Aset SMK Telkom Lampung</div>
  </div>

  <h3>Komponen Penting di Dashboard:</h3>
  <table style="margin-top: 10px;">
    <thead>
      <tr>
        <th style="width: 25%;">Komponen</th>
        <th>Fungsi & Penjelasan bagi Petugas</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>Hero Banner Merah</strong></td>
        <td>Menampilkan profil sekolah aktif <em>(SMK Telkom Lampung)</em>, nama Kepala Sekolah, dan tombol pintas <em>Kelola Inventaris</em>.</td>
      </tr>
      <tr>
        <td><strong>Card KPI Ringkasan</strong></td>
        <td>Menghitung total jenis barang, total kuantitas unit, persentase barang laik pakai (Kondisi Baik), serta titik lokasi ruangan.</td>
      </tr>
      <tr>
        <td><strong>Grafik Distribusi Kategori</strong></td>
        <td>Diagram batang horizontal yang menunjukkan kategori barang dengan jumlah aset terbanyak (misal: PC, Switch, Kabel FO).</td>
      </tr>
      <tr>
        <td><strong>Grafik Kondisi Laik Pakai</strong></td>
        <td>Diagram lingkaran (Doughnut) interaktif yang membandingkan proporsi barang dalam kondisi Baik vs Rusak.</td>
      </tr>
    </tbody>
  </table>

  <!-- PAGE BREAK: LANGKAH 4 - PANDUAN INPUT DATA BARANG -->
  <div class="page-break"></div>

  <div class="chapter-header">
    <div class="chapter-title">BAB 5: LANGKAH 4 - FORMULIR PENCATATAN BARANG</div>
    <div class="chapter-badge">Langkah 4 (Inti)</div>
  </div>

  <p>Untuk mencatat barang baru, buka menu <strong>Inventaris &gt; Inventaris Barang</strong>, lalu klik tombol merah <strong>"+ Catat Barang Baru"</strong>. Modal interaktif akan terbuka.</p>

  <div class="img-container">
    <img src="${imgModal}" alt="Modal Pencatatan Barang">
    <div class="img-caption">Gambar 4: Formulir Modal Interaktif Pencatatan Inventaris Barang Baru</div>
  </div>

  <h3>Panduan Pengisian Field Formulir:</h3>
  <table>
    <thead>
      <tr>
        <th style="width: 28%;">Nama Kolom</th>
        <th>Ketentuan & Contoh Pengisian yang Benar</th>
      </tr>
    </thead>
    <tbody>
      <tr>
        <td><strong>1. Nama Barang & Tipe *</strong></td>
        <td>Tuliskan nama barang lengkap beserta spesifikasi/tipe utamanya.<br><em>Contoh: PC Desktop Asus ROG Strix GT15 Core i7 16GB</em></td>
      </tr>
      <tr>
        <td><strong>2. Serial Number (SN)</strong></td>
        <td>Ketikkan nomor seri pabrik. Jika barang tidak memiliki SN (misal meja, proyektor lawas), <strong>centang kotak: <em>"Barang ini tidak memiliki Serial Number"</em></strong>.</td>
      </tr>
      <tr>
        <td><strong>3. Merk / Brand</strong></td>
        <td>Tuliskan merk pabrikan aset. <em>Contoh: ASUS, Cisco, Mikrotik, Epson, Daikin</em>.</td>
      </tr>
      <tr>
        <td><strong>4. Jumlah / Kuantitas *</strong></td>
        <td>Masukkan jumlah fisik barang dalam satuan unit (angka minimal 1).</td>
      </tr>
      <tr>
        <td><strong>5. Foto Dokumentasi</strong></td>
        <td>Unggah foto fisik barang (drag and drop atau pilih file). <strong>Sistem otomatis mengompresi ukuran file menjadi di bawah 1MB</strong>.</td>
      </tr>
      <tr>
        <td><strong>6. Kondisi Barang *</strong></td>
        <td>Pilih radiobutton: <span class="badge-good">1. Baik</span> jika normal berfungsi, atau <span class="badge-bad">2. Rusak</span> jika cacat/mati total.</td>
      </tr>
      <tr>
        <td><strong>7. Lokasi & Klasifikasi</strong></td>
        <td>Pilih dropdown: <strong>Kategori</strong>, <strong>Gedung</strong>, <strong>Ruangan / Lab</strong>, dan <strong>Fungsi Barang</strong> sesuai penempatan fisik aset.</td>
      </tr>
    </tbody>
  </table>

  <!-- PAGE BREAK: LANGKAH 5 - APLIKASI MOBILE FLUTTER -->
  <div class="page-break"></div>

  <div class="chapter-header">
    <div class="chapter-title">BAB 6: LANGKAH 5 - MENGGUNAKAN APLIKASI MOBILE ANDROID (APK)</div>
    <div class="chapter-badge">Mobile Lapangan</div>
  </div>

  <p>Untuk memudahkan pendataan langsung di laboratorium atau ruang kelas tanpa membawa laptop, gunakan aplikasi <strong>SIM-ASET Mobile (Android APK)</strong>.</p>

  <div class="step-box success">
    <div class="step-title">📱 Keunggulan Aplikasi Mobile:</div>
    <div class="step-desc">
      <ul>
        <li><strong>Foto Langsung dari Kamera HP:</strong> Ambil foto fisik aset langsung di depan perangkat, foto otomatis dikompresi di bawah 1MB secara instan sebelum diunggah.</li>
        <li><strong>Pemindai Barcode / QR Code:</strong> Scan barcode nomor seri barang secara cepat menggunakan kamera ponsel.</li>
        <li><strong>Sinkronisasi Real-Time:</strong> Data yang diinput di HP langsung masuk ke database server dan tampil di dashboard web.</li>
        <li><strong>Akses Server Fleksibel:</strong> Terhubung otomatis ke Cloudflare Tunnel <code>https://aset.smktelkom-lpg.id/api</code>.</li>
      </ul>
    </div>
  </div>

  <div class="step-box">
    <div class="step-title"><span class="step-number">1</span> Instalasi Berkas APK di HP Android</div>
    <div class="step-desc">Unduh berkas <code>SIM-ASET-Telkom.apk</code> yang dibagikan koordinator. Buka berkas dan pilih <em>Install / Izinkan Pemasangan Aplikasi</em>.</div>
  </div>

  <div class="step-box">
    <div class="step-title"><span class="step-number">2</span> Login & Mulai Audit Fisik</div>
    <div class="step-desc">Buka aplikasi mobile, masukkan email dan password Anda. Tap tab <strong>"Inventaris"</strong> dan tekan tombol bundar <strong>"+ Catat Barang"</strong>.</div>
  </div>

  <!-- PAGE BREAK: LANGKAH 6 - MENGELOLA DATA SENDIRI -->
  <div class="page-break"></div>

  <div class="chapter-header">
    <div class="chapter-title">BAB 7: LANGKAH 6 - MENGELOLA BARANG & HAK AKSES</div>
    <div class="chapter-badge">Aturan Akses</div>
  </div>

  <p>Pada tabel halaman <strong>Inventaris Barang</strong>, seluruh data yang dicatat oleh tim surveyor akan ditampilkan dalam bentuk tabel rapi dengan pagination 10 data per halaman.</p>

  <div class="img-container">
    <img src="${imgInventory}" alt="Tabel Inventaris Barang">
    <div class="img-caption">Gambar 5: Tabel Inventarisasi Barang dengan Filter, Hitung Mundur Cut-Off, dan Kolom Aksi</div>
  </div>

  <h3>Penjelasan Aksi pada Tabel:</h3>
  <div class="step-box">
    <div class="step-title">👁️ Ikon Mata (Detail Barang):</div>
    <div class="step-desc">Dapat diklik oleh siapa pun untuk melihat foto ukuran penuh, spesifikasi teknis, ruangan, serta identitas petugas yang mencatat barang.</div>
  </div>

  <div class="step-box warning">
    <div class="step-title">✏️ Ikon Pensil (Edit) & 🗑️ Ikon Sampah (Hapus):</div>
    <div class="step-desc">
      <ul>
        <li><strong>Barang Milik Anda:</strong> Ditandai dengan badge merah muda <code>Milik Saya</code>. Tombol Edit dan Hapus aktif dan dapat Anda gunakan jika terdapat kesalahan input.</li>
        <li><strong>Barang Petugas Lain:</strong> Tombol Edit dan Hapus dinonaktifkan / tidak muncul untuk menjaga integritas dan mencegah perubahan sepihak.</li>
      </ul>
    </div>
  </div>

  <!-- PAGE BREAK: LANGKAH 7 & 8 - EKSPOR & FINALISASI -->
  <div class="page-break"></div>

  <div class="chapter-header">
    <div class="chapter-title">BAB 8: LANGKAH 7 & 8 - EKSPOR EXCEL & FINALISASI DATA</div>
    <div class="chapter-badge">Laporan Akhir</div>
  </div>

  <h3>A. Ekspor Laporan Excel Berwarna:</h3>
  <p>Petugas dapat mengunduh rekapitulasi data sewaktu-waktu dengan mengklik tombol hijau <strong>"Export Excel"</strong> di atas tabel. Berkas Excel dirancang rapi dengan header merah Telkom, kolom nomor seri, kuantitas, ruangan, dan kondisi.</p>

  <h3>B. Finalisasi Pendataan & Penerbitan Berita Acara:</h3>
  <p>Setelah seluruh ruangan selesai diaudit secara fisik dan diverifikasi oleh koordinator, langkah terakhir adalah <strong>Finalisasi Data</strong> melalui menu <em>Finalisasi Data &amp; Berita Acara</em>.</p>

  <div class="step-box success">
    <div class="step-title">📑 Dokumen Berita Acara Serah Terima Aset Resmi:</div>
    <div class="step-desc">
      Setelah finalisasi, sistem mengunci data agar tidak dapat diubah lagi dan menerbitkan dokumen Berita Acara dengan <strong>3 Tanda Tangan Resmi Pengesahan</strong>:
      <ol style="margin-top: 6px;">
        <li><strong>Pihak 1:</strong> Petugas Pendata Lapangan (Nama & NIP Anda).</li>
        <li><strong>Pihak 2:</strong> Kaur IT / PIC Sarpras Sekolah (Rizky Pratama, S.Kom., M.T.).</li>
        <li><strong>Pihak 3:</strong> Kepala Sekolah SMK Telkom Lampung (Drs. H. Bambang Subagyo, M.Kom.).</li>
      </ol>
    </div>
  </div>

  <div class="step-box info" style="margin-top: 24px;">
    <div class="step-title">📞 Pusat Bantuan & Kontak Dukungan:</div>
    <div class="step-desc">
      Jika mengalami kendala teknis saat pendataan, silakan hubungi tim IT Sarpras SMK Telkom Lampung:<br>
      • <strong>Email PIC:</strong> sarpras@smktelkom-lpg.sch.id<br>
      • <strong>Alamat Kampus:</strong> Jl. Jenderal Sudirman No. 88, Pringsewu, Lampung.<br>
      • <strong>Portal Layanan:</strong> https://aset.smktelkom-lpg.id
    </div>
  </div>

</body>
</html>
`;

async function generatePDF() {
  console.log('Generating PDF manual...');
  const browser = await puppeteer.launch({
    executablePath: 'C:\\Program Files\\Google\\Chrome\\Application\\chrome.exe',
    headless: "new",
    args: ['--no-sandbox', '--disable-setuid-sandbox']
  });

  const page = await browser.newPage();
  await page.setContent(htmlContent, { waitUntil: 'networkidle0' });

  await page.pdf({
    path: OUTPUT_PDF_PROJECT,
    format: 'A4',
    printBackground: true,
    margin: {
      top: '12mm',
      bottom: '12mm',
      left: '12mm',
      right: '12mm'
    }
  });

  // Copy to artifact directory as well
  fs.copyFileSync(OUTPUT_PDF_PROJECT, OUTPUT_PDF_ARTIFACT);

  await browser.close();
  console.log('PDF Generated Successfully at:');
  console.log('1. Project Root: ' + OUTPUT_PDF_PROJECT);
  console.log('2. Artifact: ' + OUTPUT_PDF_ARTIFACT);
}

generatePDF();
