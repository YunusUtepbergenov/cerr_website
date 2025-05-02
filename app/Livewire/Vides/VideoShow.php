<?php

namespace App\Livewire\Vides;

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
    }

    public function render()
    {
        $this->popular_news = News::whereHas('translations', fn($query) => $query->where('lang', app()->getLocale()))->orderBy('view_count', 'DESC')->limit(5)->get();

        return view('livewire.vides.video-show');
    }
}
