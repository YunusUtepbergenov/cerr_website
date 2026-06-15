<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Number;
use Illuminate\Support\Str;

class OpenData extends Model
{
    use HasFactory, LogsActivity;

    protected $table = 'open_data';

    protected $fillable = [
        'year',
        'quarter',
        'file_path',
        'file_name',
        'file_size',
        'file_mime',
        'is_published',
        'user_id',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'year' => 'integer',
            'quarter' => 'integer',
            'file_size' => 'integer',
            'download_count' => 'integer',
            'is_published' => 'boolean',
        ];
    }

    public function translations(): HasMany
    {
        return $this->hasMany(OpenDataTranslation::class);
    }

    public function translation(): HasOne
    {
        return $this->hasOne(OpenDataTranslation::class)->where('language', app()->getLocale());
    }

    public function uploader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true);
    }

    public function title(): string
    {
        $current = $this->translations->firstWhere('language', app()->getLocale());

        return $current?->title ?? $this->translations->first()?->title ?? '';
    }

    public function quarterLabel(): string
    {
        return [1 => 'I', 2 => 'II', 3 => 'III', 4 => 'IV'][$this->quarter] ?? '';
    }

    public function fileExtension(): string
    {
        return Str::upper(pathinfo($this->file_name, PATHINFO_EXTENSION));
    }

    public function fileSizeForHumans(): string
    {
        return Number::fileSize($this->file_size, precision: 1);
    }

    public function downloadUrl(): string
    {
        return route('open-data.download', $this);
    }
}
