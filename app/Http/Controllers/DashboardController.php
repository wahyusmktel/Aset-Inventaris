<?php

namespace App\Http\Controllers;

use App\Models\Building;
use App\Models\InventoryItem;
use App\Models\ItemCategory;
use App\Models\ItemFunction;
use App\Models\Room;
use App\Models\School;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    /**
     * Display the application dashboard with comprehensive live statistics & chart data.
     */
    public function index(Request $request): Response
    {
        // 1. School Information
        $activeSchool = School::where('is_active', true)->first();

        // 2. Summary KPI Metrics
        $totalItems = InventoryItem::count();
        $totalQuantity = (int) InventoryItem::sum('quantity');
        $goodCondition = InventoryItem::where('condition', 'Baik')->count();
        $damagedCondition = InventoryItem::where('condition', 'Rusak')->count();
        $goodPercent = $totalItems > 0 ? round(($goodCondition / $totalItems) * 100, 1) : 100;

        $totalCategories = ItemCategory::count();
        $totalBuildings = Building::count();
        $totalRooms = Room::count();
        $totalFunctions = ItemFunction::count();
        $totalUsers = User::count();

        // 3. Chart 1: Distribution by Category (Top 7)
        $categoryData = ItemCategory::withCount('inventoryItems')
            ->orderByDesc('inventory_items_count')
            ->limit(7)
            ->get()
            ->map(function ($cat) {
                return [
                    'name' => $cat->name,
                    'code' => $cat->code,
                    'count' => $cat->inventory_items_count,
                ];
            });

        // 4. Chart 2: Distribution by Rooms / Labs (Top 6)
        $roomData = Room::withCount('inventoryItems')
            ->orderByDesc('inventory_items_count')
            ->limit(6)
            ->get()
            ->map(function ($rm) {
                return [
                    'name' => $rm->name,
                    'count' => $rm->inventory_items_count,
                ];
            });

        // 5. Chart 3: Distribution by Function
        $functionData = ItemFunction::withCount('inventoryItems')
            ->orderByDesc('inventory_items_count')
            ->limit(6)
            ->get()
            ->map(function ($fn) {
                return [
                    'name' => $fn->name,
                    'count' => $fn->inventory_items_count,
                ];
            });

        // 6. Recent 6 Audited Items
        $recentItems = InventoryItem::with(['category', 'building', 'room', 'creator'])
            ->latest('created_at')
            ->limit(6)
            ->get();

        return Inertia::render('Dashboard', [
            'activeSchool' => $activeSchool,
            'metrics' => [
                'total_items' => $totalItems,
                'total_quantity' => $totalQuantity,
                'good_condition' => $goodCondition,
                'damaged_condition' => $damagedCondition,
                'good_percent' => $goodPercent,
                'total_categories' => $totalCategories,
                'total_buildings' => $totalBuildings,
                'total_rooms' => $totalRooms,
                'total_functions' => $totalFunctions,
                'total_users' => $totalUsers,
            ],
            'chartData' => [
                'categories' => $categoryData,
                'rooms' => $roomData,
                'functions' => $functionData,
                'condition' => [
                    'baik' => $goodCondition,
                    'rusak' => $damagedCondition,
                ],
            ],
            'recentItems' => $recentItems,
        ]);
    }
}
