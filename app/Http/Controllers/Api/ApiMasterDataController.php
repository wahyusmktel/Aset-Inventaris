<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Building;
use App\Models\ItemCategory;
use App\Models\ItemFunction;
use App\Models\Room;
use App\Models\School;
use Illuminate\Http\JsonResponse;

class ApiMasterDataController extends Controller
{
    /**
     * Get all master data lookup options in a single structured response.
     */
    public function all(): JsonResponse
    {
        $categories = ItemCategory::orderBy('code')->get(['id', 'code', 'name']);
        $buildings = Building::orderBy('code')->get(['id', 'code', 'name']);
        $rooms = Room::orderBy('code')->get(['id', 'code', 'name', 'building_id']);
        $functions = ItemFunction::orderBy('code')->get(['id', 'code', 'name']);
        $school = School::where('is_active', true)->first();

        return response()->json([
            'success' => true,
            'data' => [
                'categories' => $categories,
                'buildings' => $buildings,
                'rooms' => $rooms,
                'functions' => $functions,
                'school' => $school,
            ],
        ]);
    }
}
