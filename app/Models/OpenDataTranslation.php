<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OpenDataTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'open_data_id',
        'language',
        'title',
    ];

    public function openData(): BelongsTo
    {
        return $this->belongsTo(OpenData::class);
    }
}
