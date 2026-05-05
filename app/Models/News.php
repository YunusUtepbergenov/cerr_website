<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class News extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'category_id',
        'user_id',
        'slug',
        'is_main',
        'view_count',
        'status',
        'scheduled_at',
    ];

    protected $attributes = [
        'view_count' => 0,
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(NewsTranslation::class, 'news_id', 'id');
    }

    public function translation(): HasOne
    {
        return $this->hasOne(NewsTranslation::class, 'news_id', 'id')
            ->where('lang', app()->getLocale());
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(Tag::class, 'news_tags');
    }

    public function activity(): MorphMany
    {
        return $this->morphMany(Activity::class, 'subject', 'subject_type', 'subject_id');
    }

    /**
     * Restrict to news that should be visible publicly:
     * - status = 'published', or
     * - status = 'auto_publish' and scheduled_at has passed.
     */
    public function scopePublished($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'published')
                ->orWhere(function ($q2) {
                    $q2->where('status', 'auto_publish')
                        ->whereNotNull('scheduled_at')
                        ->where('scheduled_at', '<=', now());
                });
        });
    }
}
