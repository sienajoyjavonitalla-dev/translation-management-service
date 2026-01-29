<?php

namespace App\Services;

use App\Models\Locale;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class TranslationExportService
{
    /**
     * Export translations.
     *
     * Shape:
     * - Single locale: { "auth.login": "Login", ... } (flat) or nested objects (nested)
     * - All locales: { "en": { ... }, "fr": { ... } }
     *
     * @return array<string, mixed>
     */
    public function export(?string $locale, bool $nested): array
    {
        if ($locale !== null && $locale !== '') {
            $localeModel = $this->resolveLocale($locale);
            if (!$localeModel) {
                return [];
            }

            $flat = $this->buildFlatForLocaleId($localeModel->id);

            return $nested ? $this->toNested($flat) : $flat;
        }

        $locales = Locale::query()->orderBy('code')->get(['id', 'code']);

        $result = [];
        foreach ($locales as $loc) {
            $flat = $this->buildFlatForLocaleId($loc->id);
            $result[$loc->code] = $nested ? $this->toNested($flat) : $flat;
        }

        return $result;
    }

    public function resolveLocale(?string $locale): ?Locale
    {
        if ($locale === null || $locale === '') {
            return null;
        }

        if (is_numeric($locale)) {
            return Locale::query()->find((int) $locale);
        }

        return Locale::query()->where('code', $locale)->first();
    }

    /**
     * @return array<string, string>
     */
    private function buildFlatForLocaleId(int $localeId): array
    {
        $rows = DB::table('translations')
            ->join('translation_keys', 'translation_keys.id', '=', 'translations.translation_key_id')
            ->where('translations.locale_id', $localeId)
            ->orderBy('translation_keys.key')
            ->select(['translation_keys.key as key', 'translations.value as value'])
            ->cursor();

        $out = [];
        foreach ($rows as $row) {
            $out[(string) $row->key] = (string) $row->value;
        }

        return $out;
    }

    /**
     * Convert flat dot-keys to nested objects.
     *
     * @param  array<string, string>  $flat
     * @return array<string, mixed>
     */
    private function toNested(array $flat): array
    {
        $nested = [];

        foreach ($flat as $key => $value) {
            $parts = explode('.', $key);
            $ref = &$nested;

            foreach ($parts as $i => $part) {
                if ($i === count($parts) - 1) {
                    $ref[$part] = $value;
                    break;
                }

                if (!isset($ref[$part]) || !is_array($ref[$part])) {
                    $ref[$part] = [];
                }

                $ref = &$ref[$part];
            }

            unset($ref);
        }

        return $nested;
    }
}

