<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translation_key_id')->constrained('translation_keys')->onDelete('cascade');
            $table->foreignId('locale_id')->constrained('locales')->onDelete('cascade');
            $table->text('value')->comment('Translation value');
            $table->timestamps();

            // Composite unique constraint: one translation per key-locale combination
            $table->unique(['translation_key_id', 'locale_id'], 'translations_key_locale_unique');

            // Indexes for performance
            $table->index(['locale_id', 'translation_key_id'], 'translations_locale_key_idx');
            $table->index('updated_at', 'translations_updated_at_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translations');
    }
};
