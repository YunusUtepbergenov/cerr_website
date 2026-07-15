<?php

namespace App\Livewire;

use App\Models\Category;
use App\Models\News;
use Livewire\Component;

class ShowCategory extends Component
{
    public const PER_PAGE_STEP = 12;

    public const PER_PAGE_MAX = 200;

    public $category;

    public $popular_news;

    public $perPage = self::PER_PAGE_STEP;

    public function mount($slug)
    {
        $this->category = Category::with('translation')->where('slug', $slug)->first();

        if (! $this->category || ! $this->category->translation) {
            abort(404);
        }

        $locale = app()->getLocale();
        $this->popular_news = News::published()->whereHas('translations', fn ($query) => $query->where('lang', $locale))->with(['translation' => fn ($query) => $query->cardColumns()])->orderBy('view_count', 'DESC')->limit(6)->get();
    }

    public function loadMore(): void
    {
        $this->perPage = min((int) $this->perPage + self::PER_PAGE_STEP, self::PER_PAGE_MAX);
    }

    public function render()
    {
        $perPage = max(1, min((int) $this->perPage, self::PER_PAGE_MAX));

        return view('livewire.show-category', [
            'news' => $this->category->news()->with(['translation' => fn ($query) => $query->cardColumns()])->take($perPage)->get(),
            'totalCount' => $this->category->news()->count(),
        ]);
    }
}
