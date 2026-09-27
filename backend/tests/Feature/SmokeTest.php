<?php
namespace Tests\Feature;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class SmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_health_check_returns_online(): void
    {
        $response = $this->getJson('/api/health');
        $response->assertStatus(200)->assertJson(['status' => 'online']);
    }

    public function test_admin_login_returns_token(): void
    {
        User::create([
            'name' => 'Smoke Admin',
            'email' => 'smoke@thevault.com',
            'password' => 'secret123',
            'role' => 'admin',
        ]);

        $response = $this->postJson('/api/auth/login', [
            'email' => 'smoke@thevault.com',
            'password' => 'secret123'
        ]);
        $response->assertStatus(200)->assertJsonStructure(['access_token', 'user']);
    }
}