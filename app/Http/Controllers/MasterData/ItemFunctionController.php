<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\ItemFunction;
use Database\Seeders\ItemFunctionSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class ItemFunctionController extends Controller
{
    /**
     * Display a listing of item functions.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $functions = ItemFunction::query()
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

        return Inertia::render('MasterData/ItemFunction/Index', [
            'functions' => $functions,
            'filters' => [
                'search' => $search,
            ],
            'nextCode' => ItemFunction::generateNextCode(),
        ]);
    }

    /**
     * Store a newly created item function in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:item_functions,code'],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ], [
            'code.required' => 'Kode fungsi barang wajib diisi.',
            'code.unique' => 'Kode fungsi barang sudah digunakan.',
            'name.required' => 'Nama fungsi barang wajib diisi.',
        ]);

        try {
            ItemFunction::create($validated);
            return redirect()->back()->with('success', "Fungsi barang '{$validated['name']}' berhasil ditambahkan.");
        } catch (\Exception $e) {
            Log::error('Error creating item function: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan data fungsi barang. Terjadi kesalahan server.');
        }
    }

    /**
     * Update the specified item function in storage.
     */
    public function update(Request $request, ItemFunction $itemFunction): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('item_functions', 'code')->ignore($itemFunction->id)],
            'name' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string'],
        ], [
            'code.required' => 'Kode fungsi barang wajib diisi.',
            'code.unique' => 'Kode fungsi barang sudah digunakan.',
            'name.required' => 'Nama fungsi barang wajib diisi.',
        ]);

        try {
            $itemFunction->update($validated);
            return redirect()->back()->with('success', "Fungsi barang '{$validated['name']}' berhasil diperbarui.");
        } catch (\Exception $e) {
            Log::error('Error updating item function: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui data fungsi barang.');
        }
    }

    /**
     * Remove the specified item function from storage.
     */
    public function destroy(ItemFunction $itemFunction): RedirectResponse
    {
        try {
            $name = $itemFunction->name;
            $itemFunction->delete();
            return redirect()->back()->with('success', "Fungsi barang '{$name}' berhasil dihapus.");
        } catch (\Exception $e) {
            Log::error('Error deleting item function: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus data fungsi barang.');
        }
    }

    /**
     * Bulk seed default 14 item functions.
     */
    public function bulkSeed(): RedirectResponse
    {
        try {
            $seeder = new ItemFunctionSeeder();
            $seeder->run();
            return redirect()->back()->with('success', 'Data standar fungsi barang sekolah berhasil dimuat.');
        } catch (\Exception $e) {
            Log::error('Error bulk seeding item functions: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat data fungsi barang.');
        }
    }
}
