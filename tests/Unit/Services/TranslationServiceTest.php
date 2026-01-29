<?php

namespace Tests\Unit\Services;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\TranslationKey;
use App\Services\TranslationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class TranslationServiceTest extends TestCase
{
    use RefreshDatabase;

    private TranslationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TranslationService;
    }

    public function test_create_creates_translation_and_key(): void
    {
        $locale = Locale::factory()->create();
        $tag = Tag::factory()->create();

        $translation = $this->service->create([
            'key' => 'auth.login',
            'locale_id' => $locale->id,
            'value' => 'Login',
            'tag_ids' => [$tag->id],
        ]);

        $this->assertInstanceOf(Translation::class, $translation);
        $this->assertSame('Login', $translation->value);
        $this->assertSame($locale->id, $translation->locale_id);
        $this->assertTrue(TranslationKey::where('key', 'auth.login')->exists());
        $this->assertTrue($translation->tags->contains($tag));
    }

    public function test_create_uses_existing_key(): void
    {
        $key = TranslationKey::factory()->create(['key' => 'auth.logout']);
        $locale = Locale::factory()->create();

        $translation = $this->service->create([
            'key' => 'auth.logout',
            'locale_id' => $locale->id,
            'value' => 'Logout',
        ]);

        $this->assertSame($key->id, $translation->translation_key_id);
        $this->assertSame(1, TranslationKey::where('key', 'auth.logout')->count());
    }

    public function test_create_throws_when_key_missing(): void
    {
        $locale = Locale::factory()->create();

        $this->expectException(ValidationException::class);
        $this->service->create([
            'locale_id' => $locale->id,
            'value' => 'X',
        ]);
    }

    public function test_update_changes_value(): void
    {
        $translation = Translation::factory()->create(['value' => 'Old']);
        $this->service->update($translation, ['value' => 'New']);
        $translation->refresh();
        $this->assertSame('New', $translation->value);
    }

    public function test_delete_removes_translation(): void
    {
        $translation = Translation::factory()->create();
        $id = $translation->id;
        $this->service->delete($translation);
        $this->assertNull(Translation::find($id));
    }
}
