<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NewsTag extends Model
{
    protected $fillable = [
        'news_id',
        'tag_id',
    ];
}
