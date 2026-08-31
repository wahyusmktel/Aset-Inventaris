<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Building;
use Database\Seeders\BuildingSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BuildingController extends Controller
{
    /**
     * Display a listing of buildings.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $buildings = Building::query()
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

        $count = Building::count();
        $nextSuggestedName = 'Gedung ' . ($count + 1);

        return Inertia::render('MasterData/Building/Index', [
            'buildings' => $buildings,
            'filters' => [
                'search' => $search,
            ],
            'nextCode' => Building::generateNextCode(),
            'nextName' => $nextSuggestedName,
        ]);
    }

    /**
     * Store a newly created building in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:buildings,code'],
            'name' => ['required', 'string', 'max:255'],
            'total_floors' => ['nullable', 'integer', 'min:1', 'max:50'],
            'description' => ['nullable', 'string'],
        ], [
            'code.required' => 'Kode gedung wajib diisi.',
            'code.unique' => 'Kode gedung sudah digunakan.',
            'name.required' => 'Nama gedung wajib diisi.',
        ]);

        try {
            $validated['total_floors'] = $validated['total_floors'] ?? 1;
            Building::create($validated);
            return redirect()->back()->with('success', "Gedung '{$validated['name']}' berhasil ditambahkan.");
        } catch (\Exception $e) {
            Log::error('Error creating building: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan data gedung. Terjadi kesalahan server.');
        }
    }

    /**
     * Update the specified building in storage.
     */
    public function update(Request $request, Building $building): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('buildings', 'code')->ignore($building->id)],
            'name' => ['required', 'string', 'max:255'],
            'total_floors' => ['nullable', 'integer', 'min:1', 'max:50'],
            'description' => ['nullable', 'string'],
        ], [
            'code.required' => 'Kode gedung wajib diisi.',
            'code.unique' => 'Kode gedung sudah digunakan.',
            'name.required' => 'Nama gedung wajib diisi.',
        ]);

        try {
            $validated['total_floors'] = $validated['total_floors'] ?? 1;
            $building->update($validated);
            return redirect()->back()->with('success', "Data '{$validated['name']}' berhasil diperbarui.");
        } catch (\Exception $e) {
            Log::error('Error updating building: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui data gedung.');
        }
    }

    /**
     * Remove the specified building from storage.
     */
    public function destroy(Building $building): RedirectResponse
    {
        try {
            $name = $building->name;
            $building->delete();
            return redirect()->back()->with('success', "Data gedung '{$name}' berhasil dihapus.");
        } catch (\Exception $e) {
            Log::error('Error deleting building: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus data gedung.');
        }
    }

    /**
     * Bulk seed default Gedung 1 to Gedung 5.
     */
    public function bulkSeed(): RedirectResponse
    {
        try {
            $seeder = new BuildingSeeder();
            $seeder->run();
            return redirect()->back()->with('success', 'Data standar Gedung 1 s/d Gedung 5 berhasil dimuat.');
        } catch (\Exception $e) {
            Log::error('Error bulk seeding buildings: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat data gedung.');
        }
    }
}
