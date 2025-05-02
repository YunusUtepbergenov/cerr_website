<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    public function translations()
    {
        return $this->hasMany(PageTranslation::class, 'page_id', 'id');
    }

    public function translation()
    {
        return $this->hasOne(PageTranslation::class, 'page_id', 'id')
            ->where('language', app()->getLocale());
    }
}
