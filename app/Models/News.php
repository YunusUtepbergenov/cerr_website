<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class News extends Model
{
    use HasFactory;

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
}
