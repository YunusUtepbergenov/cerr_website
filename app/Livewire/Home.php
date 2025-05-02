<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\News;
use App\Models\NewsTranslation;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class Home extends Component
{
    public $latest_news, $events, $infographics;
    public $popular_news;
    public $main;
    public $categories;
    public function render()
    {
        // $this->main = News::where('is_main', true)->whereHas('translation')->latest()->first();

        // $this->popular_news = News::whereHas('translations', fn($query) => $query->where('lang', app()->getLocale()))->orderBy('view_count', 'DESC')->limit(4)->get();

        $this->latest_news = News::with('translation')->whereHas('translation')->where('category_id', 12)->latest()->limit(10)->get();

        $this->events = News::with('translation')->whereHas('translation')->where('category_id', 13)->latest()->limit(10)->get();

        $this->infographics = News::with('translation')->whereHas('translation')->where('category_id', 11)->latest()->limit(10)->get();

        $this->categories = Category::with(['translation', 'news'])->limit(3)->get();
        
        return view('livewire.home');
    }
}
