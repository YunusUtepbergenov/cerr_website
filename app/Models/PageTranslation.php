<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class PageTranslation extends Model
{
    use HasFactory;

    protected $fillable = [
        'page_id',
        'language',
        'title',
        'content',
        'image',
        'seo_title',
        'seo_description',
    ];

    public function page()
    {
        return $this->belongsTo(Page::class);
    }

    /**
     * Resolve the page's cover image URL, handling the mix of legacy values in
     * this column: absolute URLs, host-relative paths, uploaded storage paths
     * (pages/…), bare filenames under public/images/, or nothing.
     */
    public function coverUrl(): ?string
    {
        $value = $this->image;

        if ($value === null || $value === '' || $value === '1') {
            return null;
        }

        if (preg_match('#^https?://#i', $value) === 1 || str_starts_with($value, '/')) {
            return $value;
        }

        if (str_starts_with($value, 'pages/') || str_starts_with($value, 'news/')) {
            return Storage::disk('public')->url($value);
        }

        return asset('images/'.$value);
    }
}
