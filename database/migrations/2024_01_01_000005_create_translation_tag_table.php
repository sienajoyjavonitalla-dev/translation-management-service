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
        Schema::create('translation_tag', function (Blueprint $table) {
            $table->id();
            $table->foreignId('translation_id')->constrained('translations')->onDelete('cascade');
            $table->foreignId('tag_id')->constrained('tags')->onDelete('cascade');
            $table->timestamps();

            // Composite primary key to prevent duplicates
            $table->unique(['translation_id', 'tag_id'], 'translation_tag_unique');

            // Indexes for performance
            $table->index('translation_id', 'translation_tag_translation_idx');
            $table->index('tag_id', 'translation_tag_tag_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('translation_tag');
    }
};
