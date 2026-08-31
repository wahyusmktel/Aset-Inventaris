<?php

namespace Database\Seeders;

use App\Models\InventoryPeriod;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Super Admin Account
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Super Admin (Administrator Utama)',
                'password' => Hash::make('12345678'),
                'role' => 'super_admin',
                'nip' => '19850101 201001 1 001',
                'phone' => '081234567890',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 2. Sample Anggota / Surveyor Account
        User::updateOrCreate(
            ['email' => 'anggota@smktelkom.sch.id'],
            [
                'name' => 'Ahmad Fauzi (Tim Surveyor Inventaris)',
                'password' => Hash::make('12345678'),
                'role' => 'anggota',
                'nip' => '19950712 202102 1 008',
                'phone' => '089876543210',
                'is_active' => true,
                'email_verified_at' => now(),
            ]
        );

        // 3. Active Inventory Period (Default Cutoff 14 days from now)
        InventoryPeriod::updateOrCreate(
            ['name' => 'Pendataan Inventaris Aset Semester Ganjil 2026/2027'],
            [
                'start_date' => Carbon::now()->subDays(2),
                'cutoff_date' => Carbon::now()->addDays(14),
                'is_active' => true,
                'notes' => 'Periode resmi pendataan barang baru dan audit fisik sarana prasarana sekolah.',
            ]
        );

        $this->call([
            SchoolSeeder::class,
            ItemCategorySeeder::class,
            BuildingSeeder::class,
            RoomSeeder::class,
            ItemFunctionSeeder::class,
            InventoryItemSeeder::class,
        ]);
    }
}
