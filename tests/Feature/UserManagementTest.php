<?php

use App\Models\User;

beforeEach(function () {
    $this->seed();
});

it('allows super admin to view user management page', function () {
    $admin = User::where('role', 'super_admin')->first();

    $response = $this->actingAs($admin)->get('/manajemen-pengguna');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('UserManagement/Index')->has('users'));
});

it('denies access to user management for anggota role', function () {
    $anggota = User::where('role', 'anggota')->first();

    // Sign pact so it passes pact.signed middleware
    $this->actingAs($anggota)->post('/pakta-integritas', ['is_agreed' => true]);

    $response = $this->actingAs($anggota)->get('/manajemen-pengguna');
    $response->assertStatus(403);
});

it('allows super admin to create a new user', function () {
    $admin = User::where('role', 'super_admin')->first();

    $response = $this->actingAs($admin)->post('/manajemen-pengguna/users', [
        'name' => 'Budi Santoso',
        'email' => 'budi.santoso@smktelkom.sch.id',
        'password' => 'password123',
        'role' => 'anggota',
        'nip' => '19980101 202203 1 009',
        'phone' => '081234567899',
        'is_active' => true,
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('users', [
        'email' => 'budi.santoso@smktelkom.sch.id',
        'role' => 'anggota',
    ]);
});

it('allows super admin to assign and switch user role', function () {
    $admin = User::where('role', 'super_admin')->first();
    $anggota = User::where('role', 'anggota')->first();

    $response = $this->actingAs($admin)->patch("/manajemen-pengguna/users/{$anggota->id}/assign-role", [
        'role' => 'super_admin',
    ]);

    $response->assertSessionHas('success');
    expect($anggota->fresh()->role)->toBe('super_admin');
});
