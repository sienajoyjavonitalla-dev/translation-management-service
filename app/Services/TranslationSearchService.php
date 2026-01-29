<?php

namespace App\Services;

use App\Models\Locale;
use App\Models\Tag;
use App\Models\Translation;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class TranslationSearchService
{
    /**
     * @param  array<string, mixed>  $filters
     */
    public function search(array $filters, int $perPage = 50): LengthAwarePaginator
    {
        $perPage = max(1, min($perPage, 100));

        $query = Translation::query()
            ->with(['translationKey', 'locale', 'tags'])
            ->select('translations.*');

        $this->applyLocaleFilter($query, $filters['locale'] ?? null);
        $this->applyTagFilter($query, $filters['tag'] ?? null);
        $this->applyKeyFilter($query, $filters['key'] ?? null);
        $this->applyContentFilter($query, $filters['content'] ?? null);

        return $query
            ->orderByDesc('translations.updated_at')
            ->paginate($perPage);
    }

    private function applyLocaleFilter(Builder $query, mixed $locale): void
    {
        if ($locale === null || $locale === '') {
            return;
        }

        if (is_numeric($locale)) {
            $query->where('translations.locale_id', (int) $locale);

            return;
        }

        $localeModel = Locale::query()->where('code', (string) $locale)->first();

        if ($localeModel) {
            $query->where('translations.locale_id', $localeModel->id);
        }
    }

    private function applyTagFilter(Builder $query, mixed $tag): void
    {
        if ($tag === null || $tag === '') {
            return;
        }

        $tagId = null;

        if (is_numeric($tag)) {
            $tagId = (int) $tag;
        } else {
            $tagModel = Tag::query()->where('name', (string) $tag)->first();
            $tagId = $tagModel?->id;
        }

        if ($tagId) {
            $query->whereHas('tags', function (Builder $q) use ($tagId) {
                $q->where('tags.id', $tagId);
            });
        }
    }

    private function applyKeyFilter(Builder $query, mixed $key): void
    {
        if ($key === null || $key === '') {
            return;
        }

        $key = trim((string) $key);

        $query->join('translation_keys', 'translation_keys.id', '=', 'translations.translation_key_id');

        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $query->whereRaw('MATCH(translation_keys.`key`) AGAINST(? IN BOOLEAN MODE)', [$this->toBooleanSearch($key)]);

            return;
        }

        $query->where('translation_keys.key', 'like', '%' . $this->escapeLike($key) . '%');
    }

    private function applyContentFilter(Builder $query, mixed $content): void
    {
        if ($content === null || $content === '') {
            return;
        }

        $content = trim((string) $content);
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $query->whereRaw('MATCH(translations.value) AGAINST(? IN BOOLEAN MODE)', [$this->toBooleanSearch($content)]);

            return;
        }

        $query->where('translations.value', 'like', '%' . $this->escapeLike($content) . '%');
    }

    private function escapeLike(string $value): string
    {
        return str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $value);
    }

    private function toBooleanSearch(string $value): string
    {
        // Basic boolean-mode tokenization: split by whitespace and require each token.
        $tokens = preg_split('/\\s+/', trim($value)) ?: [];
        $tokens = array_filter($tokens, static fn ($t) => $t !== '');

        return implode(' ', array_map(static fn ($t) => '+' . $t . '*', $tokens));
    }
}

