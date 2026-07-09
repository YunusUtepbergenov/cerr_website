<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\Journal;
use App\Models\News;
use App\Models\Video;
use Livewire\Component;

class Home extends Component
{
    public $latest_news;

    public $events;

    public $infographics;

    public $videos;

    public $journals;

    public $popular_news;

    public $main;

    public $categories;

    public function mount()
    {
        // Get category IDs by slug to avoid hard-coding
        $researchCategory = Category::where('slug', 'research')->first();
        $eventsCategory = Category::where('slug', 'events')->first();
        $infographicsCategory = Category::where('slug', 'infografikalar')->first();

        $this->latest_news = $researchCategory
            ? News::published()->with('translation')->whereHas('translation')->where('category_id', $researchCategory->id)->latest()->limit(10)->get()
            : collect();

        $this->events = $eventsCategory
            ? News::published()->with('translation')->whereHas('translation')->where('category_id', $eventsCategory->id)->latest()->limit(10)->get()
            : collect();

        $this->infographics = $infographicsCategory
            ? News::published()->with('translation')->whereHas('translation')->where('category_id', $infographicsCategory->id)->latest()->limit(10)->get()
            : collect();

        $this->videos = Video::latest()->take(4)->get();

        $this->journals = Journal::active()->orderByDesc('published_at')->orderByDesc('id')->take(8)->get();

        $this->categories = Category::with(['translation', 'news'])->limit(3)->get();
    }

    public function render()
    {
        return view('livewire.home');
    }
}
