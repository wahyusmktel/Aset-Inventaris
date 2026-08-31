<?php

use App\Models\IntegrityPact;
use App\Models\User;

beforeEach(function () {
    $this->seed();
});

it('redirects unsigned anggota to pakta integritas on accessing inventory', function () {
    $anggota = User::where('role', 'anggota')->first();

    $response = $this->actingAs($anggota)->get('/inventaris/items');
    $response->assertRedirect('/pakta-integritas');
});

it('allows anggota to view pakta integritas page', function () {
    $anggota = User::where('role', 'anggota')->first();

    $response = $this->actingAs($anggota)->get('/pakta-integritas');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Governance/IntegrityPact'));
});

it('allows anggota to digitally sign pakta integritas and generate record', function () {
    $anggota = User::where('role', 'anggota')->first();

    $response = $this->actingAs($anggota)->post('/pakta-integritas', [
        'is_agreed' => true,
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('integrity_pacts', [
        'user_id' => $anggota->id,
        'is_agreed' => true,
    ]);

    expect($anggota->fresh()->hasSignedPact())->toBeTrue();

    // Now anggota can access inventory items
    $invResponse = $this->actingAs($anggota)->get('/inventaris/items');
    $invResponse->assertStatus(200);
});

it('allows signed anggota to download official pakta integritas pdf', function () {
    $anggota = User::where('role', 'anggota')->first();

    // Sign first
    $this->actingAs($anggota)->post('/pakta-integritas', [
        'is_agreed' => true,
    ]);

    $downloadResponse = $this->actingAs($anggota)->get('/pakta-integritas/download');
    $downloadResponse->assertStatus(200);
    $downloadResponse->assertHeader('content-type', 'application/pdf');
});
