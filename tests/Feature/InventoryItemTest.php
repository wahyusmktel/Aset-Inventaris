<?php

use App\Models\Building;
use App\Models\InventoryItem;
use App\Models\ItemCategory;
use App\Models\ItemFunction;
use App\Models\Room;
use App\Models\User;

beforeEach(function () {
    $this->seed();
});

it('requires authentication to access inventory items page', function () {
    $response = $this->get('/inventaris/items');
    $response->assertRedirect('/login');
});

it('displays the inventory items page with pagination and preloaded items', function () {
    $user = User::where('role', 'super_admin')->first();

    $response = $this->actingAs($user)->get('/inventaris/items');
    $response->assertStatus(200);

    expect(InventoryItem::count())->toBeGreaterThanOrEqual(8);

    $pc = InventoryItem::where('name', 'like', '%Asus ROG%')->first();
    expect($pc)->not->toBeNull();
    expect($pc->condition)->toBe('Baik');
});

it('creates a new inventory item with creator audit trail and uuid', function () {
    $user = User::where('role', 'super_admin')->first();
    $category = ItemCategory::first();
    $building = Building::first();
    $room = Room::first();
    $func = ItemFunction::first();

    $response = $this->actingAs($user)->post('/inventaris/items', [
        'name' => 'Switch D-Link DGS-1210-28 28-Port Smart Managed',
        'serial_number' => 'DL-DGS-881920',
        'has_no_serial_number' => false,
        'brand' => 'D-Link',
        'quantity' => 2,
        'condition' => 'Baik',
        'category_id' => $category->id,
        'building_id' => $building->id,
        'room_id' => $room->id,
        'function_id' => $func->id,
        'notes' => 'Pengadaan lab baru.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('inventory_items', [
        'name' => 'Switch D-Link DGS-1210-28 28-Port Smart Managed',
        'brand' => 'D-Link',
        'condition' => 'Baik',
        'created_by' => $user->id,
    ]);

    $item = InventoryItem::where('name', 'Switch D-Link DGS-1210-28 28-Port Smart Managed')->first();
    expect(str_contains($item->id, '-'))->toBeTrue();
    expect($item->created_by)->toBe($user->id);
    expect($item->created_at)->not->toBeNull();
});

it('creates an item with has_no_serial_number checked', function () {
    $user = User::where('role', 'super_admin')->first();

    $response = $this->actingAs($user)->post('/inventaris/items', [
        'name' => 'Kabel UTP Cat6 Belden 1 Roll 305 Meter',
        'serial_number' => null,
        'has_no_serial_number' => true,
        'brand' => 'Belden',
        'quantity' => 5,
        'condition' => 'Baik',
    ]);

    $response->assertRedirect();
    $item = InventoryItem::where('name', 'Kabel UTP Cat6 Belden 1 Roll 305 Meter')->first();
    expect($item)->not->toBeNull();
    expect($item->has_no_serial_number)->toBeTrue();
    expect($item->serial_number)->toBeNull();
});

it('updates an inventory item and records updated_by audit trail', function () {
    $user = User::where('role', 'super_admin')->first();
    $item = InventoryItem::first();

    $response = $this->actingAs($user)->put("/inventaris/items/{$item->id}", [
        'name' => 'PC Desktop Asus ROG Updated',
        'brand' => 'ASUS',
        'quantity' => 2,
        'condition' => 'Rusak',
        'has_no_serial_number' => false,
        'serial_number' => 'ROG-SN-999',
    ]);

    $response->assertRedirect();
    $updated = $item->fresh();
    expect($updated->name)->toBe('PC Desktop Asus ROG Updated');
    expect($updated->condition)->toBe('Rusak');
    expect($updated->updated_by)->toBe($user->id);
});

it('allows anggota to update and delete items they created themselves', function () {
    $anggota = User::where('role', 'anggota')->first();
    // Sign pact
    $this->actingAs($anggota)->post('/pakta-integritas', ['is_agreed' => true]);

    // Create an item as anggota
    $this->actingAs($anggota)->post('/inventaris/items', [
        'name' => 'Kamera Sony Alpha A7 IV',
        'brand' => 'Sony',
        'quantity' => 1,
        'condition' => 'Baik',
        'has_no_serial_number' => false,
        'serial_number' => 'SONY-A7-8819',
    ]);

    $item = InventoryItem::where('name', 'Kamera Sony Alpha A7 IV')->first();
    expect($item->created_by)->toBe($anggota->id);

    // Update item
    $response = $this->actingAs($anggota)->put("/inventaris/items/{$item->id}", [
        'name' => 'Kamera Sony Alpha A7 IV Kit 28-70mm',
        'brand' => 'Sony',
        'quantity' => 1,
        'condition' => 'Baik',
        'has_no_serial_number' => false,
        'serial_number' => 'SONY-A7-8819',
    ]);

    $response->assertSessionHas('success');
    expect($item->fresh()->name)->toBe('Kamera Sony Alpha A7 IV Kit 28-70mm');

    // Delete item
    $delResponse = $this->actingAs($anggota)->delete("/inventaris/items/{$item->id}");
    $delResponse->assertSessionHas('success');
    expect($item->fresh()->trashed())->toBeTrue();
});

it('prevents anggota from updating or deleting items created by other users', function () {
    $admin = User::where('role', 'super_admin')->first();
    $anggota = User::where('role', 'anggota')->first();
    // Sign pact
    $this->actingAs($anggota)->post('/pakta-integritas', ['is_agreed' => true]);

    // Item created by admin
    $adminItem = InventoryItem::where('created_by', $admin->id)->first();
    expect($adminItem)->not->toBeNull();

    // Anggota tries to update admin's item
    $response = $this->actingAs($anggota)->put("/inventaris/items/{$adminItem->id}", [
        'name' => 'Hacked Item Name',
        'quantity' => 1,
        'condition' => 'Baik',
    ]);

    $response->assertSessionHas('error');
    expect($adminItem->fresh()->name)->not->toBe('Hacked Item Name');

    // Anggota tries to delete admin's item
    $delResponse = $this->actingAs($anggota)->delete("/inventaris/items/{$adminItem->id}");
    $delResponse->assertSessionHas('error');
    expect($adminItem->fresh()->trashed())->toBeFalse();
});

it('soft deletes an inventory item and keeps audit trail', function () {
    $user = User::where('role', 'super_admin')->first();
    $item = InventoryItem::first();

    $response = $this->actingAs($user)->delete("/inventaris/items/{$item->id}");
    $response->assertRedirect();

    expect(InventoryItem::find($item->id))->toBeNull();
    expect(InventoryItem::withTrashed()->find($item->id))->not->toBeNull();
    expect(InventoryItem::withTrashed()->find($item->id)->trashed())->toBeTrue();
});

it('exports inventory items to excel (.xlsx)', function () {
    $user = User::where('role', 'super_admin')->first();

    $response = $this->actingAs($user)->get('/inventaris/items/export');
    $response->assertStatus(200);
    $response->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
});
