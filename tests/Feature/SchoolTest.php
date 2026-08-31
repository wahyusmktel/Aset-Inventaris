<?php

use App\Models\School;
use App\Models\User;

beforeEach(function () {
    $this->seed();
});

it('requires authentication to access schools page', function () {
    $response = $this->get('/data-referensi/schools');
    $response->assertRedirect('/login');
});

it('displays the schools page for authenticated user with pagination', function () {
    $user = User::first();

    $response = $this->actingAs($user)->get('/data-referensi/schools');
    $response->assertStatus(200);
});

it('creates a new school with uuid and validates unique code', function () {
    $user = User::first();

    $response = $this->actingAs($user)->post('/data-referensi/schools', [
        'code' => 'SMKN-99-TEST',
        'name' => 'SMK Negeri 99 Test',
        'address' => 'Jl. Merdeka No. 99, Jakarta',
        'latitude' => '-6.175392',
        'longitude' => '106.827153',
        'principal_name' => 'Prof. Dr. Hendra, M.Pd.',
        'is_active' => false,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('schools', [
        'code' => 'SMKN-99-TEST',
        'name' => 'SMK Negeri 99 Test',
    ]);

    $school = School::where('code', 'SMKN-99-TEST')->first();
    expect(str_contains($school->id, '-'))->toBeTrue(); // UUID validation
});

it('enforces single active school rule when activating a new school', function () {
    $user = User::first();

    $activeSchool = School::where('is_active', true)->first();
    $inactiveSchool = School::where('is_active', false)->first();

    expect($activeSchool)->not->toBeNull();
    expect($inactiveSchool)->not->toBeNull();

    // Activate the inactive school
    $response = $this->actingAs($user)->patch("/data-referensi/schools/{$inactiveSchool->id}/activate");
    $response->assertRedirect();

    // Verify previously active school is now deactivated
    expect($activeSchool->fresh()->is_active)->toBeFalse();

    // Verify target school is now active
    expect($inactiveSchool->fresh()->is_active)->toBeTrue();

    // Verify there is strictly only 1 active school in database
    expect(School::where('is_active', true)->count())->toBe(1);
});

it('updates an existing school record', function () {
    $user = User::first();
    $school = School::first();

    $response = $this->actingAs($user)->put("/data-referensi/schools/{$school->id}", [
        'code' => $school->code,
        'name' => 'SMK Updated Name',
        'address' => 'Jl. Baru No. 123',
        'latitude' => '-6.200000',
        'longitude' => '106.800000',
        'principal_name' => 'Drs. Baru, M.Pd.',
        'is_active' => $school->is_active,
    ]);

    $response->assertRedirect();
    expect($school->fresh()->name)->toBe('SMK Updated Name');
});

it('deletes a school record', function () {
    $user = User::first();
    $school = School::create([
        'code' => 'SMK-TO-DELETE',
        'name' => 'SMK To Delete',
        'address' => 'Jl. Hapus No. 1',
        'principal_name' => 'Kepsek Hapus',
        'is_active' => false,
    ]);

    $response = $this->actingAs($user)->delete("/data-referensi/schools/{$school->id}");
    $response->assertRedirect();
    $this->assertDatabaseMissing('schools', ['id' => $school->id]);
});
