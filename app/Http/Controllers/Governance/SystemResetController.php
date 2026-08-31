<?php

namespace App\Http\Controllers\Governance;

use App\Http\Controllers\Controller;
use App\Models\DataFinalization;
use App\Models\IntegrityPact;
use App\Models\InventoryItem;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class SystemResetController extends Controller
{
    /**
     * Clear all inventory items and finalization data.
     * Keeps user accounts, integrity pacts, and reference master data.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function resetInventory(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user || !$user->isSuperAdmin()) {
            return back()->with('error', 'Hanya Super Administrator yang berhak mengosongkan data sistem.');
        }

        try {
            DB::beginTransaction();

            // 1. Delete physical photo files
            $items = InventoryItem::withTrashed()->get();
            foreach ($items as $item) {
                if ($item->photo_path && str_starts_with($item->photo_path, '/storage/')) {
                    $relativePath = str_replace('/storage/', '', $item->photo_path);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
            }

            // 2. Force delete all items
            InventoryItem::withTrashed()->forceDelete();

            // 3. Reset all finalizations
            DataFinalization::truncate();

            DB::commit();

            Log::warning("Super Admin {$user->name} ({$user->email}) cleared all inventory items and finalizations.");

            return back()->with('success', 'Seluruh data barang inventaris dan berita acara finalisasi berhasil dikosongkan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error clearing inventory data: ' . $e->getMessage());

            return back()->with('error', 'Gagal mengosongkan data inventaris. Silakan coba lagi.');
        }
    }

    /**
     * Clear all transactional data (inventory items, finalizations, and signed integrity pacts).
     * Preserves master data (school, buildings, rooms, categories, functions) and users.
     *
     * @param Request $request
     * @return RedirectResponse
     */
    public function resetAllTransactional(Request $request): RedirectResponse
    {
        $user = $request->user();

        if (!$user || !$user->isSuperAdmin()) {
            return back()->with('error', 'Hanya Super Administrator yang berhak mengosongkan data sistem.');
        }

        try {
            DB::beginTransaction();

            // 1. Delete physical photo files
            $items = InventoryItem::withTrashed()->get();
            foreach ($items as $item) {
                if ($item->photo_path && str_starts_with($item->photo_path, '/storage/')) {
                    $relativePath = str_replace('/storage/', '', $item->photo_path);
                    if (Storage::disk('public')->exists($relativePath)) {
                        Storage::disk('public')->delete($relativePath);
                    }
                }
            }

            // 2. Force delete inventory items
            InventoryItem::withTrashed()->forceDelete();

            // 3. Truncate finalizations and pacts
            DataFinalization::truncate();
            IntegrityPact::truncate();

            DB::commit();

            Log::warning("Super Admin {$user->name} ({$user->email}) performed full transactional data reset.");

            return back()->with('success', 'Seluruh data pendataan (inventaris, pakta integritas, & berita acara) berhasil dikosongkan.');
        } catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Error resetting all transactional data: ' . $e->getMessage());

            return back()->with('error', 'Gagal mengosongkan data sistem. Silakan coba lagi.');
        }
    }
}
