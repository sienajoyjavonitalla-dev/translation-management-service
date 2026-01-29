<?php

namespace App\Http\Requests\Locale;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateLocaleRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    public function rules(): array
    {
        $localeId = $this->route('locale')?->id ?? $this->route('locale');

        return [
            'code' => [
                'sometimes',
                'required',
                'string',
                'max:10',
                Rule::unique('locales', 'code')->ignore($localeId),
            ],
            'name' => ['sometimes', 'required', 'string', 'max:100'],
        ];
    }

    protected function prepareForValidation(): void
    {
        if ($this->has('code')) {
            $this->merge([
                'code' => strtolower((string) $this->input('code')),
            ]);
        }
    }
}

