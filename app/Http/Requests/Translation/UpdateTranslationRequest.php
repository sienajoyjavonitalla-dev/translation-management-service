<?php

namespace App\Http\Requests\Translation;

use Illuminate\Foundation\Http\FormRequest;

class UpdateTranslationRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        return [
            // Allow changing key by providing translation_key_id or key; optional
            'translation_key_id' => ['sometimes', 'nullable', 'integer', 'exists:translation_keys,id'],
            'key' => ['sometimes', 'nullable', 'string', 'max:255'],

            'locale_id' => ['sometimes', 'required', 'integer', 'exists:locales,id'],
            'value' => ['sometimes', 'required', 'string'],

            'tag_ids' => ['sometimes', 'array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
        ];
    }

    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            if (!$this->hasAny(['translation_key_id', 'key', 'locale_id', 'value', 'tag_ids'])) {
                $validator->errors()->add('base', 'At least one field is required.');
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

