<?php

namespace App\Http\Controllers\Governance;

use App\Http\Controllers\Controller;
use App\Models\IntegrityPact;
use App\Models\School;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

class IntegrityPactController extends Controller
{
    /**
     * Show the Pakta Integritas page.
     */
    public function show(Request $request): Response
    {
        $user = $request->user();
        $pact = $user->integrityPact;
        $school = School::where('is_active', true)->first();

        return Inertia::render('Governance/IntegrityPact', [
            'pact' => $pact,
            'school' => $school,
            'hasSigned' => $user->hasSignedPact(),
        ]);
    }

    /**
     * Agree and digitally sign Pakta Integritas.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'is_agreed' => ['required', 'accepted'],
        ], [
            'is_agreed.accepted' => 'Anda wajib menyetujui seluruh klausul pakta integritas untuk melanjutkan.',
        ]);

        $user = $request->user();

        try {
            $school = School::where('is_active', true)->first();
            $docNumber = IntegrityPact::generateDocumentNumber();
            $signedAt = now();
            $signerIp = $request->ip();

            // Generate SHA-256 digital signature hash
            $hashContent = "{$user->id}|{$docNumber}|{$signedAt->toIso8601String()}|{$signerIp}|SMK_TELKOM_LAMPUNG";
            $digitalHash = hash('sha256', $hashContent);

            // Create Integrity Pact record
            $pact = IntegrityPact::updateOrCreate(
                ['user_id' => $user->id],
                [
                    'school_id' => $school?->id,
                    'document_number' => $docNumber,
                    'is_agreed' => true,
                    'signed_at' => $signedAt,
                    'signer_ip' => $signerIp,
                    'digital_signature_hash' => $digitalHash,
                ]
            );

            // Generate Official PDF Document
            $pdf = Pdf::loadView('pdf.integrity_pact', [
                'user' => $user,
                'pact' => $pact,
                'school' => $school,
            ])->setPaper('a4', 'portrait');

            $pdfFileName = "Pakta_Integritas_" . str_replace(['/', '\\', ' '], '_', $user->name) . "_" . date('Ymd_His') . ".pdf";
            $storagePath = "pacts/{$pdfFileName}";
            Storage::disk('public')->put($storagePath, $pdf->output());

            $pact->update(['pdf_path' => "/storage/{$storagePath}"]);

            return redirect()->route('integrity-pact.show')->with('success', 'Pakta Integritas berhasil ditandatangani secara digital dan dokumen PDF telah diterbitkan.');
        } catch (\Exception $e) {
            Log::error('Error signing integrity pact: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Gagal memproses tanda tangan pakta integritas. Terjadi kesalahan server.');
        }
    }

    /**
     * Download the official signed Pakta Integritas PDF.
     */
    public function download(Request $request)
    {
        $user = $request->user();
        $pact = $user->integrityPact;

        if (!$pact || !$pact->is_agreed) {
            return redirect()->route('integrity-pact.show')->with('error', 'Anda belum menandatangani Pakta Integritas.');
        }

        $school = School::where('is_active', true)->first();

        $pdf = Pdf::loadView('pdf.integrity_pact', [
            'user' => $user,
            'pact' => $pact,
            'school' => $school,
        ])->setPaper('a4', 'portrait');

        $fileName = "Pakta_Integritas_{$user->name}.pdf";
        return $pdf->download($fileName);
    }
}
