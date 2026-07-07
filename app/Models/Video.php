<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'title',
        'image',
        'url',
    ];

    /**
     * Resolve a thumbnail: an uploaded image if one exists, otherwise the
     * video's own YouTube thumbnail derived from its URL, otherwise null.
     */
    public function thumbnailUrl(): ?string
    {
        if ($this->image && file_exists(public_path('images/video/'.$this->image))) {
            return asset('images/video/'.$this->image);
        }

        if ($id = $this->youtubeId()) {
            return 'https://img.youtube.com/vi/'.$id.'/hqdefault.jpg';
        }

        if ($this->image && preg_match('#^https?://#i', $this->image) === 1) {
            return $this->image;
        }

        return null;
    }

    /**
     * Extract the 11-character YouTube video id from the stored URL, if any.
     */
    public function youtubeId(): ?string
    {
        if (! $this->url) {
            return null;
        }

        if (preg_match('#(?:youtu\.be/|youtube\.com/(?:watch\?v=|embed/|v/|shorts/))([A-Za-z0-9_-]{11})#', $this->url, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }
}
