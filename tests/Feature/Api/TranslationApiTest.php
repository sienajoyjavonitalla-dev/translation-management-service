<?php

namespace Tests\Feature\Api;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\TranslationKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationApiTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->token = $user->createToken('test')->plainTextToken;
    }

    public function test_index_returns_translations(): void
    {
        Translation::factory()->count(2)->create();
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/translations');
        $response->assertStatus(200)
            ->assertJsonStructure(['data', 'meta']);
    }

    public function test_index_filters_by_locale(): void
    {
        $locale = Locale::factory()->create(['code' => 'en']);
        $key = TranslationKey::factory()->create();
        Translation::factory()->create(['translation_key_id' => $key->id, 'locale_id' => $locale->id]);
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/translations?locale=en');
        $response->assertStatus(200);
        $this->assertGreaterThanOrEqual(1, count($response->json('data')));
    }

    public function test_store_creates_translation(): void
    {
        $locale = Locale::factory()->create();
        $tag = Tag::factory()->create();
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->postJson('/api/v1/translations', [
                'key' => 'auth.login',
                'locale_id' => $locale->id,
                'value' => 'Login',
                'tag_ids' => [$tag->id],
            ]);
        $response->assertStatus(201)
            ->assertJsonPath('data.value', 'Login');
        $this->assertDatabaseHas('translation_keys', ['key' => 'auth.login']);
    }

    public function test_show_returns_translation(): void
    {
        $translation = Translation::factory()->create();
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/translations/' . $translation->id);
        $response->assertStatus(200)
            ->assertJsonPath('data.id', $translation->id);
    }

    public function test_update_modifies_translation(): void
    {
        $translation = Translation::factory()->create(['value' => 'Old']);
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->patchJson('/api/v1/translations/' . $translation->id, ['value' => 'New']);
        $response->assertStatus(200)
            ->assertJsonPath('data.value', 'New');
    }

    public function test_destroy_deletes_translation(): void
    {
        $translation = Translation::factory()->create();
        $id = $translation->id;
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->deleteJson('/api/v1/translations/' . $id);
        $response->assertStatus(204);
        $this->assertDatabaseMissing('translations', ['id' => $id]);
    }
}
