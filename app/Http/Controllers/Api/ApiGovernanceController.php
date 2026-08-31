<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataFinalization;
use App\Models\IntegrityPact;
use App\Models\InventoryItem;
use App\Models\InventoryPeriod;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApiGovernanceController extends Controller
{
    /**
     * Sign integrity pact digitally on mobile.
     */
    public function signPact(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $request->validate([
            'is_agreed' => ['required', 'accepted'],
        ]);

        $activeSchool = School::where('is_active', true)->first();
        $schoolName = $activeSchool ? $activeSchool->name : 'SMK Telkom Lampung';

        $pact = IntegrityPact::firstOrCreate(
            ['user_id' => $user->id],
            [
                'document_number' => IntegrityPact::generateDocumentNumber(),
                'school_name' => $schoolName,
                'user_name' => $user->name,
                'user_nip' => $user->nip,
                'user_role' => $user->role,
                'digital_signature_hash' => hash('sha256', $user->id . '|' . $user->email . '|' . now()->toIso8601String()),
                'signed_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent() ?? 'Mobile App',
                'is_agreed' => true,
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Pakta Integritas berhasil ditandatangani secara digital.',
            'data' => $pact,
        ]);
    }

    /**
     * Finalize inventory data & create Berita Acara from mobile.
     */
    public function finalize(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        // Check if cutoff has passed
        $activePeriod = InventoryPeriod::getActivePeriod();
        if ($activePeriod && $activePeriod->isCutoffPassed()) {
            return response()->json([
                'success' => false,
                'message' => 'Batas waktu cut-off telah berakhir.',
            ], 403);
        }

        $activeSchool = School::where('is_active', true)->first();
        $totalItems = InventoryItem::count();
        $totalQuantity = (int) InventoryItem::sum('quantity');
        $totalGood = InventoryItem::where('condition', 'Baik')->count();
        $totalDamaged = InventoryItem::where('condition', 'Rusak')->count();

        $finalization = DataFinalization::firstOrCreate(
            ['user_id' => $user->id],
            [
                'document_number' => DataFinalization::generateDocumentNumber(),
                'school_id' => $activeSchool ? $activeSchool->id : null,
                'school_name' => $activeSchool ? $activeSchool->name : 'SMK Telkom Lampung',
                'principal_name' => $activeSchool ? $activeSchool->principal_name : 'Kepala Sekolah',
                'principal_nip' => $activeSchool ? $activeSchool->principal_nip : null,
                'kaur_it_name' => $activeSchool ? $activeSchool->kaur_it_name : 'Kaur IT / PIC Sarpras',
                'kaur_it_nip' => $activeSchool ? $activeSchool->kaur_it_nip : null,
                'officer_name' => $user->name,
                'officer_nip' => $user->nip,
                'officer_role' => $user->role,
                'total_items' => $totalItems,
                'total_quantity' => $totalQuantity,
                'total_good_condition' => $totalGood,
                'total_damaged_condition' => $totalDamaged,
                'notes' => $request->input('notes', 'Finalisasi pendataan via aplikasi mobile.'),
                'digital_signature_hash' => hash('sha256', $user->id . '|finalisasi|' . now()->toIso8601String()),
                'finalized_at' => now(),
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent() ?? 'Mobile App',
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Data inventaris berhasil difinalisasi.',
            'data' => $finalization,
        ]);
    }
}
