<?php

use App\Models\InventoryItem;
use App\Models\School;
use App\Models\User;

beforeEach(function () {
    $this->seed();
});

it('requires authentication to access dashboard', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

it('displays the dashboard with live metrics, chart datasets, and active school', function () {
    $user = User::first();

    $response = $this->actingAs($user)->get('/dashboard');
    $response->assertStatus(200);

    $response->assertInertia(fn ($page) => $page
        ->component('Dashboard')
        ->has('metrics')
        ->has('chartData')
        ->has('recentItems')
        ->where('metrics.total_items', fn ($val) => $val >= 8)
    );
});
