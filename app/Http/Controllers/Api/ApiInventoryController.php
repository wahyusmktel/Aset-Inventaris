<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\InventoryItem;
use App\Models\InventoryPeriod;
use App\Models\Room;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class ApiInventoryController extends Controller
{
    /**
     * Get paginated inventory items with filtering and search.
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        $query = InventoryItem::with(['category', 'building', 'room', 'itemFunction', 'creator'])
            ->orderBy('created_at', 'desc');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('serial_number', 'like', "%{$search}%")
                  ->orWhere('brand', 'like', "%{$search}%");
            });
        }

        if ($request->filled('condition')) {
            $query->where('condition', $request->input('condition'));
        }

        if ($request->filled('category_id')) {
            $query->where('category_id', $request->input('category_id'));
        }

        if ($request->filled('building_id')) {
            $query->where('building_id', $request->input('building_id'));
        }

        if ($request->filled('room_id')) {
            $query->where('room_id', $request->input('room_id'));
        }

        if ($request->filled('function_id')) {
            $query->where('function_id', $request->input('function_id'));
        }

        $perPage = (int) $request->input('per_page', 10);
        $items = $query->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $items,
        ]);
    }

    /**
     * Store a newly created inventory item via mobile app.
     */
    public function store(Request $request): JsonResponse
    {
        $user = auth('api')->user();

        // Check if cutoff has passed
        $activePeriod = InventoryPeriod::getActivePeriod();
        if ($activePeriod && $activePeriod->isCutoffPassed() && $user->isAnggota()) {
            return response()->json([
                'success' => false,
                'message' => 'Batas waktu cut-off pendataan inventaris telah berakhir.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'has_no_serial_number' => ['nullable', 'boolean'],
            'brand' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'condition' => ['required', 'in:Baik,Rusak'],
            'category_id' => ['nullable', 'uuid', 'exists:item_categories,id'],
            'building_id' => ['nullable', 'uuid', 'exists:buildings,id'],
            'room_id' => ['nullable', 'uuid', 'exists:rooms,id'],
            'function_id' => ['nullable', 'uuid', 'exists:item_functions,id'],
            'notes' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:10240'], // up to 10MB raw, compressed by mobile
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi input gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $file = $request->file('photo');
            $filename = 'inv_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('inventory_photos', $filename, 'public');
            $photoPath = '/storage/' . $path;
        }

        $hasNoSn = filter_var($request->input('has_no_serial_number', false), FILTER_VALIDATE_BOOLEAN);

        $item = InventoryItem::create([
            'name' => $validated['name'],
            'serial_number' => $hasNoSn ? null : ($validated['serial_number'] ?? null),
            'has_no_serial_number' => $hasNoSn,
            'brand' => $validated['brand'] ?? null,
            'quantity' => $validated['quantity'],
            'condition' => $validated['condition'],
            'photo_path' => $photoPath,
            'category_id' => $validated['category_id'] ?? null,
            'building_id' => $validated['building_id'] ?? null,
            'room_id' => $validated['room_id'] ?? null,
            'function_id' => $validated['function_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'created_by' => $user->id,
            'updated_by' => $user->id,
        ]);

        $item->load(['category', 'building', 'room', 'itemFunction', 'creator']);

        return response()->json([
            'success' => true,
            'message' => 'Barang inventaris berhasil dicatat.',
            'data' => $item,
        ], 201);
    }

    /**
     * Get single inventory item detail.
     */
    public function show(string $id): JsonResponse
    {
        $item = InventoryItem::with(['category', 'building', 'room', 'itemFunction', 'creator', 'updater'])
            ->find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Data barang tidak ditemukan.',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $item,
        ]);
    }

    /**
     * Update inventory item.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        $user = auth('api')->user();
        $item = InventoryItem::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Data barang tidak ditemukan.',
            ], 404);
        }

        // Ownership authorization check
        if ($user->isAnggota() && $item->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya memiliki izin untuk mengubah data barang yang Anda daftarkan sendiri.',
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'serial_number' => ['nullable', 'string', 'max:255'],
            'has_no_serial_number' => ['nullable', 'boolean'],
            'brand' => ['nullable', 'string', 'max:255'],
            'quantity' => ['required', 'integer', 'min:1'],
            'condition' => ['required', 'in:Baik,Rusak'],
            'category_id' => ['nullable', 'uuid', 'exists:item_categories,id'],
            'building_id' => ['nullable', 'uuid', 'exists:buildings,id'],
            'room_id' => ['nullable', 'uuid', 'exists:rooms,id'],
            'function_id' => ['nullable', 'uuid', 'exists:item_functions,id'],
            'notes' => ['nullable', 'string'],
            'photo' => ['nullable', 'image', 'max:10240'],
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Validasi gagal',
                'errors' => $validator->errors(),
            ], 422);
        }

        $validated = $validator->validated();

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($item->photo_path && str_starts_with($item->photo_path, '/storage/')) {
                $oldRel = str_replace('/storage/', '', $item->photo_path);
                Storage::disk('public')->delete($oldRel);
            }
            $file = $request->file('photo');
            $filename = 'inv_' . Str::uuid() . '.' . $file->getClientOriginalExtension();
            $path = $file->storeAs('inventory_photos', $filename, 'public');
            $item->photo_path = '/storage/' . $path;
        }

        $hasNoSn = filter_var($request->input('has_no_serial_number', false), FILTER_VALIDATE_BOOLEAN);

        $item->update([
            'name' => $validated['name'],
            'serial_number' => $hasNoSn ? null : ($validated['serial_number'] ?? null),
            'has_no_serial_number' => $hasNoSn,
            'brand' => $validated['brand'] ?? null,
            'quantity' => $validated['quantity'],
            'condition' => $validated['condition'],
            'category_id' => $validated['category_id'] ?? null,
            'building_id' => $validated['building_id'] ?? null,
            'room_id' => $validated['room_id'] ?? null,
            'function_id' => $validated['function_id'] ?? null,
            'notes' => $validated['notes'] ?? null,
            'updated_by' => $user->id,
        ]);

        $item->load(['category', 'building', 'room', 'itemFunction', 'creator', 'updater']);

        return response()->json([
            'success' => true,
            'message' => 'Data barang berhasil diperbarui.',
            'data' => $item,
        ]);
    }

    /**
     * Delete (soft-delete) an inventory item.
     */
    public function destroy(string $id): JsonResponse
    {
        $user = auth('api')->user();
        $item = InventoryItem::find($id);

        if (!$item) {
            return response()->json([
                'success' => false,
                'message' => 'Data barang tidak ditemukan.',
            ], 404);
        }

        // Ownership authorization check
        if ($user->isAnggota() && $item->created_by !== $user->id) {
            return response()->json([
                'success' => false,
                'message' => 'Anda hanya memiliki izin untuk menghapus data barang yang Anda daftarkan sendiri.',
            ], 403);
        }

        $item->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data barang berhasil dipindahkan ke arsip.',
        ]);
    }

    /**
     * Mobile Dashboard KPI Statistics.
     */
    public function stats(): JsonResponse
    {
        $totalItems = InventoryItem::count();
        $totalQuantity = (int) InventoryItem::sum('quantity');
        $goodCondition = InventoryItem::where('condition', 'Baik')->count();
        $damagedCondition = InventoryItem::where('condition', 'Rusak')->count();
        $totalRooms = Room::count();

        $goodPercent = $totalItems > 0 ? round(($goodCondition / $totalItems) * 100, 1) : 100;

        return response()->json([
            'success' => true,
            'data' => [
                'total_items' => $totalItems,
                'total_quantity' => $totalQuantity,
                'good_condition' => $goodCondition,
                'damaged_condition' => $damagedCondition,
                'good_percent' => $goodPercent,
                'total_rooms' => $totalRooms,
            ],
        ]);
    }
}
