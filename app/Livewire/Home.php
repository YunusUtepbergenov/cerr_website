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

    public function mount()
    {
        // Resolve the three homepage sections' category ids in one round-trip.
        $categories = Category::whereIn('slug', ['research', 'events', 'infografikalar'])
            ->get()
            ->keyBy('slug');

        $this->latest_news = $this->latestForCategory($categories->get('research'));
        $this->events = $this->latestForCategory($categories->get('events'));
        $this->infographics = $this->latestForCategory($categories->get('infografikalar'));

        $this->videos = Video::latest()->take(4)->get();

        $this->journals = Journal::active()->orderByDesc('published_at')->orderByDesc('id')->take(8)->get();
    }

    /**
     * Latest published news for a homepage section. Cards render title + cover
     * only, so the translation is loaded with card columns (no article body).
     */
    private function latestForCategory(?Category $category)
    {
        if (! $category) {
            return collect();
        }

        return News::published()
            ->with(['translation' => fn ($query) => $query->cardColumns()])
            ->whereHas('translation')
            ->where('category_id', $category->id)
            ->latest()
            ->limit(10)
            ->get();
    }

    public function render()
    {
        return view('livewire.home');
    }
}
