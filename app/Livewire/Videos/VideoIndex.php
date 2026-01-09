<?php

namespace App\Livewire\Videos;

use App\Models\News;
use App\Models\Video;
use Livewire\Component;

class VideoIndex extends Component
{
    public $perPage = 8;

    public $popular_news;

    public $locale;

    public function mount()
    {
        $this->locale = app()->getLocale();
        $this->popular_news = News::whereHas('translations', fn ($query) => $query->where('lang', $this->locale))->orderBy('view_count', 'DESC')->limit(5)->get();
    }

    public function loadMore()
    {
        $this->perPage += 8;
    }

    public function render()
    {
        return view('livewire.videos.video-index', [
            'videos' => Video::latest()->take($this->perPage)->get(),
            'totalCount' => Video::count(),
            'popular_news' => $this->popular_news,
        ]);
    }
}
