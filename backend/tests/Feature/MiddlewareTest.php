<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
        'totp_enabled' => true,
        'totp_secret' => 'JBSWY3DPEHPK3PXP',
    ]);

    $this->revendedor = User::factory()->create([
        'role' => 'revendedor',
        'totp_enabled' => true,
        'totp_secret' => 'JBSWY3DPEHPK3PXP',
    ]);

    $this->user = User::factory()->create([
        'role' => 'user',
        'totp_enabled' => false,
    ]);
});

test('admin can access admin routes with valid totp', function () {
    $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
    $code = $google2fa->getCurrentOtp('JBSWY3DPEHPK3PXP');

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->admin->createToken('test')->plainTextToken,
        'X-TOTP-Code' => $code,
    ])->getJson('/api/admin/logs');

    $response->assertOk()
        ->assertJsonStructure([
            'current_page',
            'data',
            'first_page_url',
            'from',
            'last_page',
            'last_page_url',
            'links',
            'next_page_url',
            'path',
            'per_page',
            'prev_page_url',
            'to',
            'total'
        ]);
});

test('admin cannot access admin routes without totp', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->admin->createToken('test')->plainTextToken,
    ])->getJson('/api/admin/logs');

    $response->assertStatus(401)
        ->assertJson([
            'error' => 'Autenticação de dois fatores necessária',
            'action' => 'verify_totp',
            'attempts_remaining' => 3,
        ]);
});

test('admin cannot access admin routes with invalid totp', function () {
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->admin->createToken('test')->plainTextToken,
        'X-TOTP-Code' => '000000',
    ])->getJson('/api/admin/logs');

    $response->assertStatus(401)
        ->assertJson([
            'error' => 'Código TOTP inválido.',
            'message' => 'Autenticação de dois fatores necessária.',
            'action' => 'verify_totp',
            'attempts_remaining' => 3,
        ]);
});

test('revendedor can access admin routes with valid totp', function () {
    $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
    $code = $google2fa->getCurrentOtp('JBSWY3DPEHPK3PXP');

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->revendedor->createToken('test')->plainTextToken,
        'X-TOTP-Code' => $code,
    ])->getJson('/api/admin/logs');

    // Revendedor tem role revendedor, mas não tem acesso admin (role.admin middleware)
    $response->assertStatus(403);
});

test('user cannot access admin routes even with totp', function () {
    $this->user->update([
        'totp_enabled' => true,
        'totp_secret' => 'JBSWY3DPEHPK3PXP',
    ]);

    $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
    $code = $google2fa->getCurrentOtp('JBSWY3DPEHPK3PXP');

    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->user->createToken('test')->plainTextToken,
        'X-TOTP-Code' => $code,
    ])->getJson('/api/admin/logs');

    // User não tem role admin
    $response->assertStatus(403);
});

test('user without totp can access non-admin routes', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/consultas');

    $response->assertOk()
        ->assertJson([]);
});

test('revendedor without totp can access non-admin routes', function () {
    $this->revendedor->update(['totp_enabled' => false]);

    $response = $this->actingAs($this->revendedor, 'sanctum')
        ->getJson('/api/consultas');

    $response->assertOk();
});

test('totp not required for non-admin routes', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/consultas');

    // Admin acessando rota não-admin - não precisa TOTP
    $response->assertOk();
});

test('totp routes are accessible without totp', function () {
    $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
    $code = $google2fa->getCurrentOtp('JBSWY3DPEHPK3PXP');

    // totp-secret não precisa de TOTP
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->admin->createToken('test')->plainTextToken,
    ])->getJson('/api/auth/totp-secret');

    $response->assertOk();

    // verify-totp não precisa de TOTP (é a rota que verifica o código)
    $response = $this->withHeaders([
        'Authorization' => 'Bearer ' . $this->admin->createToken('test')->plainTextToken,
    ])->postJson('/api/auth/verify-totp', ['code' => '123456']);

    // Código inválido, mas rota acessível
    $response->assertStatus(401);
});