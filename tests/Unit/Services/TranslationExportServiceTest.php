<?php

namespace Tests\Unit\Services;

use App\Models\Locale;
use App\Models\Translation;
use App\Models\TranslationKey;
use App\Services\TranslationExportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationExportServiceTest extends TestCase
{
    use RefreshDatabase;

    private TranslationExportService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TranslationExportService;
    }

    public function test_export_empty_returns_empty_array_for_unknown_locale(): void
    {
        $result = $this->service->export('xx', false);
        $this->assertSame([], $result);
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

        $result = $this->service->export('en', false);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('auth.login', $result);
        $this->assertSame('Login', $result['auth.login']);
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

        $result = $this->service->export('en', true);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('auth', $result);
        $this->assertArrayHasKey('login', $result['auth']);
        $this->assertSame('Login', $result['auth']['login']);
    }

    public function test_export_all_locales(): void
    {
        Locale::factory()->create(['code' => 'en']);
        Locale::factory()->create(['code' => 'fr']);
        $result = $this->service->export(null, false);
        $this->assertIsArray($result);
        $this->assertArrayHasKey('en', $result);
        $this->assertArrayHasKey('fr', $result);
    }

    public function test_resolve_locale_by_code(): void
    {
        $locale = Locale::factory()->create(['code' => 'de']);
        $this->assertNotNull($this->service->resolveLocale('de'));
        $this->assertSame($locale->id, $this->service->resolveLocale('de')->id);
    }

    public function test_resolve_locale_by_id(): void
    {
        $locale = Locale::factory()->create();
        $this->assertSame($locale->id, $this->service->resolveLocale((string) $locale->id)->id);
    }
}
