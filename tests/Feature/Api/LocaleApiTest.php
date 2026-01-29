<?php

namespace Tests\Feature\Api;

use App\Models\Locale;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LocaleApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->token = $user->createToken('test')->plainTextToken;
    }

    public function test_index_returns_locales(): void
    {
        Locale::factory()->count(2)->create();
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/locales');
        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta'])
            ->assertJsonPath('meta.current_page', 1);
    }

    public function test_store_creates_locale(): void
    {
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/locales', ['code' => 'de', 'name' => 'German']);
        $response->assertStatus(201)
            ->assertJsonPath('data.code', 'de')
            ->assertJsonPath('data.name', 'German');
        $this->assertDatabaseHas('locales', ['code' => 'de']);
    }

    public function test_store_validation_returns_422(): void
    {
        Locale::factory()->create(['code' => 'en']);
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/locales', ['code' => 'en', 'name' => 'English']);
        $response->assertStatus(422);
    }

    public function test_show_returns_locale(): void
    {
        $locale = Locale::factory()->create();
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/locales/' . $locale->id);
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $locale->id);
    }

    public function test_update_modifies_locale(): void
    {
        $locale = Locale::factory()->create(['name' => 'Old']);
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson('/api/v1/locales/' . $locale->id, ['name' => 'New']);
        $response->assertStatus(200)
            ->assertJsonPath('data.name', 'New');
    }

    public function test_destroy_deletes_locale(): void
    {
        $locale = Locale::factory()->create();
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/locales/' . $locale->id);
        $response->assertStatus(204);
        $this->assertDatabaseMissing('locales', ['id' => $locale->id]);
    }
}
