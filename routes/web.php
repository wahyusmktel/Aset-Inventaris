<?php

use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Governance\DataFinalizationController;
use App\Http\Controllers\Governance\IntegrityPactController;
use App\Http\Controllers\Governance\InventoryPeriodController;
use App\Http\Controllers\Inventory\InventoryItemController;
use App\Http\Controllers\MasterData\BuildingController;
use App\Http\Controllers\MasterData\ItemCategoryController;
use App\Http\Controllers\MasterData\ItemFunctionController;
use App\Http\Controllers\MasterData\RoomController;
use App\Http\Controllers\MasterData\SchoolController;
use App\Http\Controllers\Governance\SystemResetController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\UserManagement\UserController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (auth()->check()) {
        return redirect()->route('dashboard');
    }
    return redirect()->route('login');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    // 1. Pakta Integritas (Accessible before pact.signed middleware)
    Route::get('/pakta-integritas', [IntegrityPactController::class, 'show'])->name('integrity-pact.show');
    Route::post('/pakta-integritas', [IntegrityPactController::class, 'store'])->name('integrity-pact.store');
    Route::get('/pakta-integritas/download', [IntegrityPactController::class, 'download'])->name('integrity-pact.download');

    // 2. Routes guarded by pact.signed (Anggota must sign pact before accessing)
    Route::middleware('pact.signed')->group(function () {
        // Dashboard
        Route::get('/dashboard', [DashboardController::class, 'index'])->name('dashboard');

        // Profile
        Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
        Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
        Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

        // INVENTARIS BARANG (Accessible by both Super Admin & Anggota)
        Route::prefix('inventaris')->name('inventory.')->group(function () {
            Route::get('items/export', [InventoryItemController::class, 'exportExcel'])->name('items.export');
            Route::resource('items', InventoryItemController::class)->except(['create', 'edit', 'show']);
            Route::post('items/bulk-seed', [InventoryItemController::class, 'bulkSeed'])->name('items.bulk-seed');
        });

        // FINALISASI DATA & BERITA ACARA
        Route::prefix('finalisasi-data')->name('data-finalization.')->group(function () {
            Route::get('/', [DataFinalizationController::class, 'index'])->name('index');
            Route::post('/', [DataFinalizationController::class, 'store'])->name('store');
            Route::get('/download', [DataFinalizationController::class, 'download'])->name('download');
        });

        // SUPER ADMIN ONLY FEATURES
        Route::middleware('super_admin')->group(function () {
            // MANAJEMEN PENGGUNA & ASSIGN ROLE
            Route::prefix('manajemen-pengguna')->name('user-management.')->group(function () {
                Route::get('/', [UserController::class, 'index'])->name('users.index');
                Route::post('/users', [UserController::class, 'store'])->name('users.store');
                Route::put('/users/{user}', [UserController::class, 'update'])->name('users.update');
                Route::patch('/users/{user}/assign-role', [UserController::class, 'assignRole'])->name('users.assign-role');
                Route::delete('/users/{user}', [UserController::class, 'destroy'])->name('users.destroy');
            });

            // PENGATURAN PERIODE PENGADAAN & CUTOFF
            Route::prefix('pengaturan-periode')->name('inventory-period.')->group(function () {
                Route::get('/', [InventoryPeriodController::class, 'index'])->name('index');
                Route::post('/', [InventoryPeriodController::class, 'store'])->name('store');
                Route::put('/{period}', [InventoryPeriodController::class, 'update'])->name('update');
                Route::delete('/{period}', [InventoryPeriodController::class, 'destroy'])->name('destroy');
            });

            // DATA REFERENSI (Super Admin Only)
            Route::prefix('data-referensi')->name('master-data.')->group(function () {
                // Data Sekolah
                Route::resource('schools', SchoolController::class)->except(['create', 'edit', 'show']);
                Route::patch('schools/{school}/activate', [SchoolController::class, 'activate'])->name('schools.activate');

                // Kategori Barang
                Route::resource('item-categories', ItemCategoryController::class)->except(['create', 'edit', 'show']);
                Route::post('item-categories/bulk-seed', [ItemCategoryController::class, 'bulkSeed'])->name('item-categories.bulk-seed');

                // Data Gedung
                Route::resource('buildings', BuildingController::class)->except(['create', 'edit', 'show']);
                Route::post('buildings/bulk-seed', [BuildingController::class, 'bulkSeed'])->name('buildings.bulk-seed');

                // Data Ruangan
                Route::resource('rooms', RoomController::class)->except(['create', 'edit', 'show']);
                Route::post('rooms/bulk-seed', [RoomController::class, 'bulkSeed'])->name('rooms.bulk-seed');

                // Fungsi Barang
                Route::resource('item-functions', ItemFunctionController::class)->except(['create', 'edit', 'show']);
                Route::post('item-functions/bulk-seed', [ItemFunctionController::class, 'bulkSeed'])->name('item-functions.bulk-seed');
            });

            // PENGOSONGAN & RESET DATA SISTEM (Super Admin Only)
            Route::prefix('pengaturan-sistem')->name('system.')->group(function () {
                Route::post('/reset-inventory', [SystemResetController::class, 'resetInventory'])->name('reset.inventory');
                Route::post('/reset-all-transactional', [SystemResetController::class, 'resetAllTransactional'])->name('reset.all-transactional');
            });
        });
    });
});

require __DIR__.'/auth.php';
