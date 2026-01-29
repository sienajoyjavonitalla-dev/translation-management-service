<?php

namespace App\Http\Requests\Translation;

use Illuminate\Foundation\Http\FormRequest;

class StoreTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Either provide translation_key_id OR a key string (preferred for API ergonomics)
            'translation_key_id' => ['nullable', 'integer', 'exists:translation_keys,id'],
            'key' => ['nullable', 'string', 'max:255'],

            'locale_id' => ['required', 'integer', 'exists:locales,id'],
            'value' => ['required', 'string'],

            'tag_ids' => ['sometimes', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $hasId = $this->filled('translation_key_id');
            $hasKey = $this->filled('key');

            if (!$hasId && !$hasKey) {
                $validator->errors()->add('key', 'Either key or translation_key_id is required.');
            }
        });
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('key')) {
            $this->merge([
                'key' => trim((string) $this->input('key')),
            ]);
        }
    }
}

