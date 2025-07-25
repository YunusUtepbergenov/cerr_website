<?php

namespace App\Livewire;

use App\Models\News;
use Livewire\Component;

class ShowNews extends Component
{
    public $news;
    public $popular_news;

    public function mount($slug){
        $this->news = News::with('translation')->where('slug', $slug)->first();

        if(!$this->news->translation){
            return redirect('/');
        }

        $this->popular_news = News::whereHas('translations', fn($query) => $query->where('lang', app()->getLocale()))->orderBy('view_count', 'DESC')->limit(6)->get();
    }

    public function render()
    {
        return view('livewire.show-news');
    }
}
