<?php

use App\Models\InventoryPeriod;
use App\Models\User;

beforeEach(function () {
    $this->seed();
});

it('allows super admin to view inventory period and cutoff settings', function () {
    $admin = User::where('role', 'super_admin')->first();

    $response = $this->actingAs($admin)->get('/pengaturan-periode');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Governance/PeriodSetting')->has('periods'));
});

it('denies access to period settings for anggota role', function () {
    $anggota = User::where('role', 'anggota')->first();

    // Sign pact so it passes pact.signed middleware
    $this->actingAs($anggota)->post('/pakta-integritas', ['is_agreed' => true]);

    $response = $this->actingAs($anggota)->get('/pengaturan-periode');
    $response->assertStatus(403);
});

it('allows super admin to create and update an inventory period', function () {
    $admin = User::where('role', 'super_admin')->first();

    $response = $this->actingAs($admin)->post('/pengaturan-periode', [
        'name' => 'Pendataan Semester Genap 2026/2027',
        'start_date' => now()->toISOString(),
        'cutoff_date' => now()->addDays(20)->toISOString(),
        'is_active' => true,
        'notes' => 'Periode baru dengan batas cutoff 20 hari ke depan.',
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('inventory_periods', [
        'name' => 'Pendataan Semester Genap 2026/2027',
        'is_active' => true,
    ]);
});
