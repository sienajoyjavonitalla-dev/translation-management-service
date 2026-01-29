<?php

namespace Database\Seeders;

use App\Models\Locale;
use Illuminate\Database\Seeder;

class LocaleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $locales = [
            ['code' => 'en', 'name' => 'English'],
            ['code' => 'fr', 'name' => 'French'],
            ['code' => 'es', 'name' => 'Spanish'],
        ];

        foreach ($locales as $locale) {
            Locale::firstOrCreate(
                ['code' => $locale['code']],
                ['name' => $locale['name']]
            );
        }
    }
}
