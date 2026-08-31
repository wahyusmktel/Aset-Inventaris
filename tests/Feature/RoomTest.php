<?php

use App\Models\Building;
use App\Models\Room;
use App\Models\User;

beforeEach(function () {
    $this->seed();
});

it('requires authentication to access rooms page', function () {
    $response = $this->get('/data-referensi/rooms');
    $response->assertRedirect('/login');
});

it('displays the rooms page with pagination and preloaded 27 school rooms', function () {
    $user = User::first();

    $response = $this->actingAs($user)->get('/data-referensi/rooms');
    $response->assertStatus(200);

    expect(Room::count())->toBeGreaterThanOrEqual(25);

    $room1 = Room::where('code', '0001')->first();
    expect($room1)->not->toBeNull();
    expect($room1->name)->toContain('Ruang Kelas');

    $labRPL = Room::where('code', '0005')->first();
    expect($labRPL)->not->toBeNull();
    expect($labRPL->name)->toBe('Lab Software (RPL)');

    $labFiber = Room::where('code', '0006')->first();
    expect($labFiber)->not->toBeNull();
    expect($labFiber->name)->toBe('Lab Fiber Optic (TJAT)');
});

it('creates a new room with 4-digit code and uuid', function () {
    $user = User::first();
    $building = Building::first();

    $response = $this->actingAs($user)->post('/data-referensi/rooms', [
        'code' => '0028',
        'name' => 'Lab Cyber Security & Ethical Hacking',
        'building_id' => $building->id,
        'floor' => 3,
        'capacity' => 30,
        'type' => 'Laboratorium Komputer',
        'description' => 'Lab pengujian penetrasi dan keamanan jaringan.',
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('rooms', [
        'code' => '0028',
        'name' => 'Lab Cyber Security & Ethical Hacking',
    ]);

    $room = Room::where('code', '0028')->first();
    expect(str_contains($room->id, '-'))->toBeTrue(); // UUID check
    expect($room->building_id)->toBe($building->id);
});

it('updates an existing room', function () {
    $user = User::first();
    $room = Room::where('code', '0005')->first();

    $response = $this->actingAs($user)->put("/data-referensi/rooms/{$room->id}", [
        'code' => '0005',
        'name' => 'Lab Software Engineering & AI',
        'building_id' => $room->building_id,
        'floor' => 2,
        'capacity' => 40,
        'type' => 'Laboratorium Komputer',
        'description' => 'Updated description.',
    ]);

    $response->assertRedirect();
    expect($room->fresh()->name)->toBe('Lab Software Engineering & AI');
    expect($room->fresh()->capacity)->toBe(40);
});

it('deletes a room', function () {
    $user = User::first();
    $room = Room::create([
        'code' => '9999',
        'name' => 'Ruang Dummy',
        'floor' => 1,
        'capacity' => 10,
        'description' => 'Untuk test hapus',
    ]);

    $response = $this->actingAs($user)->delete("/data-referensi/rooms/{$room->id}");
    $response->assertRedirect();
    $this->assertDatabaseMissing('rooms', ['id' => $room->id]);
});

it('triggers bulk seed endpoint for rooms successfully', function () {
    $user = User::first();

    $response = $this->actingAs($user)->post('/data-referensi/rooms/bulk-seed');
    $response->assertRedirect();
    expect(Room::count())->toBeGreaterThanOrEqual(25);
});
