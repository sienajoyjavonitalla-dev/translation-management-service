<?php

namespace Tests\Feature\Api;

use App\Models\Locale;
use App\Models\Translation;
use App\Models\TranslationKey;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExportApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_export_all_locales_returns_structure(): void
    {
        Locale::factory()->create(['code' => 'en']);
        Locale::factory()->create(['code' => 'fr']);
        $response = $this->getJson('/api/v1/export');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertIsArray($data);
        $this->assertArrayHasKey('en', $data);
        $this->assertArrayHasKey('fr', $data);
    }

    public function test_export_single_locale_flat(): void
    {
        $locale = Locale::factory()->create(['code' => 'en']);
        $key = TranslationKey::factory()->create(['key' => 'auth.login']);
        Translation::factory()->create([
            'translation_key_id' => $key->id,
            'locale_id' => $locale->id,
            'value' => 'Login',
        ]);
        $response = $this->getJson('/api/v1/export?locale=en');
        $response->assertStatus(200);
        $data = $response->json();
        $this->assertArrayHasKey('auth.login', $data);
        $this->assertSame('Login', $data['auth.login']);
    }

    public function test_export_single_locale_nested(): void
    {
        $locale = Locale::factory()->create(['code' => 'en']);
        $key = TranslationKey::factory()->create(['key' => 'auth.login']);
        Translation::factory()->create([
            'translation_key_id' => $key->id,
            'locale_id' => $locale->id,
            'value' => 'Login',
        ]);
        $response = $this->getJson('/api/v1/export?locale=en&nested=true');
        $response->assertStatus(200)
            ->assertJsonPath('auth.login', 'Login');
    }

    public function test_export_unknown_locale_returns_empty(): void
    {
        $response = $this->getJson('/api/v1/export?locale=xx');
        $response->assertStatus(200)
            ->assertExactJson([]);
    }
}
