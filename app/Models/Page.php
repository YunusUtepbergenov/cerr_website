<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Page extends Model
{
    use HasFactory;

    protected $fillable = ['slug'];

    public function translations(): HasMany
    {
        return $this->hasMany(PageTranslation::class, 'page_id', 'id');
    }

    public function translation(): HasOne
    {
        return $this->hasOne(PageTranslation::class, 'page_id', 'id')
            ->where('language', app()->getLocale());
    }
}
