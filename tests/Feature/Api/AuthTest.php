<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_returns_token(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@example.com',
            'password' => Hash::make('password'),
        ]);

        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'admin@example.com',
            'password' => 'password',
        ]);

        $response->assertStatus(200)
            ->assertJsonStructure(['token', 'token_type', 'user'])
            ->assertJson(['token_type' => 'Bearer']);
    }

    public function test_login_invalid_credentials_returns_401(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => 'wrong@example.com',
            'password' => 'wrong',
        ]);

        $response->assertStatus(401);
    }

    public function test_login_validation_returns_422(): void
    {
        $response = $this->postJson('/api/v1/auth/login', [
            'email' => '',
            'password' => 'password',
        ]);

        $response->assertStatus(422);
    }

    public function test_protected_route_returns_401_without_token(): void
    {
        $response = $this->getJson('/api/v1/locales');
        $response->assertStatus(401);
    }

    public function test_logout_returns_204(): void
    {
        $user = User::factory()->create();
        $token = $user->createToken('test')->plainTextToken;

        $response = $this->withHeader('Authorization', 'Bearer ' . $token)
            ->postJson('/api/v1/auth/logout');

        $response->assertStatus(204);
    }
}
