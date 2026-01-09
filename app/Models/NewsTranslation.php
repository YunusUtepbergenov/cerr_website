<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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

    public function news()
    {
        return $this->belongsTo(News::class);
    }
}
