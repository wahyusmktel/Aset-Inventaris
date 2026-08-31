<?php

namespace App\Http\Controllers\Governance;

use App\Http\Controllers\Controller;
use App\Models\InventoryPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Inertia\Inertia;
use Inertia\Response;

class InventoryPeriodController extends Controller
{
    /**
     * Display a listing of inventory periods & cutoff settings.
     */
    public function index(Request $request): Response
    {
        $periods = InventoryPeriod::latest('created_at')->paginate(10);
        $activePeriod = InventoryPeriod::getActivePeriod();

        return Inertia::render('Governance/PeriodSetting', [
            'periods' => $periods,
            'activePeriod' => $activePeriod,
        ]);
    }

    /**
     * Store a newly created inventory period.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'cutoff_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ], [
            'name.required' => 'Nama periode pengadaan wajib diisi.',
            'start_date.required' => 'Tanggal mulai pendataan wajib diisi.',
            'cutoff_date.required' => 'Batas waktu cutoff wajib diisi.',
            'cutoff_date.after' => 'Batas waktu cutoff harus setelah tanggal mulai.',
        ]);

        try {
            if ($validated['is_active'] ?? false) {
                InventoryPeriod::query()->update(['is_active' => false]);
            }

            $period = InventoryPeriod::create($validated);

            return redirect()->back()->with('success', "Periode pendataan '{$period->name}' berhasil dibuat.");
        } catch (\Exception $e) {
            Log::error('Error creating inventory period: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal membuat periode pendataan.');
        }
    }

    /**
     * Update the specified inventory period.
     */
    public function update(Request $request, InventoryPeriod $period): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'start_date' => ['required', 'date'],
            'cutoff_date' => ['required', 'date', 'after:start_date'],
            'is_active' => ['boolean'],
            'notes' => ['nullable', 'string'],
        ], [
            'name.required' => 'Nama periode pengadaan wajib diisi.',
            'start_date.required' => 'Tanggal mulai pendataan wajib diisi.',
            'cutoff_date.required' => 'Batas waktu cutoff wajib diisi.',
            'cutoff_date.after' => 'Batas waktu cutoff harus setelah tanggal mulai.',
        ]);

        try {
            if (($validated['is_active'] ?? false) && !$period->is_active) {
                InventoryPeriod::where('id', '!=', $period->id)->update(['is_active' => false]);
            }

            $period->update($validated);

            return redirect()->back()->with('success', "Pengaturan periode '{$period->name}' berhasil diperbarui.");
        } catch (\Exception $e) {
            Log::error('Error updating inventory period: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memperbarui periode pendataan.');
        }
    }

    /**
     * Remove the specified inventory period.
     */
    public function destroy(InventoryPeriod $period): RedirectResponse
    {
        try {
            $name = $period->name;
            $period->delete();
            return redirect()->back()->with('success', "Periode '{$name}' berhasil dihapus.");
        } catch (\Exception $e) {
            Log::error('Error deleting inventory period: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal menghapus periode.');
        }
    }
}
