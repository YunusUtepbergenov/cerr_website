<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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
            return $this->youtubeThumbnailUrl($id);
        }

        if ($this->image && preg_match('#^https?://#i', $this->image) === 1) {
            return $this->image;
        }

        return null;
    }

    /**
     * Prefer the 1280x720 maxresdefault thumbnail when YouTube has one,
     * falling back to the always-available 480x360 hqdefault. Only videos
     * uploaded in HD get a maxresdefault, so availability is probed once
     * and cached for a week; a probe that cannot connect falls back and is
     * only cached briefly so the thumbnail can still upgrade later.
     */
    protected function youtubeThumbnailUrl(string $id): string
    {
        $cacheKey = 'video-maxres:'.$id;
        $hasMaxres = Cache::get($cacheKey);

        if ($hasMaxres === null) {
            try {
                $hasMaxres = Http::timeout(2)
                    ->head('https://img.youtube.com/vi/'.$id.'/maxresdefault.jpg')
                    ->successful();
                Cache::put($cacheKey, $hasMaxres, now()->addWeek());
            } catch (ConnectionException) {
                $hasMaxres = false;
                Cache::put($cacheKey, false, now()->addMinutes(10));
            }
        }

        return $hasMaxres
            ? 'https://img.youtube.com/vi/'.$id.'/maxresdefault.jpg'
            : 'https://img.youtube.com/vi/'.$id.'/hqdefault.jpg';
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
