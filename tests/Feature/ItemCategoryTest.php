<?php

use App\Models\ItemCategory;
use App\Models\User;

beforeEach(function () {
    $this->seed();
});

it('requires authentication to access item categories page', function () {
    $response = $this->get('/data-referensi/item-categories');
    $response->assertRedirect('/login');
});

it('displays the item categories page with pagination and concise categories', function () {
    $user = User::first();

    $response = $this->actingAs($user)->get('/data-referensi/item-categories');
    $response->assertStatus(200);

    expect(ItemCategory::count())->toBeGreaterThanOrEqual(30);
    $pc = ItemCategory::where('code', '001')->first();
    expect($pc)->not->toBeNull();
    expect($pc->name)->toBe('PC / Komputer');

    $furniture = ItemCategory::where('code', '021')->first();
    expect($furniture)->not->toBeNull();
    expect($furniture->name)->toBe('Meja & Kursi');
});

it('creates a new item category with formatted code and uuid', function () {
    $user = User::first();

    $response = $this->actingAs($user)->post('/data-referensi/item-categories', [
        'code' => '034',
        'name' => 'Alat Musik Tradisional',
        'description' => 'Gamelan dan angklung inventaris sanggar seni.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('item_categories', [
        'code' => '034',
        'name' => 'Alat Musik Tradisional',
    ]);

    $cat = ItemCategory::where('code', '034')->first();
    expect(str_contains($cat->id, '-'))->toBeTrue(); // UUID check
});

it('updates an existing item category', function () {
    $user = User::first();
    $category = ItemCategory::where('code', '001')->first();

    $response = $this->actingAs($user)->put("/data-referensi/item-categories/{$category->id}", [
        'code' => '001',
        'name' => 'PC & Komputer Desktop',
        'description' => 'Updated description.',
    ]);

    $response->assertRedirect();
    expect($category->fresh()->name)->toBe('PC & Komputer Desktop');
});

it('deletes an item category', function () {
    $user = User::first();
    $category = ItemCategory::create([
        'code' => '999',
        'name' => 'Kategori Dummy',
        'description' => 'Untuk test hapus',
    ]);

    $response = $this->actingAs($user)->delete("/data-referensi/item-categories/{$category->id}");
    $response->assertRedirect();
    $this->assertDatabaseMissing('item_categories', ['id' => $category->id]);
});

it('triggers bulk seed endpoint successfully', function () {
    $user = User::first();

    $response = $this->actingAs($user)->post('/data-referensi/item-categories/bulk-seed');
    $response->assertRedirect();
    expect(ItemCategory::count())->toBeGreaterThanOrEqual(30);
});
