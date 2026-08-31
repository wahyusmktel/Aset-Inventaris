<?php

use App\Models\ItemFunction;
use App\Models\User;

beforeEach(function () {
    $this->seed();
});

it('requires authentication to access item functions page', function () {
    $response = $this->get('/data-referensi/item-functions');
    $response->assertRedirect('/login');
});

it('displays the item functions page with pagination and preloaded 14 standard functions in 2-digit format', function () {
    $user = User::first();

    $response = $this->actingAs($user)->get('/data-referensi/item-functions');
    $response->assertStatus(200);

    expect(ItemFunction::count())->toBeGreaterThanOrEqual(14);

    $func1 = ItemFunction::where('code', '01')->first();
    expect($func1)->not->toBeNull();
    expect($func1->name)->toBe('Peralatan Praktikum Siswa');
    expect($func1->code)->toBe('01');
});

it('creates a new item function with 2-digit formatted code and uuid', function () {
    $user = User::first();

    $response = $this->actingAs($user)->post('/data-referensi/item-functions', [
        'code' => '15',
        'name' => 'Fasilitas Smart Campus & IoT Terpusat',
        'description' => 'Sensor otomatisasi hemat energi dan smart campus.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('item_functions', [
        'code' => '15',
        'name' => 'Fasilitas Smart Campus & IoT Terpusat',
    ]);

    $func = ItemFunction::where('code', '15')->first();
    expect(str_contains($func->id, '-'))->toBeTrue(); // UUID check
});

it('updates an existing item function', function () {
    $user = User::first();
    $func = ItemFunction::where('code', '01')->first();

    $response = $this->actingAs($user)->put("/data-referensi/item-functions/{$func->id}", [
        'code' => '01',
        'name' => 'Peralatan Praktikum & Eksperimen Siswa',
        'description' => 'Updated description.',
    ]);

    $response->assertRedirect();
    expect($func->fresh()->name)->toBe('Peralatan Praktikum & Eksperimen Siswa');
});

it('deletes an item function', function () {
    $user = User::first();
    $func = ItemFunction::create([
        'code' => '99',
        'name' => 'Fungsi Dummy',
        'description' => 'Untuk test hapus',
    ]);

    $response = $this->actingAs($user)->delete("/data-referensi/item-functions/{$func->id}");
    $response->assertRedirect();
    $this->assertDatabaseMissing('item_functions', ['id' => $func->id]);
});

it('triggers bulk seed endpoint for item functions successfully', function () {
    $user = User::first();

    $response = $this->actingAs($user)->post('/data-referensi/item-functions/bulk-seed');
    $response->assertRedirect();
    expect(ItemFunction::count())->toBeGreaterThanOrEqual(14);
});
