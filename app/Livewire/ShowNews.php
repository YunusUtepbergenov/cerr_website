<?php

namespace App\Livewire;

use App\Models\News;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Redis;
use Livewire\Component;

class ShowNews extends Component
{
    public $news;

    public $popular_news;

    public $related_news;

    public function mount($slug): void
    {
        $news = News::with(['translation', 'category.translation', 'tags', 'user'])
            ->where('slug', $slug)
            ->first();

        if (! $news || ! $news->translation) {
            abort(404);
        }

        $isPubliclyVisible = News::published()->whereKey($news->id)->exists();

        if (! $isPubliclyVisible && ! auth()->user()?->canEditNews($news)) {
            abort(404);
        }

        $this->news = $news;

        if ($isPubliclyVisible) {
            $this->trackView();
        }

        $locale = app()->getLocale();

        $this->popular_news = News::published()
            ->whereHas('translations', fn ($query) => $query->where('lang', $locale))
            ->with('translation')
            ->orderBy('view_count', 'DESC')
            ->limit(6)
            ->get();

        $this->related_news = $this->loadRelatedNews($news, $locale);
    }

    /**
     * Related articles: published news sharing a tag with the current article,
     * topped up with same-category news when there aren't enough tag matches.
     */
    private function loadRelatedNews(News $news, string $locale): Collection
    {
        $tagIds = $news->tags->pluck('id');

        $related = News::published()
            ->whereHas('translations', fn ($query) => $query->where('lang', $locale))
            ->where('id', '!=', $news->id)
            ->when(
                $tagIds->isNotEmpty(),
                fn ($query) => $query->whereHas('tags', fn ($tag) => $tag->whereIn('tags.id', $tagIds)),
                fn ($query) => $query->whereRaw('1 = 0'),
            )
            ->with('translation')
            ->latest()
            ->limit(3)
            ->get();

        if ($related->count() < 3 && $news->category_id) {
            $exclude = $related->pluck('id')->push($news->id);

            $fallback = News::published()
                ->whereHas('translations', fn ($query) => $query->where('lang', $locale))
                ->where('category_id', $news->category_id)
                ->whereNotIn('id', $exclude)
                ->with('translation')
                ->latest()
                ->limit(3 - $related->count())
                ->get();

            $related = $related->concat($fallback);
        }

        return $related;
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
        $translation = $this->news->translation;
        $title = $translation->seo_title ?: $translation->title;

        return view('livewire.show-news')
            ->layout('components.layouts.app', [
                'title' => $title,
                'metaDescription' => $translation->seo_description ?: $translation->short_description,
                'ogTitle' => $title,
                'ogImage' => $translation->coverUrl(),
                'canonical' => route('show.news', $this->news->slug),
            ]);
    }
}
