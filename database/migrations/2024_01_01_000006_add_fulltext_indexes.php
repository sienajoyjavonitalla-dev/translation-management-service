<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // MySQL FULLTEXT indexes
            DB::statement('ALTER TABLE translations ADD FULLTEXT INDEX translations_value_fulltext (value)');
            // `key` is a reserved word in MySQL; escape it.
            DB::statement('ALTER TABLE translation_keys ADD FULLTEXT INDEX translation_keys_key_fulltext (`key`)');
        } elseif ($driver === 'pgsql') {
            // PostgreSQL GIN indexes for full-text search
            DB::statement('CREATE INDEX translations_value_gin_idx ON translations USING gin(to_tsvector(\'english\', value))');
            DB::statement('CREATE INDEX translation_keys_key_gin_idx ON translation_keys USING gin(to_tsvector(\'english\', key))');
        }
        // For other databases, regular indexes are already in place
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            // For FULLTEXT indexes, use raw SQL to ensure correct drop syntax.
            DB::statement('ALTER TABLE translations DROP INDEX translations_value_fulltext');
            DB::statement('ALTER TABLE translation_keys DROP INDEX translation_keys_key_fulltext');
        } elseif ($driver === 'pgsql') {
            DB::statement('DROP INDEX IF EXISTS translations_value_gin_idx');
            DB::statement('DROP INDEX IF EXISTS translation_keys_key_gin_idx');
        }
    }
};
