<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    public function translations()
    {
        return $this->hasMany(NewsTranslation::class, 'news_id', 'id');
    }

    // public function translation()
    // {
    //     return $this->translations()->where('lang', app()->getLocale())->first();
    // }

    public function translation()
    {
        return $this->hasOne(NewsTranslation::class, 'news_id', 'id')
            ->where('lang', app()->getLocale());
    }
}