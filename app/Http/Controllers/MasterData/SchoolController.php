<?php

namespace App\Http\Controllers\MasterData;

use App\Http\Controllers\Controller;
use App\Models\School;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class SchoolController extends Controller
{
    /**
     * Display a listing of schools.
     */
    public function index(Request $request): Response
    {
        $search = $request->input('search');

        $schools = School::query()
            ->when($search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('code', 'like', "%{$search}%")
                        ->orWhere('name', 'like', "%{$search}%")
                        ->orWhere('principal_name', 'like', "%{$search}%")
                        ->orWhere('kaur_it_name', 'like', "%{$search}%")
                        ->orWhere('address', 'like', "%{$search}%");
                });
            })
            ->orderByDesc('is_active')
            ->orderBy('name')
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('MasterData/School/Index', [
            'schools' => $schools,
            'filters' => [
                'search' => $search,
            ],
        ]);
    }

    /**
     * Store a newly created school in storage.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:schools,code'],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'latitude' => ['nullable', 'string', 'max:50'],
            'longitude' => ['nullable', 'string', 'max:50'],
            'principal_name' => ['required', 'string', 'max:255'],
            'principal_nip' => ['nullable', 'string', 'max:50'],
            'kaur_it_name' => ['nullable', 'string', 'max:255'],
            'kaur_it_nip' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ], [
            'code.required' => 'Kode lembaga wajib diisi.',
            'code.unique' => 'Kode lembaga sudah digunakan oleh sekolah lain.',
            'name.required' => 'Nama lembaga wajib diisi.',
            'address.required' => 'Alamat lembaga wajib diisi.',
            'principal_name.required' => 'Nama kepala sekolah wajib diisi.',
        ]);

        try {
            DB::transaction(function () use ($validated) {
                $isActive = $validated['is_active'] ?? false;

                if ($isActive) {
                    School::where('is_active', true)->update(['is_active' => false]);
                }

                School::create($validated);
            });

            return redirect()->back()->with('success', 'Data sekolah baru berhasil ditambahkan.');
        } catch (\Exception $e) {
            Log::error('Error creating school: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menyimpan data sekolah. Terjadi kesalahan pada server.');
        }
    }

    /**
     * Update the specified school in storage.
     */
    public function update(Request $request, School $school): RedirectResponse
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', Rule::unique('schools', 'code')->ignore($school->id)],
            'name' => ['required', 'string', 'max:255'],
            'address' => ['required', 'string'],
            'latitude' => ['nullable', 'string', 'max:50'],
            'longitude' => ['nullable', 'string', 'max:50'],
            'principal_name' => ['required', 'string', 'max:255'],
            'principal_nip' => ['nullable', 'string', 'max:50'],
            'kaur_it_name' => ['nullable', 'string', 'max:255'],
            'kaur_it_nip' => ['nullable', 'string', 'max:50'],
            'is_active' => ['boolean'],
        ], [
            'code.required' => 'Kode lembaga wajib diisi.',
            'code.unique' => 'Kode lembaga sudah digunakan oleh sekolah lain.',
            'name.required' => 'Nama lembaga wajib diisi.',
            'address.required' => 'Alamat lembaga wajib diisi.',
            'principal_name.required' => 'Nama kepala sekolah wajib diisi.',
        ]);

        try {
            DB::transaction(function () use ($validated, $school) {
                $isActive = $validated['is_active'] ?? false;

                if ($isActive && !$school->is_active) {
                    School::where('is_active', true)->where('id', '!=', $school->id)->update(['is_active' => false]);
                }

                $school->update($validated);
            });

            return redirect()->back()->with('success', 'Data sekolah berhasil diperbarui.');
        } catch (\Exception $e) {
            Log::error('Error updating school: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui data sekolah.');
        }
    }

    /**
     * Remove the specified school from storage.
     */
    public function destroy(School $school): RedirectResponse
    {
        try {
            if ($school->is_active) {
                return redirect()->back()->with('error', 'Tidak dapat menghapus lembaga sekolah yang sedang aktif.');
            }

            $school->delete();
            return redirect()->back()->with('success', 'Data sekolah berhasil dihapus.');
        } catch (\Exception $e) {
            Log::error('Error deleting school: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus data sekolah.');
        }
    }

    /**
     * Set the specified school as the only active school.
     */
    public function activate(School $school): RedirectResponse
    {
        try {
            DB::transaction(function () use ($school) {
                School::where('is_active', true)->where('id', '!=', $school->id)->update(['is_active' => false]);
                $school->update(['is_active' => true]);
            });

            return redirect()->back()->with('success', "Lembaga '{$school->name}' berhasil diaktifkan.");
        } catch (\Exception $e) {
            Log::error('Error activating school: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal mengaktifkan lembaga sekolah.');
        }
    }
}
