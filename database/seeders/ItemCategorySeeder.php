<?php

namespace Database\Seeders;

use App\Models\ItemCategory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ItemCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categories = [
            // KEJURUAN RPL (Rekayasa Perangkat Lunak)
            ['code' => '001', 'name' => 'PC / Komputer', 'description' => 'Unit PC desktop dan workstation praktikum programming & database.'],
            ['code' => '002', 'name' => 'Laptop & Notebook', 'description' => 'Laptop praktikum pengujian software dan mobile development.'],
            ['code' => '003', 'name' => 'Server & Data Center', 'description' => 'Unit server hosting aplikasi, database server, dan deployment staging.'],
            ['code' => '004', 'name' => 'Perangkat Lunak / Lisensi', 'description' => 'Lisensi sistem operasi, IDE, compiler, dan database management.'],

            // KEJURUAN ANIMASI & MULTIMEDIA
            ['code' => '005', 'name' => 'Drawing Tablet', 'description' => 'Tablet grafis layar sentuh dan pen display untuk sketsa & modeling 2D/3D.'],
            ['code' => '006', 'name' => 'Kamera & Lensa', 'description' => 'Kamera video, DSLR, mirrorless, dan lensa produksi multimedia.'],
            ['code' => '007', 'name' => 'Peralatan Audio & Mic', 'description' => 'Mikrofon condenser, clip-on wireless, audio interface, soundcard, headphone studio.'],
            ['code' => '008', 'name' => 'Pencahayaan / Lighting', 'description' => 'Softbox, ring light, LED panel, dan perlengkapan lighting studio green screen.'],
            ['code' => '009', 'name' => 'Tripod, Gimbal & Rig', 'description' => 'Stabilizer gimbal kamera, monopod, slider, dan rig shooting.'],
            ['code' => '010', 'name' => 'Green Screen & Studio Set', 'description' => 'Kain background green/blue screen, frame stand, dan dekorasi set studio.'],

            // KEJURUAN TJAT (Teknik Jaringan Akses Telekomunikasi)
            ['code' => '011', 'name' => 'Alat Fiber Optik', 'description' => 'Fusion splicer, optical cleaver, fiber stripper, dan visual fault locator.'],
            ['code' => '012', 'name' => 'Alat Ukur Optik & OTDR', 'description' => 'OTDR (Optical Time Domain Reflectometer), Optical Power Meter (OPM), Laser Source.'],
            ['code' => '013', 'name' => 'Kabel Fiber Optik & Patchcord', 'description' => 'Kabel dropcore, patchcord SC/LC/FC, pigtail, dan closure distribusi.'],
            ['code' => '014', 'name' => 'ODP, ODC & Kotak Distribusi', 'description' => 'Optical Distribution Point (ODP), Optical Distribution Cabinet (ODC), OTB rack.'],
            ['code' => '015', 'name' => 'Antena & Perangkat Wireless', 'description' => 'Antena sektoral, radio link mikrotik, point-to-point wireless backbone.'],

            // KEJURUAN TKJ (Teknik Komputer & Jaringan) & TELKOM INFRASTRUKTUR
            ['code' => '016', 'name' => 'Router', 'description' => 'Router gateway mikrotik, cisco router, dan core router sekolah.'],
            ['code' => '017', 'name' => 'Switch & Hub', 'description' => 'Manageable gigabit switch, PoE switch, dan distribusi switch rackmount.'],
            ['code' => '018', 'name' => 'Access Point & WiFi', 'description' => 'Access point enterprise, ceiling AP, controller hotspot sekolah.'],
            ['code' => '019', 'name' => 'Kabel Jaringan & Konektor', 'description' => 'Kabel UTP/STP Cat5e/Cat6, RJ45 konektor, barrel, dan modular jack.'],
            ['code' => '020', 'name' => 'Server Rack & Patch Panel', 'description' => 'Rak server close rack 19 inch, wallmount rack, patch panel, cable management.'],

            // SARANA PRASARANA & FASILITAS SEKOLAH (GLOBAL)
            ['code' => '021', 'name' => 'Meja & Kursi', 'description' => 'Meja belajar siswa, kursi lab hidrolik, meja guru, dan meja rapat.'],
            ['code' => '022', 'name' => 'Lemari, Rak & Loker', 'description' => 'Lemari arsip besi, loker penyimpanan barang siswa, rak buku perpustakaan.'],
            ['code' => '023', 'name' => 'Papan Tulis & Display', 'description' => 'Whiteboard magnetic, papan pengumuman kaca, dan standing banner akrilik.'],
            ['code' => '024', 'name' => 'Proyektor & Layar', 'description' => 'LCD Projector HDMI, smart projector, screen tripod, dan motorized screen.'],
            ['code' => '025', 'name' => 'Sound System & Speaker', 'description' => 'Speaker aktif aula, amplifier rapat, mic wireless, dan bel otomatis sekolah.'],
            ['code' => '026', 'name' => 'AC & Pendingin Ruangan', 'description' => 'Air conditioner split, standing AC aula, dan kipas angin exhaust lab.'],
            ['code' => '027', 'name' => 'CCTV & Keamanan', 'description' => 'Kamera pengawas IP CCTV, NVR server recorder, decoder monitor satpam.'],
            ['code' => '028', 'name' => 'UPS & Genset Listrik', 'description' => 'Uninterruptible Power Supply (UPS) rack, stabilizer servo, genset diesel.'],
            ['code' => '029', 'name' => 'Peralatan Bengkel & Toolkit', 'description' => 'Crimping tool, multimeter digital, solder station, obeng presisi, tang potong.'],
            ['code' => '030', 'name' => 'Alat Kebersihan & Sanitasi', 'description' => 'Mesin pemotong rumput, vacuum cleaner lab, tempat sampah pilah 3 warna.'],
            ['code' => '031', 'name' => 'Peralatan Olahraga', 'description' => 'Bola basket, bola voli, meja tenis meja, matras senam, net olahraga.'],
            ['code' => '032', 'name' => 'Peralatan Medis UKS', 'description' => 'Tempat tidur pasien UKS, tabung oksigen, tensimeter digital, timbangan badan.'],
            ['code' => '033', 'name' => 'Kendaraan Operasional', 'description' => 'Mobil dinas sekolah, motor kurir administrasi, dan armada logistik.'],
        ];

        Schema::disableForeignKeyConstraints();
        DB::table('item_categories')->truncate();
        Schema::enableForeignKeyConstraints();

        foreach ($categories as $category) {
            ItemCategory::create($category);
        }
    }
}
