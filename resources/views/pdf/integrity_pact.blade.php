<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Pakta Integritas - {{ $pact->document_number }}</title>
    <style>
        @page {
            margin: 2.2cm 2.5cm 2.2cm 2.5cm;
        }
        body {
            font-family: 'Times New Roman', Times, serif;
            font-size: 12pt;
            line-height: 1.45;
            color: #111827;
        }
        .header-table {
            width: 100%;
            border-bottom: 3px double #000;
            padding-bottom: 8px;
            margin-bottom: 16px;
        }
        .header-title {
            text-align: center;
        }
        .header-title h2 {
            font-size: 14pt;
            margin: 0;
            text-transform: uppercase;
            font-weight: bold;
            letter-spacing: 0.5px;
        }
        .header-title h3 {
            font-size: 13pt;
            margin: 2px 0;
            font-weight: bold;
        }
        .header-title p {
            font-size: 9pt;
            margin: 0;
            font-style: italic;
        }
        .doc-title {
            text-align: center;
            margin-top: 14px;
            margin-bottom: 16px;
        }
        .doc-title h4 {
            font-size: 13pt;
            text-decoration: underline;
            margin: 0;
            text-transform: uppercase;
            font-weight: bold;
        }
        .doc-title p {
            font-size: 10pt;
            margin: 3px 0 0 0;
            font-family: monospace;
        }
        .data-table {
            width: 100%;
            margin-bottom: 12px;
            font-size: 11pt;
        }
        .data-table td {
            padding: 3px 0;
            vertical-align: top;
        }
        .data-table td.label {
            width: 180px;
        }
        .data-table td.colon {
            width: 15px;
            text-align: center;
        }
        .clauses {
            margin-top: 8px;
            margin-bottom: 16px;
            text-align: justify;
        }
        .clauses ol {
            padding-left: 20px;
            margin-top: 4px;
            margin-bottom: 4px;
        }
        .clauses li {
            margin-bottom: 6px;
            font-size: 11pt;
        }
        .signature-table {
            width: 100%;
            margin-top: 20px;
        }
        .sign-box {
            text-align: center;
            width: 250px;
            float: right;
        }
        .sign-box p {
            margin: 2px 0;
            font-size: 11pt;
        }
        .digital-badge {
            margin: 10px auto;
            padding: 8px;
            border: 1px dashed #166534;
            background-color: #f0fdf4;
            border-radius: 4px;
            text-align: center;
            font-size: 8.5pt;
            color: #166534;
            font-family: sans-serif;
        }
        .footer-hash {
            margin-top: 30px;
            border-top: 1px solid #e5e7eb;
            padding-top: 6px;
            font-size: 7.5pt;
            color: #6b7280;
            font-family: monospace;
            word-break: break-all;
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
        <h4>SURAT PERNYATAAN & PAKTA INTEGRITAS</h4>
        <p>Nomor: {{ $pact->document_number }}</p>
    </div>

    <p style="margin-bottom: 8px; font-size: 11pt;">Yang bertanda tangan di bawah ini:</p>

    <!-- IDENTITAS PETUGAS -->
    <table class="data-table">
        <tr>
            <td class="label">Nama Lengkap</td>
            <td class="colon">:</td>
            <td><strong>{{ $user->name }}</strong></td>
        </tr>
        <tr>
            <td class="label">NIP / ID Petugas</td>
            <td class="colon">:</td>
            <td>{{ $user->nip ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Email Resmi</td>
            <td class="colon">:</td>
            <td>{{ $user->email }}</td>
        </tr>
        <tr>
            <td class="label">No. Telepon / WhatsApp</td>
            <td class="colon">:</td>
            <td>{{ $user->phone ?: '-' }}</td>
        </tr>
        <tr>
            <td class="label">Peran / Tugas</td>
            <td class="colon">:</td>
            <td>Tim Pendataan & Inventarisasi Aset Sekolah</td>
        </tr>
        <tr>
            <td class="label">Unit Kerja / Sekolah</td>
            <td class="colon">:</td>
            <td>{{ $school ? $school->name : 'SMK Telkom Lampung' }}</td>
        </tr>
    </table>

    <!-- PERNYATAAN PAKTA INTEGRITAS -->
    <div class="clauses">
        <p style="margin-bottom: 4px;">Dengan ini menyatakan dengan sadar, sungguh-sungguh, dan penuh rasa tanggung jawab bahwa saya berjanji:</p>
        <ol>
            <li>Melaksanakan seluruh tugas pendataan dan inventarisasi fisik barang/aset sekolah dengan jujur, tertib, cermat, transparan, dan penuh integritas.</li>
            <li>Melaporkan seluruh kondisi fisik barang secara faktual (Kondisi Baik maupun Rusak) sesuai kenyataan di lapangan tanpa melakukan perubahan, rekayasa, atau manipulasi data dalam bentuk apa pun.</li>
            <li>Menjaga kerahasiaan data inventaris dan tidak menyalahgunakan informasi atau kewenangan pendataan aset untuk kepentingan pribadi maupun pihak ketiga.</li>
            <li>Menjaga dan memelihara keutuhan barang-barang sekolah yang sedang didata serta segera melaporkan kepada Koordinator Sarpras / PIC Aset apabila menemukan potensi kehilangan atau kerusakan.</li>
            <li>Bersedia menerima sanksi administratif, sanksi disiplin, dan/atau tuntutan hukum sesuai dengan peraturan perundang-undangan yang berlaku apabila saya terbukti melanggar isi Pakta Integritas ini.</li>
        </ol>
    </div>

    <!-- TANDA TANGAN DIGITAL -->
    <table class="signature-table">
        <tr>
            <td style="width: 50%;"></td>
            <td style="width: 50%;">
                <div class="sign-box">
                    <p>Ditetapkan di: Pringsewu</p>
                    <p>Pada tanggal: {{ $pact->signed_at->translatedFormat('d F Y') }}</p>
                    <p style="margin-top: 6px;">Yang Membuat Pernyataan,</p>
                    
                    <div class="digital-badge">
                        <strong>✓ DITANDATANGANI SECARA DIGITAL</strong><br>
                        Waktu: {{ $pact->signed_at->format('d/m/Y H:i:s') }} WIB<br>
                        IP: {{ $pact->signer_ip ?: '127.0.0.1' }}
                    </div>

                    <p><strong><u>{{ $user->name }}</u></strong></p>
                    <p style="font-size: 9pt; color: #4b5563;">NIP: {{ $user->nip ?: '-' }}</p>
                </div>
            </td>
        </tr>
    </table>

    <div style="clear: both;"></div>

    <!-- HASH INTEGRITAS & AUDIT TRAIL -->
    <div class="footer-hash">
        Digital Signature Hash (SHA-256): {{ $pact->digital_signature_hash }}<br>
        Dokumen ini diterbitkan secara elektronik oleh Sistem Informasi Manajemen Aset SMK Telkom Lampung dan sah sebagai arsip hukum integritas pendataan.
    </div>

</body>
</html>
