<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;

    public function translations()
    {
        return $this->hasMany(CategoryTranslation::class);
    }

    public function translation()
    {
        return $this->hasOne(CategoryTranslation::class)->where('language', app()->getLocale());
    }

    public function news(){
        return $this->hasMany(News::class)->whereHas('translation')->latest();
    }

    public function getLatestNews(){
        return $this->hasMany(News::class)->whereHas('translation')->latest()->limit(3);
    }
}
