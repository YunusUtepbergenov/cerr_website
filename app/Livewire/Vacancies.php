<?php

namespace App\Livewire;

use App\Models\News;
use App\Models\Page;
use Livewire\Component;

class Vacancies extends Component
{
    public $page, $popular_news;

    public function render()
    {
        $this->page = Page::with('translation')->where('slug', 'vacancies')->first();
        $this->popular_news = News::whereHas('translations', fn($query) => $query->where('lang', app()->getLocale()))->orderBy('view_count', 'DESC')->limit(8)->get();

        return view('livewire.vacancies');
    }
}
