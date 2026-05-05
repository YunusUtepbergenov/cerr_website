<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Category extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'slug',
        'status',
    ];

    public function translations(): HasMany
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(CategoryTranslation::class)->where('language', app()->getLocale());
    }

    public function news(): HasMany
    {
        return $this->hasMany(News::class)->published()->whereHas('translation')->latest();
    }

    public function getLatestNews(): HasMany
    {
        return $this->hasMany(News::class)->published()->whereHas('translation')->latest()->limit(3);
    }
}
