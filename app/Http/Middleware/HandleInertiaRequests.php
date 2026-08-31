<?php

namespace App\Http\Middleware;

use App\Models\InventoryPeriod;
use App\Models\School;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();
        $jwtToken = null;
        $userData = null;

        if ($user) {
            $jwtToken = session('jwt_token') ?: auth('api')->tokenById($user->id);
            $userData = [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'nip' => $user->nip,
                'phone' => $user->phone,
                'is_active' => $user->is_active,
                'is_super_admin' => $user->isSuperAdmin(),
                'is_anggota' => $user->isAnggota(),
                'has_signed_pact' => $user->hasSignedPact(),
                'has_finalized' => $user->hasFinalized(),
            ];
        }

        $activePeriod = InventoryPeriod::getActivePeriod();
        $activeSchool = School::where('is_active', true)->first();

        return [
            ...parent::share($request),
            'auth' => [
                'user' => $userData,
                'jwt_token' => $jwtToken,
            ],
            'governance' => [
                'active_period' => $activePeriod ? [
                    'id' => $activePeriod->id,
                    'name' => $activePeriod->name,
                    'start_date' => $activePeriod->start_date?->toISOString(),
                    'cutoff_date' => $activePeriod->cutoff_date?->toISOString(),
                    'is_cutoff_passed' => $activePeriod->isCutoffPassed(),
                ] : null,
                'active_school' => $activeSchool ? [
                    'name' => $activeSchool->name,
                    'principal_name' => $activeSchool->principal_name,
                    'principal_nip' => $activeSchool->principal_nip,
                    'kaur_it_name' => $activeSchool->kaur_it_name,
                    'kaur_it_nip' => $activeSchool->kaur_it_nip,
                ] : null,
            ],
            'flash' => [
                'success' => fn () => $request->session()->get('success'),
                'error' => fn () => $request->session()->get('error'),
                'warning' => fn () => $request->session()->get('warning'),
                'info' => fn () => $request->session()->get('info'),
            ],
        ];
    }
}
