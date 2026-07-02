<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class NewsTranslation extends Model
{
    use HasFactory;

    protected $table = 'news_translations';

    protected $fillable = [
        'news_id',
        'lang',
        'title',
        'short_description',
        'content',
        'image_url',
        'seo_title',
        'seo_description',
    ];

    public function news(): BelongsTo
    {
        return $this->belongsTo(News::class);
    }

    /**
     * Resolve the cover image URL.
     *
     * Legacy values are bare filenames stored under public/images/news/.
     * New uploads live on the public storage disk under news/covers/.
     */
    public function coverUrl(): ?string
    {
        $value = $this->image_url;

        if ($value === null || $value === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $value) === 1) {
            return $value;
        }

        if (str_starts_with($value, 'news/')) {
            return Storage::disk('public')->url($value);
        }

        return asset('images/news/'.$value);
    }

    /**
     * Estimated reading time of the body content in whole minutes (min 1).
     */
    public function readingTime(): int
    {
        $text = trim(strip_tags((string) $this->content));

        if ($text === '') {
            return 1;
        }

        $words = count(preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY) ?: []);

        return max(1, (int) ceil($words / 200));
    }
}
