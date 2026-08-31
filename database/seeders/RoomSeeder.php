<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\Room;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class RoomSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buildings = Building::all()->keyBy('code');

        $g1 = $buildings->get('001')?->id;
        $g2 = $buildings->get('002')?->id;
        $g3 = $buildings->get('003')?->id;
        $g4 = $buildings->get('004')?->id;
        $g5 = $buildings->get('005')?->id;

        $rooms = [
            ['code' => '0001', 'name' => 'Ruang Kelas X Teori', 'building_id' => $g4, 'floor' => 1, 'capacity' => 36, 'type' => 'Ruang Teori / Kelas', 'description' => 'Ruang kelas pembelajaran teori umum kelas X.'],
            ['code' => '0002', 'name' => 'Ruang Kelas XI Teori', 'building_id' => $g4, 'floor' => 2, 'capacity' => 36, 'type' => 'Ruang Teori / Kelas', 'description' => 'Ruang kelas pembelajaran teori umum kelas XI.'],
            ['code' => '0003', 'name' => 'Ruang Kelas XII Teori', 'building_id' => $g4, 'floor' => 3, 'capacity' => 36, 'type' => 'Ruang Teori / Kelas', 'description' => 'Ruang kelas pembelajaran teori umum kelas XII.'],
            ['code' => '0004', 'name' => 'Ruang Guru & Staf Pengajar', 'building_id' => $g1, 'floor' => 2, 'capacity' => 60, 'type' => 'Ruang Kantor & Guru', 'description' => 'Ruang kerja bersama para dewan guru dan staf pengajar.'],
            ['code' => '0005', 'name' => 'Lab Software (RPL)', 'building_id' => $g2, 'floor' => 1, 'capacity' => 40, 'type' => 'Laboratorium Komputer', 'description' => 'Laboratorium pemrograman, software development, dan web/mobile app RPL.'],
            ['code' => '0006', 'name' => 'Lab Fiber Optic (TJAT)', 'building_id' => $g3, 'floor' => 1, 'capacity' => 36, 'type' => 'Laboratorium Khusus', 'description' => 'Laboratorium praktikum penyambungan serat optik, OTDR, dan instalasi akses telekomunikasi.'],
            ['code' => '0007', 'name' => 'Lab Animasi & Studio 3D', 'building_id' => $g3, 'floor' => 2, 'capacity' => 36, 'type' => 'Laboratorium Khusus', 'description' => 'Studio workstation rendering grafis 2D/3D, animasi karakter, dan motion capture.'],
            ['code' => '0008', 'name' => 'Lab Hardware & Perakitan', 'building_id' => $g2, 'floor' => 2, 'capacity' => 36, 'type' => 'Laboratorium Komputer', 'description' => 'Lab praktikum perakitan komputer, troubleshooting PC, dan instalasi sistem operasi.'],
            ['code' => '0009', 'name' => 'Lab Jaringan (TKJ)', 'building_id' => $g2, 'floor' => 3, 'capacity' => 40, 'type' => 'Laboratorium Komputer', 'description' => 'Lab praktikum konfigurasi routing mikrotik/cisco, switching, dan keamanan jaringan.'],
            ['code' => '0010', 'name' => 'Lab Informatika Dasar', 'building_id' => $g2, 'floor' => 1, 'capacity' => 40, 'type' => 'Laboratorium Komputer', 'description' => 'Lab literasi komputer dan mata pelajaran informatika kurikulum nasional.'],
            ['code' => '0011', 'name' => 'Lab IPAS & Sains Terapan', 'building_id' => $g4, 'floor' => 1, 'capacity' => 36, 'type' => 'Laboratorium Khusus', 'description' => 'Lab praktikum eksperimen sains terapan Ilmu Pengetahuan Alam dan Sosial.'],
            ['code' => '0012', 'name' => 'Lab Bahasa & Multimedia', 'building_id' => $g4, 'floor' => 2, 'capacity' => 40, 'type' => 'Laboratorium Khusus', 'description' => 'Lab audio-visual untuk pembelajaran bahasa asing dan listening comprehension.'],
            ['code' => '0013', 'name' => 'Ruang Kepala Sekolah', 'building_id' => $g1, 'floor' => 1, 'capacity' => 10, 'type' => 'Ruang Pimpinan & Rapat', 'description' => 'Ruang kerja manajerial Kepala Sekolah dan penerimaan tamu dinas.'],
            ['code' => '0014', 'name' => 'Ruang Tata Usaha (TU)', 'building_id' => $g1, 'floor' => 1, 'capacity' => 20, 'type' => 'Ruang Kantor & Guru', 'description' => 'Ruang pelayanan administrasi persuratan, keuangan, dan data pokok pendidikan.'],
            ['code' => '0015', 'name' => 'Ruang Server & NOC', 'building_id' => $g1, 'floor' => 3, 'capacity' => 10, 'type' => 'Laboratorium Khusus', 'description' => 'Network Operations Center (NOC) dan pusat rak server internet sekolah.'],
            ['code' => '0016', 'name' => 'Perpustakaan Digital & Ruang Baca', 'building_id' => $g4, 'floor' => 4, 'capacity' => 80, 'type' => 'Fasilitas Akademik & Siswa', 'description' => 'Pusat sumber belajar, e-library komputer, dan ruang diskusi literasi siswa.'],
            ['code' => '0017', 'name' => 'Ruang Bimbingan Konseling (BK)', 'building_id' => $g1, 'floor' => 2, 'capacity' => 12, 'type' => 'Ruang Kantor & Guru', 'description' => 'Ruang layanan konseling siswa, psikologi pendidikan, dan bimbingan karir.'],
            ['code' => '0018', 'name' => 'Ruang OSIS & MPK', 'building_id' => $g5, 'floor' => 1, 'capacity' => 25, 'type' => 'Fasilitas Akademik & Siswa', 'description' => 'Sekretariat organisasi kesiswaan OSIS dan Majelis Perwakilan Kelas.'],
            ['code' => '0019', 'name' => 'Ruang UKS & Medis', 'building_id' => $g1, 'floor' => 1, 'capacity' => 10, 'type' => 'Fasilitas Umum & Olahraga', 'description' => 'Unit Kesehatan Sekolah untuk pertolongan pertama dan perawatan darurat siswa/guru.'],
            ['code' => '0020', 'name' => 'Ruang Robotika & IoT Hub', 'building_id' => $g2, 'floor' => 3, 'capacity' => 30, 'type' => 'Laboratorium Khusus', 'description' => 'Pusat riset dan praktikum mikrokontroler Arduino, IoT, dan robotika kompetisi.'],
            ['code' => '0021', 'name' => 'Studio Podcast & Broadcasting', 'building_id' => $g3, 'floor' => 3, 'capacity' => 15, 'type' => 'Laboratorium Khusus', 'description' => 'Studio kedap suara rekaman podcast sekolah, streaming acara, dan voice over.'],
            ['code' => '0022', 'name' => 'Ruang Koperasi & Kantin Sekolah', 'building_id' => $g5, 'floor' => 1, 'capacity' => 50, 'type' => 'Fasilitas Umum & Olahraga', 'description' => 'Layanan penjualan ATK, seragam siswa, dan kantin sehat sekolah.'],
            ['code' => '0023', 'name' => 'Musholla As-Salam', 'building_id' => $g5, 'floor' => 1, 'capacity' => 150, 'type' => 'Fasilitas Umum & Olahraga', 'description' => 'Fasilitas ibadah sholat berjamaah dan kegiatan keagamaan kampus.'],
            ['code' => '0024', 'name' => 'Aula Serbaguna (Auditorium)', 'building_id' => $g5, 'floor' => 2, 'capacity' => 500, 'type' => 'Fasilitas Umum & Olahraga', 'description' => 'Gedung pertemuan akbar, seminar nasional, pameran karya siswa, dan wisuda.'],
            ['code' => '0025', 'name' => 'Ruang Rapat Utama (Meeting Room)', 'building_id' => $g1, 'floor' => 2, 'capacity' => 30, 'type' => 'Ruang Pimpinan & Rapat', 'description' => 'Ruang rapat dewan pimpinan, komite sekolah, dan kerja sama industri.'],
            ['code' => '0026', 'name' => 'Gudang Sarana & Prasarana', 'building_id' => $g5, 'floor' => 1, 'capacity' => 15, 'type' => 'Fasilitas Umum & Olahraga', 'description' => 'Tempat penyimpanan cadangan inventaris mebel, alat kebersihan, dan logistik.'],
            ['code' => '0027', 'name' => 'Pos Satpam & Security', 'building_id' => $g1, 'floor' => 1, 'capacity' => 8, 'type' => 'Fasilitas Umum & Olahraga', 'description' => 'Pos penjagaan keamanan gerbang utama, pantauan CCTV, dan buku tamu.'],
        ];

        Schema::disableForeignKeyConstraints();
        DB::table('rooms')->truncate();
        Schema::enableForeignKeyConstraints();

        foreach ($rooms as $room) {
            Room::create($room);
        }
    }
}
