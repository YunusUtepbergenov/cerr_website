<?php

namespace App\Livewire;

use App\Models\News;
use Illuminate\Support\Facades\Redis;
use Livewire\Component;

class ShowNews extends Component
{
    public $news;

    public $popular_news;

    public function mount($slug): void
    {
        $this->news = News::published()->with('translation')->where('slug', $slug)->first();

        if (! $this->news || ! $this->news->translation) {
            abort(404);
        }

        $this->trackView();

        $locale = app()->getLocale();
        $this->popular_news = News::published()->whereHas('translations', fn ($query) => $query->where('lang', $locale))->orderBy('view_count', 'DESC')->limit(6)->get();
    }

    private function trackView(): void
    {
        $cacheKey = 'news_view:'.session()->getId().':'.$this->news->id;

        if (! cache()->has($cacheKey)) {
            cache()->put($cacheKey, true, now()->addHours(24));
            Redis::hincrby('news:views', $this->news->id, 1);
        }
    }

    public function render()
    {
        return view('livewire.show-news');
    }
}
