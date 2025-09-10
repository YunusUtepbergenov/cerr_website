<?php

namespace App\Livewire\Vides;

use App\Models\News;
use App\Models\Video;
use Livewire\Component;

class VideoIndex extends Component
{
    public $perPage = 2;

    public function loadMore(){
        $this->perPage += 2;
    }

    public function render(){
        return view('livewire.vides.video-index', [
            'videos' => Video::take($this->perPage)->get(),
            'totalCount' => Video::count(),
            'popular_news' => News::whereHas('translations', fn($query) => $query->where('lang', app()->getLocale()))->orderBy('view_count', 'DESC')->limit(5)->get()
        ]);
    }
}
