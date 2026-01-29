<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Translation extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'translation_key_id',
        'locale_id',
        'value',
    ];

    /**
     * Get the translation key that owns this translation.
     */
    public function translationKey(): BelongsTo
    {
        return $this->belongsTo(TranslationKey::class);
    }

    /**
     * Get the locale that owns this translation.
     */
    public function locale(): BelongsTo
    {
        return $this->belongsTo(Locale::class);
    }

    /**
     * Get the tags for this translation.
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'translation_tag')
            ->withTimestamps();
    }
}
