<?php

namespace Database\Factories;

use App\Models\TranslationKey;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\TranslationKey>
 */
class TranslationKeyFactory extends Factory
{
    protected $model = TranslationKey::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $section = fake()->randomElement(['auth', 'validation', 'ui', 'message', 'error', 'common']);
        $item = fake()->unique()->slug(2);

        return [
            'key' => "{$section}.{$item}",
        ];
    }
}
