<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\DataFinalization;
use App\Models\IntegrityPact;
use App\Models\InventoryPeriod;
use App\Models\School;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class ApiAuthController extends Controller
{
    /**
     * Authenticate user and return JWT bearer token.
     */
    public function login(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $credentials = $request->only('email', 'password');

        if (!$token = auth('api')->attempt($credentials)) {
            return response()->json([
                'success' => false,
                'message' => 'Alamat email atau kata sandi tidak valid.',
            ], 401);
        }

        $user = auth('api')->user();

        if (!$user->is_active) {
            auth('api')->logout();
            return response()->json([
                'success' => false,
                'message' => 'Akun Anda dinonaktifkan oleh administrator.',
            ], 403);
        }

        $activeSchool = School::where('is_active', true)->first();
        $activePeriod = InventoryPeriod::getActivePeriod();
        $hasSignedPact = IntegrityPact::where('user_id', $user->id)->exists();
        $hasFinalized = DataFinalization::where('user_id', $user->id)->exists();

        return response()->json([
            'success' => true,
            'message' => 'Login berhasil',
            'access_token' => $token,
            'token_type' => 'bearer',
            'expires_in' => auth('api')->factory()->getTTL() * 60,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'nip' => $user->nip,
                'phone' => $user->phone,
            ],
            'governance' => [
                'has_signed_pact' => $hasSignedPact,
                'has_finalized' => $hasFinalized,
                'is_cutoff_passed' => $activePeriod ? $activePeriod->isCutoffPassed() : false,
                'active_period' => $activePeriod,
                'active_school' => $activeSchool,
            ],
        ]);
    }

    /**
     * Get authenticated user profile & governance status.
     */
    public function me(): JsonResponse
    {
        $user = auth('api')->user();

        if (!$user) {
            return response()->json(['success' => false, 'message' => 'Unauthenticated'], 401);
        }

        $activeSchool = School::where('is_active', true)->first();
        $activePeriod = InventoryPeriod::getActivePeriod();
        $hasSignedPact = IntegrityPact::where('user_id', $user->id)->exists();
        $hasFinalized = DataFinalization::where('user_id', $user->id)->exists();

        return response()->json([
            'success' => true,
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'nip' => $user->nip,
                'phone' => $user->phone,
            ],
            'governance' => [
                'has_signed_pact' => $hasSignedPact,
                'has_finalized' => $hasFinalized,
                'is_cutoff_passed' => $activePeriod ? $activePeriod->isCutoffPassed() : false,
                'active_period' => $activePeriod,
                'active_school' => $activeSchool,
            ],
        ]);
    }

    /**
     * Logout and invalidate token.
     */
    public function logout(): JsonResponse
    {
        auth('api')->logout();

        return response()->json([
            'success' => true,
            'message' => 'Berhasil keluar dari sesi mobile.',
        ]);
    }
}
