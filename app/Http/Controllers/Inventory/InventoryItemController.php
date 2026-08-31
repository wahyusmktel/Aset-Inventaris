<?php

namespace App\Http\Controllers\Inventory;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\InventoryItem;
use App\Models\ItemCategory;
use App\Models\ItemFunction;
use App\Models\Room;
use App\Services\InventoryExportService;
use Database\Seeders\InventoryItemSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

class InventoryItemController extends Controller
{
    /**
     * Display a listing of inventory items.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $condition = $request->input('condition');
        $categoryId = $request->input('category_id');
        $buildingId = $request->input('building_id');
        $roomId = $request->input('room_id');
        $functionId = $request->input('function_id');

        $items = InventoryItem::query()
            ->with(['category', 'building', 'room', 'itemFunction', 'creator', 'updater'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($c) use ($search) {
                            $c->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('room', function ($r) use ($search) {
                            $r->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('building', function ($b) use ($search) {
                            $b->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($condition, function ($query, $condition) {
                $query->where('condition', $condition);
            })
            ->when($categoryId, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($buildingId, function ($query, $buildingId) {
                $query->where('building_id', $buildingId);
            })
            ->when($roomId, function ($query, $roomId) {
                $query->where('room_id', $roomId);
            })
            ->when($functionId, function ($query, $functionId) {
                $query->where('function_id', $functionId);
            })
            ->latest('created_at')
            ->paginate(10)
            ->withQueryString();

        // Statistics
        $totalItems = InventoryItem::count();
        $totalGood = InventoryItem::where('condition', 'Baik')->count();
        $totalDamaged = InventoryItem::where('condition', 'Rusak')->count();
        $totalQuantity = InventoryItem::sum('quantity');

        $categories = ItemCategory::orderBy('code')->get(['id', 'code', 'name']);
        $buildings = Building::orderBy('code')->get(['id', 'code', 'name']);
        $rooms = Room::orderBy('code')->get(['id', 'code', 'name', 'building_id', 'capacity', 'type']);
        $functions = ItemFunction::orderBy('code')->get(['id', 'code', 'name']);

        return Inertia::render('Inventory/Item/Index', [
            'items' => $items,
            'statistics' => [
                'total_items' => $totalItems,
                'total_good' => $totalGood,
                'total_damaged' => $totalDamaged,
                'total_quantity' => (int) $totalQuantity,
            ],
            'categories' => $categories,
            'buildings' => $buildings,
            'rooms' => $rooms,
            'functions' => $functions,
            'filters' => [
                'search' => $search,
                'condition' => $condition,
                'category_id' => $categoryId,
                'building_id' => $buildingId,
                'room_id' => $roomId,
                'function_id' => $functionId,
            ],
        ]);
    }

    /**
     * Export all filtered inventory items to Excel (.xlsx).
     */
    public function exportExcel(Request $request): StreamedResponse
    {
        $search = $request->input('search');
        $condition = $request->input('condition');
        $categoryId = $request->input('category_id');
        $buildingId = $request->input('building_id');
        $roomId = $request->input('room_id');
        $functionId = $request->input('function_id');

        $query = InventoryItem::query()
            ->with(['category', 'building', 'room', 'itemFunction', 'creator', 'updater'])
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('serial_number', 'like', "%{$search}%")
                        ->orWhere('brand', 'like', "%{$search}%")
                        ->orWhere('notes', 'like', "%{$search}%")
                        ->orWhereHas('category', function ($c) use ($search) {
                            $c->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('room', function ($r) use ($search) {
                            $r->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('building', function ($b) use ($search) {
                            $b->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($condition, function ($query, $condition) {
                $query->where('condition', $condition);
            })
            ->when($categoryId, function ($query, $categoryId) {
                $query->where('category_id', $categoryId);
            })
            ->when($buildingId, function ($query, $buildingId) {
                $query->where('building_id', $buildingId);
            })
            ->when($roomId, function ($query, $roomId) {
                $query->where('room_id', $roomId);
            })
            ->when($functionId, function ($query, $functionId) {
                $query->where('function_id', $functionId);
            })
            ->orderBy('created_at', 'desc');

        $exportService = new InventoryExportService();
        return $exportService->export($query, auth()->user());
    }

    /**
     * Store a newly created inventory item in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'has_no_serial_number' => ['boolean'],
            'brand' => ['nullable', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'condition' => ['required', 'in:Baik,Rusak'],
            'category_id' => ['nullable', 'exists:item_categories,id'],
            'building_id' => ['nullable', 'exists:buildings,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'function_id' => ['nullable', 'exists:item_functions,id'],
            'notes' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:3072'],
        ], [
            'name.required' => 'Nama barang lengkap wajib diisi.',
            'quantity.required' => 'Jumlah barang wajib diisi minimal 1.',
            'condition.required' => 'Kondisi barang (Baik/Rusak) wajib dipilih.',
        ]);

        try {
            if ($validated['has_no_serial_number'] ?? false) {
                $validated['serial_number'] = null;
            }

            // Handle photo upload
            if ($request->hasFile('photo')) {
                $path = $request->file('photo')->store('inventory_photos', 'public');
                $validated['photo_path'] = '/storage/' . $path;
            }

            $validated['created_by'] = auth()->id();
            $validated['updated_by'] = auth()->id();

            $item = InventoryItem::create($validated);

            return redirect()->back()->with('success', "Barang '{$item->name}' berhasil dicatat ke inventaris sekolah.");
        } catch (\Exception $e) {
            Log::error('Error creating inventory item: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mencatat barang. Terjadi kesalahan pada server.');
        }
    }

    /**
     * Update the specified inventory item in storage.
     */
    public function update(Request $request, InventoryItem $item): RedirectResponse
    {
        $user = $request->user();

        // Role Anggota can ONLY update items they created
        if ($user && $user->isAnggota() && $item->created_by !== $user->id) {
            return redirect()->back()->with('error', 'Anda hanya memiliki izin untuk mengubah data barang yang Anda daftarkan sendiri.');
        }

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:100'],
            'has_no_serial_number' => ['boolean'],
            'brand' => ['nullable', 'string', 'max:100'],
            'quantity' => ['required', 'integer', 'min:1', 'max:10000'],
            'condition' => ['required', 'in:Baik,Rusak'],
            'category_id' => ['nullable', 'exists:item_categories,id'],
            'building_id' => ['nullable', 'exists:buildings,id'],
            'room_id' => ['nullable', 'exists:rooms,id'],
            'function_id' => ['nullable', 'exists:item_functions,id'],
            'notes' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:3072'],
        ], [
            'name.required' => 'Nama barang lengkap wajib diisi.',
            'quantity.required' => 'Jumlah barang wajib diisi minimal 1.',
            'condition.required' => 'Kondisi barang (Baik/Rusak) wajib dipilih.',
        ]);

        try {
            if ($validated['has_no_serial_number'] ?? false) {
                $validated['serial_number'] = null;
            }

            // Handle photo update
            if ($request->hasFile('photo')) {
                if ($item->photo_path) {
                    $oldPath = str_replace('/storage/', '', $item->photo_path);
                    Storage::disk('public')->delete($oldPath);
                }
                $path = $request->file('photo')->store('inventory_photos', 'public');
                $validated['photo_path'] = '/storage/' . $path;
            }

            $validated['updated_by'] = auth()->id();

            $item->update($validated);

            return redirect()->back()->with('success', "Data barang '{$item->name}' berhasil diperbarui.");
        } catch (\Exception $e) {
            Log::error('Error updating inventory item: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui data barang.');
        }
    }

    /**
     * Remove the specified inventory item (Soft Delete).
     */
    public function destroy(InventoryItem $item): RedirectResponse
    {
        $user = auth()->user();

        // Role Anggota can ONLY delete items they created
        if ($user && $user->isAnggota() && $item->created_by !== $user->id) {
            return redirect()->back()->with('error', 'Anda hanya memiliki izin untuk menghapus data barang yang Anda daftarkan sendiri.');
        }

        try {
            $name = $item->name;
            $item->update(['updated_by' => auth()->id()]);
            $item->delete(); // Soft Delete

            return redirect()->back()->with('success', "Barang '{$name}' berhasil dipindahkan ke arsip (soft delete).");
        } catch (\Exception $e) {
            Log::error('Error deleting inventory item: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus data barang.');
        }
    }

    /**
     * Bulk seed default sample inventory items.
     */
    public function bulkSeed(): RedirectResponse
    {
        if (!auth()->user()->isSuperAdmin()) {
            abort(403, 'Aksi ini hanya dapat dilakukan oleh Super Admin.');
        }

        try {
            $seeder = new InventoryItemSeeder();
            $seeder->run();

            return redirect()->back()->with('success', 'Data contoh inventaris barang sekolah berhasil disinkronisasi.');
        } catch (\Exception $e) {
            Log::error('Error bulk seeding inventory items: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat data inventaris.');
        }
    }
}
