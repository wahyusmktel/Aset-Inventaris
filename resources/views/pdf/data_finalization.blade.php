<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Berita Acara Finalisasi - {{ $finalization->document_number }}</title>
    <style>
        @page {
            margin: 2cm 2.2cm 2cm 2.2cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 11pt;
            line-height: 1.4;
            color: #111827;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 6px;
            margin-bottom: 14px;
        }
        .header-title {
            text-align: center;
        }
        .header-title h2 {
            font-size: 13pt;
            margin: 0;
            text-transform: uppercase;
            font-weight: bold;
        }
        .header-title h3 {
            font-size: 12pt;
            margin: 2px 0;
            font-weight: bold;
        }
        .header-title p {
            font-size: 8.5pt;
            margin: 0;
            font-style: italic;
        }
        .doc-title {
            text-align: center;
            margin-top: 10px;
            margin-bottom: 14px;
        }
        .doc-title h4 {
            font-size: 12pt;
            text-decoration: underline;
            margin: 0;
            text-transform: uppercase;
            font-weight: bold;
        }
        .doc-title p {
            font-size: 9.5pt;
            margin: 2px 0 0 0;
            font-family: monospace;
        }
        .summary-box {
            border: 1px solid #94a3b8;
            background-color: #f8fafc;
            border-radius: 4px;
            padding: 8px 12px;
            margin: 12px 0;
        }
        .summary-table {
            width: 100%;
            border-collapse: collapse;
            font-size: 10pt;
            margin-top: 4px;
        }
        .summary-table th, .summary-table td {
            border: 1px solid #cbd5e1;
            padding: 5px 8px;
        }
        .summary-table th {
            background-color: #e2e8f0;
            text-align: center;
            font-weight: bold;
        }
        .sign-table {
            width: 100%;
            margin-top: 25px;
            page-break-inside: avoid;
        }
        .sign-col {
            width: 33.33%;
            text-align: center;
            vertical-align: top;
            padding: 0 4px;
        }
        .sign-col p {
            margin: 1px 0;
            font-size: 9.5pt;
        }
        .sign-space {
            height: 55px;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        .sign-badge {
            border: 1px dashed #2563eb;
            background: #eff6ff;
            color: #1e40af;
            font-size: 7.5pt;
            padding: 4px;
            border-radius: 4px;
            margin: 6px auto;
            width: 85%;
            font-family: sans-serif;
        }
        .footer-note {
            margin-top: 20px;
            border-top: 1px solid #e2e8f0;
            padding-top: 4px;
            font-size: 7.5pt;
            color: #64748b;
            font-family: monospace;
        }
    </style>
</head>
<body>

    <!-- KOP SURAT SEKOLAH -->
    <table class="header-table">
        <tr>
            <td class="header-title">
                <h2>YAYASAN PENDIDIKAN TELKOM</h2>
                <h3>{{ $school ? strtoupper($school->name) : 'SMK TELKOM LAMPUNG' }}</h3>
                <p>{{ $school ? $school->address : 'Jl. Jenderal Sudirman No. 88, Pringsewu, Lampung' }}</p>
            </td>
        </tr>
    </table>

    <!-- JUDUL DOKUMEN -->
    <div class="doc-title">
        <h4>BERITA ACARA PENYELESAIAN & FINALISASI PENDATAAN INVENTARIS</h4>
        <p>Nomor: {{ $finalization->document_number }}</p>
    </div>

    <p style="text-align: justify; font-size: 10.5pt; margin-bottom: 6px;">
        Pada hari ini, <strong>{{ $finalization->signed_at->translatedFormat('l') }}</strong> 
        tanggal <strong>{{ $finalization->signed_at->translatedFormat('d F Y') }}</strong>, telah diselesaikan rangkaian proses pencatatan, 
        pemeriksaan fisik, dan audit kelayakan sarana prasarana sekolah oleh Tim Surveyor Pendataan Inventaris Barang 
        <strong>{{ $school ? $school->name : 'SMK Telkom Lampung' }}</strong> dengan hasil rekapitulasi data sebagai berikut:
    </p>

    <!-- TABEL REKAPITULASI ASET -->
    <div class="summary-box">
        <strong style="font-size: 10pt; color: #1e293b;">Rekapitulasi Hasil Pendataan Inventaris:</strong>
        <table class="summary-table">
            <thead>
                <tr>
                    <th>Total Jenis Barang</th>
                    <th>Total Kuantitas Fisik</th>
                    <th>Kondisi Baik (Laik)</th>
                    <th>Kondisi Rusak</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td style="text-align: center; font-weight: bold;">{{ $finalization->total_items_recorded }} Jenis Item</td>
                    <td style="text-align: center; font-weight: bold; color: #1d4ed8;">{{ $finalization->total_units_recorded }} Unit</td>
                    <td style="text-align: center; font-weight: bold; color: #15803d;">{{ $finalization->total_good_condition }} Unit</td>
                    <td style="text-align: center; font-weight: bold; color: #b91c1c;">{{ $finalization->total_damaged_condition }} Unit</td>
                </tr>
            </tbody>
        </table>
    </div>

    <p style="text-align: justify; font-size: 10.5pt; margin-top: 6px; margin-bottom: 6px;">
        Dengan diterbitkannya Berita Acara ini, maka seluruh data barang yang telah diinput dinyatakan telah <strong>DIKUNCI (FINALISASI)</strong> 
        dan siap untuk disahkan sebagai buku aset resmi sekolah. Segala bentuk perubahan, mutasi, atau penghapusan barang selanjutnya harus melalui prosedur persetujuan resmi Kepala Sekolah dan Kaur IT / PIC Aset.
    </p>

    <p style="font-size: 10pt; font-style: italic; margin-top: 4px;">
        Catatan / Keterangan: {{ $finalization->statement_notes ?: 'Pendataan terlaksana dengan lengkap sesuai prosedur standar operasional inventarisasi sekolah.' }}
    </p>

    <!-- 3 TANDA TANGAN RESMI -->
    <table class="sign-table">
        <tr>
            <!-- 1. Tanda Tangan Anggota / Tim Pendata -->
            <td class="sign-col">
                <p>Petugas Pendata,</p>
                <div class="sign-badge">
                    <strong>✓ VALIDASI FINAL</strong><br>
                    {{ $finalization->signed_at->format('d/m/Y H:i') }} WIB
                </div>
                <p><strong><u>{{ $user->name }}</u></strong></p>
                <p>NIP: {{ $user->nip ?: '-' }}</p>
            </td>

            <!-- 2. Tanda Tangan Kaur IT / PIC Aset -->
            <td class="sign-col">
                <p>Mengetahui,</p>
                <p style="font-weight: bold;">Kaur IT / PIC Aset Sekolah</p>
                <div class="sign-space"></div>
                <p><strong><u>{{ $school && $school->kaur_it_name ? $school->kaur_it_name : 'Rizky Pratama, S.Kom., M.T.' }}</u></strong></p>
                <p>NIP: {{ $school && $school->kaur_it_nip ? $school->kaur_it_nip : '19881210 201402 1 005' }}</p>
            </td>

            <!-- 3. Tanda Tangan Kepala Sekolah -->
            <td class="sign-col">
                <p>Menyetujui,</p>
                <p style="font-weight: bold;">Kepala Sekolah</p>
                <div class="sign-space"></div>
                <p><strong><u>{{ $school && $school->principal_name ? $school->principal_name : 'Drs. H. Bambang Subagyo, M.Kom.' }}</u></strong></p>
                <p>NIP: {{ $school && $school->principal_nip ? $school->principal_nip : '19750815 199903 1 002' }}</p>
            </td>
        </tr>
    </table>

    <!-- FOOTER AUDIT -->
    <div class="footer-note">
        Dokumen Berita Acara Finalisasi Sah No. {{ $finalization->document_number }} | Dicetak melalui Sistem Manajemen Aset SMK Telkom Lampung.
    </div>

</body>
</html>
