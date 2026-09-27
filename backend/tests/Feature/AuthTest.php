<?php

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;

uses(RefreshDatabase::class);

beforeEach(function () {
    $this->admin = User::factory()->create([
        'role' => 'admin',
        'password' => Hash::make('secret123'),
        'totp_enabled' => false,
    ]);

    $this->user = User::factory()->create([
        'role' => 'user',
        'password' => Hash::make('secret123'),
    ]);

    $this->revendedor = User::factory()->create([
        'role' => 'revendedor',
        'password' => Hash::make('secret123'),
    ]);
});

test('admin can login with valid credentials', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => $this->admin->email,
        'password' => 'secret123',
    ]);

    $response->assertOk()
        ->assertJsonStructure([
            'access_token',
            'token_type',
            'user' => [
                'id',
                'name',
                'email',
                'role',
                'totpEnabled'
            ]
        ]);
});

test('user can login with valid credentials', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => $this->user->email,
        'password' => 'secret123',
    ]);

    $response->assertOk()
        ->assertJsonStructure(['access_token', 'token_type', 'user']);
});

test('cannot login with invalid credentials', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => $this->user->email,
        'password' => 'wrongpassword',
    ]);

    $response->assertUnauthorized()
        ->assertJson(['message' => 'Usuário ou senha inválidos.']);
});

test('cannot login with non-existent user', function () {
    $response = $this->postJson('/api/auth/login', [
        'email' => 'nonexistent@example.com',
        'password' => 'secret123',
    ]);

    $response->assertUnauthorized();
});

test('authenticated user can get profile', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->getJson('/api/auth/me');

    $response->assertOk()
        ->assertJsonStructure([
            'user' => [
                'id',
                'name',
                'email',
                'role',
                'roleLabel',
                'eyebrow',
                'initials',
                'funcionalidades',
                'contaStatus',
                'contaStatusLabel',
                'contaStatusKind',
                'totpEnabled'
            ]
        ]);
});

test('cannot access me without token', function () {
    $response = $this->getJson('/api/auth/me');
    $response->assertUnauthorized();
});

test('user can logout', function () {
    $response = $this->actingAs($this->user, 'sanctum')
        ->postJson('/api/auth/logout');

    $response->assertOk()
        ->assertJson(['message' => 'Sessão encerrada.']);
});

test('totp secret generation for admin', function () {
    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/auth/totp-secret');

    $response->assertOk()
        ->assertJsonStructure([
            'secret',
            'qr_code'
        ]);

    expect($response['secret'])->toBeString();
    expect($response['qr_code'])->toContain('otpauth://totp/');
});

test('cannot generate totp secret when already enabled', function () {
    $this->admin->update([
        'totp_enabled' => true,
        'totp_secret' => 'TESTSECRET123',
    ]);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->getJson('/api/auth/totp-secret');

    $response->assertStatus(400)
        ->assertJsonStructure(['message', 'secret', 'qr_code']);
});

test('can enable totp with valid code', function () {
    $this->admin->update(['totp_secret' => 'JBSWY3DPEHPK3PXP']);
    
    $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
    $code = $google2fa->getCurrentOtp('JBSWY3DPEHPK3PXP');

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/auth/totp-enable', ['code' => $code]);

    $response->assertOk()
        ->assertJson(['success' => true, 'totpEnabled' => true, 'message' => '2FA habilitado com sucesso!']);

    $this->admin->refresh();
    expect($this->admin->totp_enabled)->toBeTrue();
});

test('cannot enable totp with invalid code', function () {
    $this->admin->update(['totp_secret' => 'JBSWY3DPEHPK3PXP']);

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/auth/totp-enable', ['code' => '000000']);

    $response->assertStatus(401)
        ->assertJson(['success' => false, 'message' => 'Código TOTP inválido.']);
});

test('can disable totp with valid code', function () {
    $this->admin->update([
        'totp_enabled' => true,
        'totp_secret' => 'JBSWY3DPEHPK3PXP',
    ]);

    $google2fa = app(\PragmaRX\Google2FA\Google2FA::class);
    $code = $google2fa->getCurrentOtp('JBSWY3DPEHPK3PXP');

    $response = $this->actingAs($this->admin, 'sanctum')
        ->postJson('/api/auth/totp-disable', ['code' => $code]);

    $response->assertOk()
        ->assertJson(['success' => true, 'totpEnabled' => false, 'message' => '2FA desabilitado com sucesso!']);

    $this->admin->refresh();
    expect($this->admin->totp_enabled)->toBeFalse();
    expect($this->admin->totp_secret)->toBeNull();
});