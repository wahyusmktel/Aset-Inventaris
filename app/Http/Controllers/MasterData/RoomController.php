<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\Room;
use Database\Seeders\RoomSeeder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class RoomController extends Controller
{
    /**
     * Display a listing of rooms.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');
        $buildingId = $request->input('building_id');

        $rooms = Room::query()
            ->with('building')
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('type', 'like', "%{$search}%")
                        ->orWhere('description', 'like', "%{$search}%")
                        ->orWhereHas('building', function ($b) use ($search) {
                            $b->where('name', 'like', "%{$search}%");
                        });
                });
            })
            ->when($buildingId, function ($query, $buildingId) {
                $query->where('building_id', $buildingId);
            })
            ->orderBy('code')
            ->paginate(10)
            ->withQueryString();

        $buildings = Building::orderBy('code')->get(['id', 'code', 'name']);

        return Inertia::render('MasterData/Room/Index', [
            'rooms' => $rooms,
            'buildings' => $buildings,
            'filters' => [
                'search' => $search,
                'building_id' => $buildingId,
            ],
            'nextCode' => Room::generateNextCode(),
        ]);
    }

    /**
     * Store a newly created room in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:rooms,code'],
            'name' => ['required', 'string', 'max:255'],
            'building_id' => ['nullable', 'exists:buildings,id'],
            'floor' => ['nullable', 'integer', 'min:1', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'type' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ], [
            'code.required' => 'Kode ruangan wajib diisi.',
            'code.unique' => 'Kode ruangan sudah digunakan.',
            'name.required' => 'Nama ruangan wajib diisi.',
        ]);

        try {
            $validated['floor'] = $validated['floor'] ?? 1;
            Room::create($validated);
            return redirect()->back()->with('success', "Ruangan '{$validated['name']}' berhasil ditambahkan.");
        } catch (\Exception $e) {
            Log::error('Error creating room: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan data ruangan. Terjadi kesalahan server.');
        }
    }

    /**
     * Update the specified room in storage.
     */
    public function update(Request $request, Room $room): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('rooms', 'code')->ignore($room->id)],
            'name' => ['required', 'string', 'max:255'],
            'building_id' => ['nullable', 'exists:buildings,id'],
            'floor' => ['nullable', 'integer', 'min:1', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:1000'],
            'type' => ['nullable', 'string', 'max:100'],
            'description' => ['nullable', 'string'],
        ], [
            'code.required' => 'Kode ruangan wajib diisi.',
            'code.unique' => 'Kode ruangan sudah digunakan.',
            'name.required' => 'Nama ruangan wajib diisi.',
        ]);

        try {
            $validated['floor'] = $validated['floor'] ?? 1;
            $room->update($validated);
            return redirect()->back()->with('success', "Ruangan '{$validated['name']}' berhasil diperbarui.");
        } catch (\Exception $e) {
            Log::error('Error updating room: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui data ruangan.');
        }
    }

    /**
     * Remove the specified room from storage.
     */
    public function destroy(Room $room): RedirectResponse
    {
        try {
            $name = $room->name;
            $room->delete();
            return redirect()->back()->with('success', "Ruangan '{$name}' berhasil dihapus.");
        } catch (\Exception $e) {
            Log::error('Error deleting room: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus data ruangan.');
        }
    }

    /**
     * Bulk seed default complete school rooms.
     */
    public function bulkSeed(): RedirectResponse
    {
        try {
            $seeder = new RoomSeeder();
            $seeder->run();
            return redirect()->back()->with('success', 'Data lengkap 27 ruangan sekolah standar berhasil dimuat.');
        } catch (\Exception $e) {
            Log::error('Error bulk seeding rooms: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memuat data ruangan.');
        }
    }
}
