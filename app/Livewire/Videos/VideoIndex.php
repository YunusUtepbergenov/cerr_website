<?php

namespace App\Livewire\Videos;

use App\Models\News;
use App\Models\Video;
use Livewire\Component;

class VideoIndex extends Component
{
    public const PER_PAGE_STEP = 8;

    public const PER_PAGE_MAX = 200;

    public $perPage = self::PER_PAGE_STEP;

    public $popular_news;

    public $locale;

    public function mount()
    {
        $this->locale = app()->getLocale();
        $this->popular_news = News::whereHas('translations', fn ($query) => $query->where('lang', $this->locale))->with(['translation' => fn ($query) => $query->cardColumns()])->orderBy('view_count', 'DESC')->limit(5)->get();
    }

    public function loadMore()
    {
        $this->perPage = min((int) $this->perPage + self::PER_PAGE_STEP, self::PER_PAGE_MAX);
    }

    public function render()
    {
        $perPage = max(1, min((int) $this->perPage, self::PER_PAGE_MAX));

        return view('livewire.videos.video-index', [
            'videos' => Video::latest()->take($perPage)->get(),
            'totalCount' => Video::count(),
            'popular_news' => $this->popular_news,
        ]);
    }
}
