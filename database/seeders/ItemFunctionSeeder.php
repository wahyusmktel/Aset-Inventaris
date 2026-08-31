<?php

namespace Database\Seeders;

use App\Models\ItemFunction;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ItemFunctionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $functions = [
            ['code' => '01', 'name' => 'Peralatan Praktikum Siswa', 'description' => 'Peralatan yang digunakan langsung oleh siswa dalam kegiatan belajar praktikum kejuruan di laboratorium.'],
            ['code' => '02', 'name' => 'Media Pembelajaran & Teori', 'description' => 'Alat bantu visual, proyektor, smartboard interaktif, dan modul ajar untuk ruang kelas teori.'],
            ['code' => '03', 'name' => 'Infrastruktur Jaringan & Server', 'description' => 'Perangkat utama jaringan backbone, router, switch distribusi, server data center, dan gateway internet.'],
            ['code' => '04', 'name' => 'Operasional Kantor & Administrasi', 'description' => 'Peralatan penunjang kerja manajerial kepala sekolah, tata usaha, ruang guru, dan administrasi kesiswaan.'],
            ['code' => '05', 'name' => 'Keamanan & Pengawasan Kampus', 'description' => 'Perangkat kamera CCTV, sensor keamanan, sistem alarm, pos penjagaan, dan palang gerbang otomatis.'],
            ['code' => '06', 'name' => 'Penyimpanan & Cadangan Daya', 'description' => 'Unit penyimpan daya darurat UPS server, generator listrik diesel (genset), dan panel stabilizer.'],
            ['code' => '07', 'name' => 'Pengujian & Alat Ukur (Testing & Measurement)', 'description' => 'Alat ukur parameter teknis seperti OTDR optik, Optical Power Meter (OPM), LAN tester, dan multimeter.'],
            ['code' => '08', 'name' => 'Kreativitas Digital & Produksi Media', 'description' => 'Perangkat rendering grafis 3D animasi, tablet grafis, kamera studio multimedia, dan podcast.'],
            ['code' => '09', 'name' => 'Pemeliharaan, Service & Troubleshooting', 'description' => 'Perlengkapan toolkit teknisi, obeng presisi, tang crimping RJ45, solder, dan pembersih hardware.'],
            ['code' => '10', 'name' => 'Kenyamanan & Tata Ruang (Sarpras)', 'description' => 'Pendingin ruangan AC, meja kursi siswa/guru, lemari arsip, loker, dan papan tulis whiteboard.'],
            ['code' => '11', 'name' => 'Keselamatan, Medis & Tanggap Darurat', 'description' => 'Tabung pemadam kebakaran (APAR), perlengkapan medis UKS, kotak P3K, dan tandu darurat.'],
            ['code' => '12', 'name' => 'Kegiatan Ekstrakurikuler & Olahraga', 'description' => 'Peralatan latihan olahraga, sound sistem organisasi OSIS, robotika, dan kesenian sekolah.'],
            ['code' => '13', 'name' => 'Transportasi & Logistik Sekolah', 'description' => 'Kendaraan dinas operasional sekolah dan sarana pengangkut logistik/sarpras.'],
            ['code' => '14', 'name' => 'Sanitasi, Kebersihan & Pantry', 'description' => 'Dispenser air minum, peralatan kebersihan gedung, tempat sampah pilah, dan mesin pemotong rumput.'],
        ];

        Schema::disableForeignKeyConstraints();
        DB::table('item_functions')->truncate();
        Schema::enableForeignKeyConstraints();

        foreach ($functions as $function) {
            ItemFunction::create($function);
        }
    }
}
