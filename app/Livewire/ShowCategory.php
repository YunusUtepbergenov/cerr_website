<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\News;
use Livewire\Component;

class ShowCategory extends Component
{
    public $category;

    public $popular_news;

    public function mount($slug)
    {
        $this->category = Category::with(['translation', 'news'])->where('slug', $slug)->first();

        if (! $this->category || ! $this->category->translation) {
            abort(404);
        }

        $locale = app()->getLocale();
        $this->popular_news = News::whereHas('translations', fn ($query) => $query->where('lang', $locale))->orderBy('view_count', 'DESC')->limit(6)->get();
    }

    public function render()
    {
        return view('livewire.show-category');
    }
}
