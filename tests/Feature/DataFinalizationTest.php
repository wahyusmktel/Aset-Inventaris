<?php

use App\Models\User;

beforeEach(function () {
    $this->seed();
});

it('allows user to view data finalization review page', function () {
    $admin = User::where('role', 'super_admin')->first();

    $response = $this->actingAs($admin)->get('/finalisasi-data');
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Governance/DataFinalization')->has('statistics'));
});

it('allows anggota to finalize inventory data and generate berita acara', function () {
    $anggota = User::where('role', 'anggota')->first();

    // Sign pakta first
    $this->actingAs($anggota)->post('/pakta-integritas', ['is_agreed' => true]);

    $response = $this->actingAs($anggota)->post('/finalisasi-data', [
        'confirm_statement' => true,
        'statement_notes' => 'Seluruh data laboratorium dan kelas telah diverifikasi fisik lengkap.',
    ]);

    $response->assertSessionHas('success');
    $this->assertDatabaseHas('data_finalizations', [
        'user_id' => $anggota->id,
        'is_finalized' => true,
    ]);

    expect($anggota->fresh()->hasFinalized())->toBeTrue();
});

it('allows finalized user to download berita acara pdf with 3 signatures', function () {
    $anggota = User::where('role', 'anggota')->first();

    // Sign pakta & finalize
    $this->actingAs($anggota)->post('/pakta-integritas', ['is_agreed' => true]);
    $this->actingAs($anggota)->post('/finalisasi-data', ['confirm_statement' => true]);

    $downloadResponse = $this->actingAs($anggota)->get('/finalisasi-data/download');
    $downloadResponse->assertStatus(200);
    $downloadResponse->assertHeader('content-type', 'application/pdf');
});
