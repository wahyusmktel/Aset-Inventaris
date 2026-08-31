<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;

beforeEach(function () {
    $this->seed();
});

it('redirects guest from root to login', function () {
    $response = $this->get('/');
    $response->assertRedirect('/login');
});

it('prevents unauthenticated user from accessing dashboard', function () {
    $response = $this->get('/dashboard');
    $response->assertRedirect('/login');
});

it('prevents authenticated user from accessing login page', function () {
    $user = User::first();

    $response = $this->actingAs($user)->get('/login');
    $response->assertRedirect('/dashboard');
});

it('authenticates admin with valid credentials and sets jwt token', function () {
    $response = $this->post('/login', [
        'email' => 'admin@admin.com',
        'password' => '12345678',
    ]);

    $response->assertRedirect('/dashboard');
    $this->assertAuthenticated();
    $this->assertNotEmpty(session('jwt_token'));
});

it('authenticates via jwt api endpoint and returns uuid and access token', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'admin@admin.com',
        'password' => '12345678',
    ]);

    $response->assertStatus(200)
        ->assertJsonStructure([
            'access_token',
            'token_type',
            'expires_in',
            'user' => ['id', 'name', 'email'],
        ]);

    $data = $response->json();
    expect($data['user']['email'])->toBe('admin@admin.com');
    expect(str_contains($data['user']['id'], '-'))->toBeTrue();
});
