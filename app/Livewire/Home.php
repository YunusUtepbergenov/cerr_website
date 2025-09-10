<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\News;
use App\Models\NewsTranslation;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Livewire\Component;

class Home extends Component
{
    public $latest_news, $events, $infographics, $videos;
    public $popular_news;
    public $main;
    public $categories;
    public function render()
    {
        $this->latest_news = News::with('translation')->whereHas('translation')->where('category_id', 12)->latest()->limit(10)->get();

        $this->events = News::with('translation')->whereHas('translation')->where('category_id', 13)->latest()->limit(10)->get();

        $this->videos = Video::latest()->take(4)->get();

        $this->infographics = News::with('translation')->whereHas('translation')->where('category_id', 11)->latest()->limit(10)->get();

        $this->categories = Category::with(['translation', 'news'])->limit(3)->get();
        
        return view('livewire.home');
    }
}
