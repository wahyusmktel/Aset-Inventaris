<?php

namespace Database\Seeders;

use App\Models\Building;
use App\Models\InventoryItem;
use App\Models\ItemCategory;
use App\Models\ItemFunction;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class InventoryItemSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $admin = User::first();
        $categories = ItemCategory::all()->keyBy('name');
        $buildings = Building::all()->keyBy('code');
        $rooms = Room::all()->keyBy('name');
        $functions = ItemFunction::all()->keyBy('code');

        $g1 = $buildings->get('001')?->id;
        $g2 = $buildings->get('002')?->id;
        $g3 = $buildings->get('003')?->id;
        $g4 = $buildings->get('004')?->id;

        $catPC = $categories->get('PC / Komputer')?->id;
        $catRouter = $categories->get('Router')?->id;
        $catSwitch = $categories->get('Switch & Hub')?->id;
        $catSplicer = $categories->get('Alat Fiber Optik')?->id;
        $catTablet = $categories->get('Drawing Tablet')?->id;
        $catSmartboard = $categories->get('Proyektor & Layar')?->id;
        $catAC = $categories->get('AC & Pendingin Ruangan')?->id;
        $catMeja = $categories->get('Meja & Kursi')?->id;

        $roomLabRPL = $rooms->get('Lab Software (RPL)')?->id;
        $roomLabTKJ = $rooms->get('Lab Jaringan (TKJ)')?->id;
        $roomLabFiber = $rooms->get('Lab Fiber Optic (TJAT)')?->id;
        $roomLabAnimasi = $rooms->get('Lab Animasi & Studio 3D')?->id;
        $roomKelasX = $rooms->get('Ruang Kelas X Teori')?->id;
        $roomServer = $rooms->get('Ruang Server & NOC')?->id;

        $funcPraktikum = $functions->get('01')?->id;
        $funcMedia = $functions->get('02')?->id;
        $funcNetwork = $functions->get('03')?->id;
        $funcSarpras = $functions->get('10')?->id;

        $sampleItems = [
            [
                'name' => 'PC Desktop Asus ROG Strix Core i7 Gen-13 RAM 32GB RTX 4060',
                'serial_number' => 'ROG-2026-X89211',
                'has_no_serial_number' => false,
                'brand' => 'ASUS ROG',
                'quantity' => 1,
                'condition' => 'Baik',
                'category_id' => $catPC,
                'building_id' => $g2,
                'room_id' => $roomLabRPL,
                'function_id' => $funcPraktikum,
                'created_by' => $admin?->id,
                'notes' => 'Unit PC praktikum software development & AI coding.',
            ],
            [
                'name' => 'Router Core Mikrotik CCR1036-8G-2S+ Cloud Core Router',
                'serial_number' => 'MT-CCR-8829103',
                'has_no_serial_number' => false,
                'brand' => 'MikroTik',
                'quantity' => 1,
                'condition' => 'Baik',
                'category_id' => $catRouter,
                'building_id' => $g1,
                'room_id' => $roomServer,
                'function_id' => $funcNetwork,
                'created_by' => $admin?->id,
                'notes' => 'Router gateway utama distribusi bandwidth internet sekolah.',
            ],
            [
                'name' => 'Fusion Splicer Fujikura 90S+ Core Alignment Fiber Optic',
                'serial_number' => 'FJK-90S-004812',
                'has_no_serial_number' => false,
                'brand' => 'Fujikura',
                'quantity' => 1,
                'condition' => 'Baik',
                'category_id' => $catSplicer,
                'building_id' => $g3,
                'room_id' => $roomLabFiber,
                'function_id' => $funcPraktikum,
                'created_by' => $admin?->id,
                'notes' => 'Alat penyambung serat optik presisi tinggi praktikum TJAT.',
            ],
            [
                'name' => 'Interactive Display Smart Board 75 Inch 4K Touchscreen',
                'serial_number' => 'SB-75-4K-99021',
                'has_no_serial_number' => false,
                'brand' => 'ViewSonic',
                'quantity' => 1,
                'condition' => 'Baik',
                'category_id' => $catSmartboard,
                'building_id' => $g4,
                'room_id' => $roomKelasX,
                'function_id' => $funcMedia,
                'created_by' => $admin?->id,
                'notes' => 'Layar sentuh cerdas interaktif papan tulis digital kelas teori.',
            ],
            [
                'name' => 'Pen Display Drawing Tablet Huion Kamvas Pro 16 (4K)',
                'serial_number' => 'HUION-KP16-11849',
                'has_no_serial_number' => false,
                'brand' => 'Huion',
                'quantity' => 1,
                'condition' => 'Baik',
                'category_id' => $catTablet,
                'building_id' => $g3,
                'room_id' => $roomLabAnimasi,
                'function_id' => $funcPraktikum,
                'created_by' => $admin?->id,
                'notes' => 'Tablet gambar digital layar sentuh untuk lab studio animasi 3D.',
            ],
            [
                'name' => 'Meja Siswa Kayu Solid & Kursi Besi Ergonomis',
                'serial_number' => null,
                'has_no_serial_number' => true,
                'brand' => 'Chitose',
                'quantity' => 36,
                'condition' => 'Baik',
                'category_id' => $catMeja,
                'building_id' => $g4,
                'room_id' => $roomKelasX,
                'function_id' => $funcSarpras,
                'created_by' => $admin?->id,
                'notes' => 'Set meja dan kursi belajar siswa 1 set 36 unit.',
            ],
            [
                'name' => 'Air Conditioner Split Inverter 2 PK R32',
                'serial_number' => 'DK-AC2PK-77189',
                'has_no_serial_number' => false,
                'brand' => 'Daikin',
                'quantity' => 2,
                'condition' => 'Baik',
                'category_id' => $catAC,
                'building_id' => $g2,
                'room_id' => $roomLabRPL,
                'function_id' => $funcSarpras,
                'created_by' => $admin?->id,
                'notes' => 'Pendingin ruangan lab komputer agar temperatur tetap stabil.',
            ],
            [
                'name' => 'Cisco Catalyst 2960-X Series 24 Port Gigabit Switch',
                'serial_number' => 'WS-C2960X-24TS-L',
                'has_no_serial_number' => false,
                'brand' => 'Cisco',
                'quantity' => 1,
                'condition' => 'Baik',
                'category_id' => $catSwitch,
                'building_id' => $g2,
                'room_id' => $roomLabTKJ,
                'function_id' => $funcPraktikum,
                'created_by' => $admin?->id,
                'notes' => 'Switch praktikum konfigurasi VLAN dan Trunking TKJ.',
            ],
        ];

        Schema::disableForeignKeyConstraints();
        DB::table('inventory_items')->truncate();
        Schema::enableForeignKeyConstraints();

        foreach ($sampleItems as $item) {
            InventoryItem::create($item);
        }
    }
}
