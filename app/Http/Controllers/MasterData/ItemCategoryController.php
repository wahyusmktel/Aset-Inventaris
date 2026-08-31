<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\ItemCategory;
use Database\Seeders\ItemCategorySeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ItemCategoryController extends Controller
{
    /**
     * Display a listing of item categories.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $categories = ItemCategory::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%");
                });
            })
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('MasterData/ItemCategory/Index', [
            'categories' => $categories,
            'filters' => [
                'search' => $search,
            ],
            'nextCode' => ItemCategory::generateNextCode(),
        ]);
    }

    /**
     * Store a newly created item category in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:item_categories,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ], [
            'code.required' => 'Kode kategori barang wajib diisi.',
            'code.unique' => 'Kode kategori barang sudah digunakan.',
            'name.required' => 'Nama kategori barang wajib diisi.',
        ]);

        try {
            ItemCategory::create($validated);
            return redirect()->back()->with('success', "Kategori barang '{$validated['name']}' berhasil ditambahkan.");
        } catch (\Exception $e) {
            Log::error('Error creating item category: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan kategori barang. Terjadi kesalahan server.');
        }
    }

    /**
     * Update the specified item category in storage.
     */
    public function update(Request $request, ItemCategory $itemCategory): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('item_categories', 'code')->ignore($itemCategory->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ], [
            'code.required' => 'Kode kategori barang wajib diisi.',
            'code.unique' => 'Kode kategori barang sudah digunakan.',
            'name.required' => 'Nama kategori barang wajib diisi.',
        ]);

        try {
            $itemCategory->update($validated);
            return redirect()->back()->with('success', "Kategori barang '{$validated['name']}' berhasil diperbarui.");
        } catch (\Exception $e) {
            Log::error('Error updating item category: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui kategori barang.');
        }
    }

    /**
     * Remove the specified item category from storage.
     */
    public function destroy(ItemCategory $itemCategory): RedirectResponse
    {
        try {
            $name = $itemCategory->name;
            $itemCategory->delete();
            return redirect()->back()->with('success', "Kategori '{$name}' berhasil dihapus.");
        } catch (\Exception $e) {
            Log::error('Error deleting item category: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus kategori barang.');
        }
    }

    /**
     * Reset / Bulk seed default SMK Telkom Lampung asset categories.
     */
    public function bulkSeed(): RedirectResponse
    {
        try {
            $seeder = new ItemCategorySeeder();
            $seeder->run();
            return redirect()->back()->with('success', 'Data masal kategori barang standar SMK Telkom Lampung berhasil dimuat.');
        } catch (\Exception $e) {
            Log::error('Error bulk seeding categories: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat data masal kategori barang.');
        }
    }
}
