<?php

use App\Models\Building;
use App\Models\User;

beforeEach(function () {
    $this->seed();
});

it('requires authentication to access buildings page', function () {
    $response = $this->get('/data-referensi/buildings');
    $response->assertRedirect('/login');
});

it('displays the buildings page with pagination and preloaded Gedung 1 to Gedung 5', function () {
    $user = User::first();

    $response = $this->actingAs($user)->get('/data-referensi/buildings');
    $response->assertStatus(200);

    expect(Building::count())->toBeGreaterThanOrEqual(5);

    $building1 = Building::where('code', '001')->first();
    expect($building1)->not->toBeNull();
    expect($building1->name)->toBe('Gedung 1');

    $building5 = Building::where('code', '005')->first();
    expect($building5)->not->toBeNull();
    expect($building5->name)->toBe('Gedung 5');
});

it('creates a new building with formatted code and uuid', function () {
    $user = User::first();

    $response = $this->actingAs($user)->post('/data-referensi/buildings', [
        'code' => '006',
        'name' => 'Gedung 6',
        'total_floors' => 3,
        'description' => 'Gedung Inkubator Bisnis & Teaching Factory RPL/Animasi.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('buildings', [
        'code' => '006',
        'name' => 'Gedung 6',
    ]);

    $bld = Building::where('code', '006')->first();
    expect(str_contains($bld->id, '-'))->toBeTrue(); // UUID check
});

it('updates an existing building', function () {
    $user = User::first();
    $building = Building::where('code', '001')->first();

    $response = $this->actingAs($user)->put("/data-referensi/buildings/{$building->id}", [
        'code' => '001',
        'name' => 'Gedung 1 (Pusat)',
        'total_floors' => 4,
        'description' => 'Updated description.',
    ]);

    $response->assertRedirect();
    expect($building->fresh()->name)->toBe('Gedung 1 (Pusat)');
    expect($building->fresh()->total_floors)->toBe(4);
});

it('deletes a building', function () {
    $user = User::first();
    $building = Building::create([
        'code' => '999',
        'name' => 'Gedung Dummy',
        'total_floors' => 1,
        'description' => 'Untuk test hapus',
    ]);

    $response = $this->actingAs($user)->delete("/data-referensi/buildings/{$building->id}");
    $response->assertRedirect();
    $this->assertDatabaseMissing('buildings', ['id' => $building->id]);
});

it('triggers bulk seed endpoint for buildings successfully', function () {
    $user = User::first();

    $response = $this->actingAs($user)->post('/data-referensi/buildings/bulk-seed');
    $response->assertRedirect();
    expect(Building::count())->toBeGreaterThanOrEqual(5);
});
