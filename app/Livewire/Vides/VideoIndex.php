<?php

namespace App\Livewire\Vides;

use App\Models\News;
use App\Models\Video;
use Livewire\Component;
use Livewire\WithPagination;

class VideoIndex extends Component
{
    use WithPagination;

    public $popular_news;    

    public function render()
    {
        $videos = Video::latest()->paginate(10);
        $this->popular_news = News::whereHas('translations', fn($query) => $query->where('lang', app()->getLocale()))->orderBy('view_count', 'DESC')->limit(5)->get();


        return view('livewire.vides.video-index', [
            'videos' => $videos,
        ]);
    }
}
