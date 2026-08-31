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

it('authenticates user via API and returns JWT token and governance info', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'admin@admin.com',
        'password' => '12345678',
    ]);

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'success',
                 'access_token',
                 'token_type',
                 'user' => ['id', 'name', 'email', 'role'],
                 'governance' => ['has_signed_pact', 'has_finalized'],
             ]);
});

it('returns master data lookups for mobile dropdowns', function () {
    $user = User::where('role', 'super_admin')->first();
    $token = auth('api')->login($user);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                     ->getJson('/api/master-data/all');

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'success',
                 'data' => ['categories', 'buildings', 'rooms', 'functions', 'school'],
             ]);
});

it('creates an inventory item via API with creator audit trail', function () {
    $user = User::where('role', 'super_admin')->first();
    $token = auth('api')->login($user);
    $category = ItemCategory::first();
    $room = Room::first();

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                     ->postJson('/api/inventory/items', [
                         'name' => 'Tablet Samsung Galaxy Tab S9 FE+',
                         'serial_number' => 'SS-TAB-998811',
                         'has_no_serial_number' => false,
                         'brand' => 'Samsung',
                         'quantity' => 5,
                         'condition' => 'Baik',
                         'category_id' => $category->id,
                         'room_id' => $room->id,
                     ]);

    $response->assertStatus(201)
             ->assertJson([
                 'success' => true,
                 'data' => [
                     'name' => 'Tablet Samsung Galaxy Tab S9 FE+',
                     'brand' => 'Samsung',
                     'created_by' => $user->id,
                 ],
             ]);
});

it('returns mobile dashboard stats via API', function () {
    $user = User::where('role', 'super_admin')->first();
    $token = auth('api')->login($user);

    $response = $this->withHeader('Authorization', 'Bearer ' . $token)
                     ->getJson('/api/inventory/stats');

    $response->assertStatus(200)
             ->assertJsonStructure([
                 'success',
                 'data' => ['total_items', 'total_quantity', 'good_condition', 'damaged_condition', 'total_rooms'],
             ]);
});
