<?php

use App\Models\DataFinalization;
use App\Models\IntegrityPact;
use App\Models\InventoryItem;
use App\Models\User;

beforeEach(function () {
    $this->seed();
});

it('allows super admin to reset inventory items and finalizations', function () {
    $admin = User::where('role', 'super_admin')->first();

    // Ensure we have items
    expect(InventoryItem::count())->toBeGreaterThan(0);

    $response = $this->actingAs($admin)->post('/pengaturan-sistem/reset-inventory');
    $response->assertSessionHas('success');

    // All items should be permanently deleted
    expect(InventoryItem::withTrashed()->count())->toBe(0);
    expect(DataFinalization::count())->toBe(0);
});

it('allows super admin to perform full transactional reset', function () {
    $admin = User::where('role', 'super_admin')->first();
    $anggota = User::where('role', 'anggota')->first();

    // Anggota signs pact
    $this->actingAs($anggota)->post('/pakta-integritas', ['is_agreed' => true]);
    expect(IntegrityPact::count())->toBeGreaterThan(0);

    $response = $this->actingAs($admin)->post('/pengaturan-sistem/reset-all-transactional');
    $response->assertSessionHas('success');

    // All items, pacts, and finalizations cleared
    expect(InventoryItem::withTrashed()->count())->toBe(0);
    expect(DataFinalization::count())->toBe(0);
    expect(IntegrityPact::count())->toBe(0);

    // Users and school master data must still exist
    expect(User::count())->toBeGreaterThan(0);
});

it('prevents anggota from executing system reset operations', function () {
    $anggota = User::where('role', 'anggota')->first();
    // Sign pact
    $this->actingAs($anggota)->post('/pakta-integritas', ['is_agreed' => true]);

    $res1 = $this->actingAs($anggota)->post('/pengaturan-sistem/reset-inventory');
    $res1->assertStatus(403);

    $res2 = $this->actingAs($anggota)->post('/pengaturan-sistem/reset-all-transactional');
    $res2->assertStatus(403);
});
