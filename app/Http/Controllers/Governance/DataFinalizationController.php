<?php

namespace App\Http\Controllers\Governance;

use App\Http\Controllers\Controller;
use App\Models\DataFinalization;
use App\Models\InventoryItem;
use App\Models\InventoryPeriod;
use App\Models\School;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DataFinalizationController extends Controller
{
    /**
     * Display data finalization review & status page.
     */
    public function index(Request $request): Response
    {
        $user = $request->user();
        $finalization = $user->dataFinalization;
        $period = InventoryPeriod::getActivePeriod();
        $school = School::where('is_active', true)->first();

        // Real-time inventory stats recorded in this session/school
        $totalItems = InventoryItem::count();
        $totalUnits = (int) InventoryItem::sum('quantity');
        $totalGood = InventoryItem::where('condition', 'Baik')->count();
        $totalDamaged = InventoryItem::where('condition', 'Rusak')->count();

        $isCutoff = $period ? $period->isCutoffPassed() : false;

        return Inertia::render('Governance/DataFinalization', [
            'finalization' => $finalization,
            'period' => $period,
            'school' => $school,
            'isCutoff' => $isCutoff,
            'hasFinalized' => $user->hasFinalized(),
            'statistics' => [
                'total_items' => $totalItems,
                'total_units' => $totalUnits,
                'total_good' => $totalGood,
                'total_damaged' => $totalDamaged,
            ],
        ]);
    }

    /**
     * Submit and lock inventory data finalization.
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'confirm_statement' => ['required', 'accepted'],
            'statement_notes' => ['nullable', 'string', 'max:500'],
        ], [
            'confirm_statement.accepted' => 'Anda wajib mengonfirmasi kebenaran data untuk melakukan finalisasi.',
        ]);

        $user = $request->user();
        $period = InventoryPeriod::getActivePeriod();
        $school = School::where('is_active', true)->first();

        try {
            $totalItems = InventoryItem::count();
            $totalUnits = (int) InventoryItem::sum('quantity');
            $totalGood = InventoryItem::where('condition', 'Baik')->count();
            $totalDamaged = InventoryItem::where('condition', 'Rusak')->count();
            $docNumber = DataFinalization::generateDocumentNumber();
            $signedAt = now();

            $finalization = DataFinalization::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'inventory_period_id' => $period?->id,
                    'document_number' => $docNumber,
                    'total_items_recorded' => $totalItems,
                    'total_units_recorded' => $totalUnits,
                    'total_good_condition' => $totalGood,
                    'total_damaged_condition' => $totalDamaged,
                    'statement_notes' => $validated['statement_notes'] ?? null,
                    'signed_at' => $signedAt,
                    'is_finalized' => true,
                ]
            );

            // Generate Berita Acara PDF with 3 Signatures
            $pdf = Pdf::loadView('pdf.data_finalization', [
                'user' => $user,
                'finalization' => $finalization,
                'school' => $school,
                'period' => $period,
            ])->setPaper('a4', 'portrait');

            $pdfFileName = "Berita_Acara_Finalisasi_" . str_replace(['/', '\\', ' '], '_', $user->name) . "_" . date('Ymd_His') . ".pdf";
            $storagePath = "finalizations/{$pdfFileName}";
            Storage::disk('public')->put($storagePath, $pdf->output());

            $finalization->update(['pdf_path' => "/storage/{$storagePath}"]);

            return redirect()->route('data-finalization.index')->with('success', 'Finalisasi data inventaris berhasil dikunci dan Berita Acara resmi telah diterbitkan.');
        } catch (\Exception $e) {
            Log::error('Error finalizing inventory data: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses finalisasi data inventaris.');
        }
    }

    /**
     * Download the official Berita Acara Finalisasi PDF with 3 signatures.
     */
    public function download(Request $request)
    {
        $user = $request->user();
        $finalization = $user->dataFinalization;

        if (!$finalization || !$finalization->is_finalized) {
            return redirect()->route('data-finalization.index')->with('error', 'Anda belum melakukan finalisasi data.');
        }

        $school = School::where('is_active', true)->first();
        $period = $finalization->period ?: InventoryPeriod::getActivePeriod();

        $pdf = Pdf::loadView('pdf.data_finalization', [
            'user' => $user,
            'finalization' => $finalization,
            'school' => $school,
            'period' => $period,
        ])->setPaper('a4', 'portrait');

        $fileName = "Berita_Acara_Finalisasi_{$user->name}.pdf";
        return $pdf->download($fileName);
    }
}
