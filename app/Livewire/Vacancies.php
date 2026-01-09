<?php

namespace App\Livewire;

use App\Models\News;
use App\Models\Page;
use Livewire\Component;

class Vacancies extends Component
{
    public $page;

    public $popular_news;

    public function mount()
    {
        $this->page = Page::with('translation')->where('slug', 'vacancies')->first();

        if (! $this->page || ! $this->page->translation) {
            abort(404);
        }

        $this->popular_news = News::whereHas('translations', fn ($query) => $query->where('lang', app()->getLocale()))->orderBy('view_count', 'DESC')->limit(8)->get();
    }

    public function render()
    {
        return view('livewire.vacancies');
    }
}
