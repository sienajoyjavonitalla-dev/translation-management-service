<?php

namespace Tests\Unit\Services;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use App\Models\TranslationKey;
use App\Services\TranslationSearchService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class TranslationSearchServiceTest extends TestCase
{
    use RefreshDatabase;

    private TranslationSearchService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = new TranslationSearchService;
    }

    public function test_search_returns_paginator(): void
    {
        $result = $this->service->search([], 15);
        $this->assertSame(15, $result->perPage());
        $this->assertCount(0, $result->items());
    }

    public function test_search_filters_by_locale_id(): void
    {
        $locale1 = Locale::factory()->create();
        $locale2 = Locale::factory()->create();
        $key = TranslationKey::factory()->create();
        Translation::factory()->create(['translation_key_id' => $key->id, 'locale_id' => $locale1->id]);
        Translation::factory()->create(['translation_key_id' => $key->id, 'locale_id' => $locale2->id]);

        $result = $this->service->search(['locale' => (string) $locale1->id], 10);
        $this->assertCount(1, $result->items());
        $this->assertSame($locale1->id, $result->items()[0]->locale_id);
    }

    public function test_search_filters_by_locale_code(): void
    {
        $locale = Locale::factory()->create(['code' => 'fr']);
        $key = TranslationKey::factory()->create();
        Translation::factory()->create(['translation_key_id' => $key->id, 'locale_id' => $locale->id]);

        $result = $this->service->search(['locale' => 'fr'], 10);
        $this->assertCount(1, $result->items());
    }

    public function test_search_filters_by_tag(): void
    {
        $tag = Tag::factory()->create(['name' => 'mobile']);
        $translation = Translation::factory()->create();
        $translation->tags()->attach($tag->id);

        $result = $this->service->search(['tag' => 'mobile'], 10);
        $this->assertCount(1, $result->items());
    }

    public function test_search_per_page_capped(): void
    {
        $result = $this->service->search([], 200);
        $this->assertSame(100, $result->perPage());
    }
}
