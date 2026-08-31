<?php

namespace Database\Seeders;

use App\Models\Building;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class BuildingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $buildings = [
            [
                'code' => '001',
                'name' => 'Gedung 1',
                'total_floors' => 3,
                'description' => 'Gedung Utama Administrasi, Ruang Kepala Sekolah, Tata Usaha, Ruang Guru, dan Ruang Server Pusat (NOC).',
            ],
            [
                'code' => '002',
                'name' => 'Gedung 2',
                'total_floors' => 4,
                'description' => 'Gedung Laboratorium Komputer Kejuruan: Lab Software (RPL), Lab Hardware, dan Lab Jaringan (TKJ).',
            ],
            [
                'code' => '003',
                'name' => 'Gedung 3',
                'total_floors' => 3,
                'description' => 'Gedung Praktikum Khusus: Lab Fiber Optic (TJAT), Studio Animasi 3D, dan Studio Podcast Multimedia.',
            ],
            [
                'code' => '004',
                'name' => 'Gedung 4',
                'total_floors' => 4,
                'description' => 'Gedung Teori & Akademik: Ruang Kelas X, XI, XII, Perpustakaan Digital, dan Lab Bahasa/IPAS.',
            ],
            [
                'code' => '005',
                'name' => 'Gedung 5',
                'total_floors' => 2,
                'description' => 'Gedung Serbaguna (Auditorium Hall), Ruang OSIS/MPK, UKS, Kantin Sekolah, dan Musholla Kampus.',
            ],
        ];

        Schema::disableForeignKeyConstraints();
        DB::table('buildings')->truncate();
        Schema::enableForeignKeyConstraints();

        foreach ($buildings as $building) {
            Building::create($building);
        }
    }
}
