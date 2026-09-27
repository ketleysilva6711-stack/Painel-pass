<?php

use App\Models\User;
use App\Models\PlayFakeApp;
use Illuminate\Foundation\Testing\RefreshDatabase;

uses(RefreshDatabase::class);

test('user can create consulta', function () {
    $user = User::factory()->create();

    $response = $this->actingAs($user, 'sanctum')
        ->postJson('/api/consultas', [
            'documento' => '123.456.789-00',
        ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'id',
            'user_id',
            'tipo',
            'documento',
            'resultado',
            'ip',
            'created_at',
            'updated_at'
        ]);

    expect($response['tipo'])->toBe('CPF/CNPJ');
    expect($response['documento'])->toBe('123.456.789-00');
    expect($response['resultado']['status'])->toBe('REGULAR');
});

test('user can list own consultas', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    // Criar consultas para ambos os usuários
    \App\Models\Consulta::factory()->count(3)->create(['user_id' => $user->id]);
    \App\Models\Consulta::factory()->count(2)->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/consultas');

    $response->assertOk()
        ->assertJsonCount(3);
});

test('user cannot see other users consultas', function () {
    $user = User::factory()->create();
    $otherUser = User::factory()->create();

    \App\Models\Consulta::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/consultas');

    $response->assertOk()
        ->assertJsonCount(0);
});

test('ranking podio returns top users by points', function () {
    $user1 = User::factory()->create();
    $user2 = User::factory()->create();
    $user3 = User::factory()->create();

    \App\Models\Ranking::factory()->create(['user_id' => $user1->id, 'points' => 100]);
    \App\Models\Ranking::factory()->create(['user_id' => $user2->id, 'points' => 500]);
    \App\Models\Ranking::factory()->create(['user_id' => $user3->id, 'points' => 200]);

    $response = $this->actingAs($user1, 'sanctum')
        ->getJson('/api/ranking/podio');

    $response->assertOk()
        ->assertJsonCount(3);

    // Deve vir ordenado por points desc
    $data = $response->json();
    expect($data[0]['points'])->toBe(500); // user2
    expect($data[1]['points'])->toBe(200); // user3
    expect($data[2]['points'])->toBe(100); // user1
});

test('ranking includes user relationship', function () {
    $user = User::factory()->create(['name' => 'Test User']);
    \App\Models\Ranking::factory()->create(['user_id' => $user->id, 'points' => 1000]);

    $response = $this->actingAs($user, 'sanctum')
        ->getJson('/api/ranking/podio');

    $response->assertOk();
    $data = $response->json();
    expect($data[0]['user']['name'])->toBe('Test User');
});

test('bypass rule creation dispatches job', function () {
    $admin = \App\Models\User::factory()->create(['role' => 'admin']);
    $apk = PlayFakeApp::factory()->create(['user_id' => $admin->id]);

    $response = $this->actingAs($admin, 'sanctum')
        ->postJson('/api/bypass-rules', [
            'target_package' => 'com.example.app',
            'apk_id' => $apk->id,
        ]);

    $response->assertCreated()
        ->assertJsonStructure([
            'message',
            'item' => [
                'id',
                'status',
                'progress',
                'phase',
                'apk_id',
                'apk_path'
            ]
        ]);

    expect($response['item']['status'])->toBe('pending');
    expect($response['item']['phase'])->toBe('decompile');
});

test('bypass job status endpoint', function () {
    $admin = \App\Models\User::factory()->create(['role' => 'admin']);
    $job = \App\Models\BypassRule::factory()->create([
        'user_id' => $admin->id,
        'status' => 'completed',
        'progress' => 100,
        'phase' => 'completed',
        'output_path' => '/fake/path.apk',
    ]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson("/api/bypass/jobs/{$job->id}");

    $response->assertOk()
        ->assertJson([
            'id' => $job->id,
            'status' => 'completed',
            'progress' => 100,
            'phase' => 'completed',
        ]);
});

test('cannot access bypass job of another user', function () {
    $admin = \App\Models\User::factory()->create(['role' => 'admin']);
    $otherUser = \App\Models\User::factory()->create(['role' => 'admin']);
    $job = \App\Models\BypassRule::factory()->create(['user_id' => $otherUser->id]);

    $response = $this->actingAs($admin, 'sanctum')
        ->getJson("/api/bypass/jobs/{$job->id}");

    $response->assertStatus(403);
});