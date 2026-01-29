<?php

namespace App\Console\Commands;

use App\Models\Locale;
use App\Models\Tag;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SeedLargeTranslationsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'translations:seed-large
                            {--count=100000 : Number of translation rows to create}
                            {--chunk=2000 : Chunk size for bulk inserts}
                            {--no-pivot : Skip attaching tags to translations}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Populate the database with a large number of translations for scalability testing (100k+ by default)';

    public function handle(): int
    {
        $count = (int) $this->option('count');
        $chunkSize = max(500, min(5000, (int) $this->option('chunk')));
        $withPivot = !$this->option('no-pivot');

        if ($count < 1) {
            $this->error('Count must be at least 1.');

            return self::FAILURE;
        }

        $locales = Locale::query()->orderBy('id')->get(['id']);
        $tags = Tag::query()->orderBy('id')->get(['id']);

        if ($locales->isEmpty()) {
            $this->error('No locales found. Run php artisan db:seed first (LocaleSeeder).');

            return self::FAILURE;
        }

        if ($withPivot && $tags->isEmpty()) {
            $this->error('No tags found. Run php artisan db:seed first (TagSeeder).');

            return self::FAILURE;
        }

        $localeIds = $locales->pluck('id')->all();
        $tagIds = $tags->pluck('id')->all();
        $numLocales = count($localeIds);
        $numKeys = (int) ceil($count / $numLocales);
        $now = now();

        $this->info("Creating {$numKeys} translation keys and ~" . ($numKeys * $numLocales) . " translation rows (target ≥ {$count})...");

        // 1) Create translation keys in chunks
        $keyChunk = 1000;
        $createdKeys = 0;
        $allKeyIds = [];

        for ($offset = 0; $offset < $numKeys; $offset += $keyChunk) {
            $batchSize = min($keyChunk, $numKeys - $offset);
            $keys = [];

            for ($i = 0; $i < $batchSize; $i++) {
                $idx = $offset + $i;
                $section = ['auth', 'validation', 'ui', 'message', 'error', 'common'][$idx % 6];
                $keys[] = [
                    'key' => "{$section}.item.{$idx}",
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('translation_keys')->insert($keys);
            $createdKeys += $batchSize;

            $lastId = DB::table('translation_keys')->orderByDesc('id')->value('id');
            for ($i = $batchSize - 1; $i >= 0; $i--) {
                $allKeyIds[] = $lastId - $i;
            }

            $this->output->write('.');
        }

        $this->newLine();
        $this->info("Created {$createdKeys} translation keys.");

        // 2) Bulk insert translations (key_id × locale_id × value)
        $translationChunk = $chunkSize;
        $translationRows = [];
        $totalTranslations = 0;
        $keyIndex = 0;

        foreach ($allKeyIds as $keyId) {
            foreach ($localeIds as $localeId) {
                $translationRows[] = [
                    'translation_key_id' => $keyId,
                    'locale_id' => $localeId,
                    'value' => 'Translation value for key ' . $keyId . ' locale ' . $localeId,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
                $totalTranslations++;

                if (count($translationRows) >= $translationChunk) {
                    DB::table('translations')->insert($translationRows);
                    $translationRows = [];
                    $this->output->write('.');
                }
            }
        }

        if (!empty($translationRows)) {
            DB::table('translations')->insert($translationRows);
            $this->output->write('.');
        }

        $this->newLine();
        $this->info("Created {$totalTranslations} translations.");

        // 3) Pivot: attach one tag per translation (in chunks)
        if ($withPivot && count($tagIds) > 0) {
            $pivotChunk = 5000;
            $minId = DB::table('translations')->min('id');
            $maxId = DB::table('translations')->max('id');
            $pivotRows = [];
            $cursor = $minId;

            while ($cursor !== null && $cursor <= $maxId) {
                $ids = DB::table('translations')->where('id', '>=', $cursor)->orderBy('id')->limit($pivotChunk)->pluck('id');
                if ($ids->isEmpty()) {
                    break;
                }

                foreach ($ids as $translationId) {
                    $tagId = $tagIds[array_rand($tagIds)];
                    $pivotRows[] = [
                        'translation_id' => $translationId,
                        'tag_id' => $tagId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ];
                }

                if (count($pivotRows) >= $pivotChunk) {
                    DB::table('translation_tag')->insert($pivotRows);
                    $pivotRows = [];
                    $this->output->write('.');
                }

                $cursor = $ids->max() + 1;
            }

            if (!empty($pivotRows)) {
                DB::table('translation_tag')->insert($pivotRows);
                $this->output->write('.');
            }

            $this->newLine();
            $this->info('Attached tags to translations (translation_tag).');
        }

        $this->info('Done. List and export endpoints should stay within performance targets (<200ms / <500ms).');

        return self::SUCCESS;
    }
}
