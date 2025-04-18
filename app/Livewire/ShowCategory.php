<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\News;
use Livewire\Component;

class ShowCategory extends Component
{
    public $category;
    public $popular_news;

    public function mount($slug){
        $this->category = Category::with(['translation', 'news'])->where('slug', $slug)->first();
    }

    public function render()
    {
        $this->popular_news = News::whereHas('translations', fn($query) => $query->where('lang', app()->getLocale()))->orderBy('view_count', 'DESC')->limit(6)->get();
        
        return view('livewire.show-category');
    }
}
