<?php

namespace App\Services;

use App\Http\Controllers\Api\ExportController;
use App\Models\Translation;
use App\Models\TranslationKey;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class TranslationService
{
    /**
     * Create a translation (and key if needed).
     *
     * @param  array<string, mixed>  $data
     */
    public function create(array $data): Translation
    {
        return DB::transaction(function () use ($data) {
            $translationKeyId = $this->resolveTranslationKeyId($data);

            $translation = Translation::updateOrCreate(
                [
                    'translation_key_id' => $translationKeyId,
                    'locale_id' => (int) $data['locale_id'],
                ],
                [
                    'value' => (string) $data['value'],
                ]
            );

            if (array_key_exists('tag_ids', $data)) {
                $translation->tags()->sync($data['tag_ids'] ?? []);
            }

            ExportController::forgetExportCache((int) $translation->locale_id);

            return $translation->load(['translationKey', 'locale', 'tags']);
        });
    }

    /**
     * Update a translation and optionally re-key / re-locale.
     *
     * @param  array<string, mixed>  $data
     */
    public function update(Translation $translation, array $data): Translation
    {
        return DB::transaction(function () use ($translation, $data) {
            $oldLocaleId = (int) $translation->locale_id;

            if (array_key_exists('translation_key_id', $data) || array_key_exists('key', $data)) {
                $translation->translation_key_id = $this->resolveTranslationKeyId($data);
            }

            if (array_key_exists('locale_id', $data)) {
                $translation->locale_id = (int) $data['locale_id'];
            }

            if (array_key_exists('value', $data)) {
                $translation->value = (string) $data['value'];
            }

            $translation->save();

            if (array_key_exists('tag_ids', $data)) {
                $translation->tags()->sync($data['tag_ids'] ?? []);
            }

            ExportController::forgetExportCache($oldLocaleId);
            ExportController::forgetExportCache((int) $translation->locale_id);

            return $translation->fresh()->load(['translationKey', 'locale', 'tags']);
        });
    }

    public function delete(Translation $translation): void
    {
        $localeId = (int) $translation->locale_id;
        $translation->delete();
        ExportController::forgetExportCache($localeId);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    private function resolveTranslationKeyId(array $data): int
    {
        if (!empty($data['translation_key_id'])) {
            return (int) $data['translation_key_id'];
        }

        $key = trim((string) ($data['key'] ?? ''));

        if ($key === '') {
            throw ValidationException::withMessages([
                'key' => ['Either key or translation_key_id is required.'],
            ]);
        }

        return TranslationKey::firstOrCreate(['key' => $key])->id;
    }
}

