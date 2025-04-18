<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NewsTranslation extends Model
{
    use HasFactory;
    
    protected $table = 'news_translations';

    public function news(){
        return $this->belongsTo(News::class);
    }
}
