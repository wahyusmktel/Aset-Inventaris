<?php

namespace Database\Seeders;

use App\Models\School;
use Illuminate\Database\Seeder;

class SchoolSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $schools = [
            [
                'code' => 'SMK-TELKOM-LPG',
                'name' => 'SMK Telkom Lampung',
                'address' => 'Jl. Jenderal Sudirman No. 88, Pringsewu, Kabupaten Pringsewu, Lampung',
                'latitude' => '-5.358241',
                'longitude' => '104.981242',
                'principal_name' => 'Drs. H. Bambang Subagyo, M.Kom.',
                'principal_nip' => '19750815 199903 1 002',
                'kaur_it_name' => 'Rizky Pratama, S.Kom., M.T.',
                'kaur_it_nip' => '19881210 201402 1 005',
                'is_active' => true,
            ],
            [
                'code' => 'SMKN-01-JKT',
                'name' => 'SMK Negeri 1 Jakarta',
                'address' => 'Jl. Budi Utomo No.7, Ps. Baru, Kecamatan Sawah Besar, Kota Jakarta Pusat, DKI Jakarta',
                'latitude' => '-6.166649',
                'longitude' => '106.837387',
                'principal_name' => 'Drs. H. Mulyadi, M.Pd.',
                'principal_nip' => '19680312 199201 1 001',
                'kaur_it_name' => 'Hendra Setiawan, S.T.',
                'kaur_it_nip' => '19850414 201001 1 003',
                'is_active' => false,
            ],
        ];

        foreach ($schools as $school) {
            School::updateOrCreate(
                ['code' => $school['code']],
                $school
            );
        }
    }
}
