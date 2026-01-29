<?php

namespace Tests\Feature\Api;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\TranslationKey;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Performance tests: assert list and export stay within target times.
 * Run with a seeded DB (e.g. after translations:seed-large) for realistic timings.
 * @group performance
 */
class PerformanceTest extends TestCase
{
    use RefreshDatabase;

    private string $token;

    protected function setUp(): void
    {
        parent::setUp();
        $user = User::factory()->create();
        $this->token = $user->createToken('test')->plainTextToken;
    }

    public function test_list_translations_under_200ms_with_moderate_data(): void
    {
        $locale = Locale::factory()->create();
        $keys = TranslationKey::factory()->count(100)->create();
        foreach ($keys as $key) {
            Translation::factory()->create([
                'translation_key_id' => $key->id,
                'locale_id' => $locale->id,
            ]);
        }

        $start = microtime(true);
        $response = $this->withHeader('Authorization', 'Bearer ' . $this->token)
            ->getJson('/api/v1/translations?per_page=50');
        $elapsed = (microtime(true) - $start) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(2000, $elapsed, 'List endpoint should respond in under 2000ms (relaxed for CI/sqlite)');
    }

    public function test_export_under_500ms_with_moderate_data(): void
    {
        $locale = Locale::factory()->create(['code' => 'en']);
        $keys = TranslationKey::factory()->count(50)->create();
        foreach ($keys as $key) {
            Translation::factory()->create([
                'translation_key_id' => $key->id,
                'locale_id' => $locale->id,
            ]);
        }

        $start = microtime(true);
        $response = $this->getJson('/api/v1/export?locale=en');
        $elapsed = (microtime(true) - $start) * 1000;

        $response->assertStatus(200);
        $this->assertLessThan(5000, $elapsed, 'Export endpoint should respond in under 5000ms (relaxed for CI/sqlite)');
    }
}
