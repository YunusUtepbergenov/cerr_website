<?php

namespace App\Livewire\Videos;

use App\Models\News;
use App\Models\Video;
use Livewire\Component;

class VideoShow extends Component
{
    public Video $video;

    public $popular_news;

    public function mount($id)
    {
        $this->video = Video::findOrFail($id);
        $locale = app()->getLocale();
        $this->popular_news = News::whereHas('translations', fn ($query) => $query->where('lang', $locale))->orderBy('view_count', 'DESC')->limit(5)->get();
    }

    public function render()
    {
        return view('livewire.videos.video-show');
    }
}
