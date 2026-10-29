<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Facades\Storage;

class Page extends Model
{
    use HasFactory, LogsActivity;

    /**
     * Slugs the site renders on fixed routes. These pages cannot be deleted
     * from the admin, because their public URL would start returning 404.
     *
     * @var array<string, string>
     */
    public const PUBLIC_ROUTES = [
        'objectives' => 'about',
        'history' => 'history',
        'vacancies' => 'vacancies',
    ];

    protected $fillable = ['slug', 'image'];

    public function translations(): HasMany
    {
        return $this->hasMany(PageTranslation::class, 'page_id', 'id');
    }

    public function translation(): HasOne
    {
        return $this->hasOne(PageTranslation::class, 'page_id', 'id')
            ->where('language', app()->getLocale());
    }

    public function isSystemPage(): bool
    {
        return isset(self::PUBLIC_ROUTES[$this->slug]);
    }

    /**
     * The public URL for a page bound to a site route, null for free-form pages.
     */
    public function publicUrl(): ?string
    {
        $route = self::PUBLIC_ROUTES[$this->slug] ?? null;

        return $route === null ? null : route($route);
    }

    /**
     * The hero photo: an image uploaded in the admin, else the Centre's building.
     */
    public function heroImageUrl(): string
    {
        $image = (string) $this->image;

        if ($image === '') {
            return asset('images/research-staff/cerr-building.webp');
        }
        if (preg_match('#^https?://#i', $image) === 1 || str_starts_with($image, '/')) {
            return $image;
        }

        return Storage::disk('public')->url($image);
    }
}
