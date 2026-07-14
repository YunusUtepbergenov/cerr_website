<?php

namespace App\Livewire;

use App\Models\News;
use Livewire\Attributes\Url;
use Livewire\Component;

class Search extends Component
{
    public const MIN_QUERY_LENGTH = 2;

    public const PER_PAGE_STEP = 12;

    public const PER_PAGE_MAX = 200;

    #[Url(as: 'q')]
    public $q = '';

    public $perPage = self::PER_PAGE_STEP;

    public function updatedQ(): void
    {
        $this->perPage = self::PER_PAGE_STEP;
    }

    public function loadMore(): void
    {
        $this->perPage = min((int) $this->perPage + self::PER_PAGE_STEP, self::PER_PAGE_MAX);
    }

    public function render()
    {
        // #[Url] can hydrate $q as an array (?q[]=x) — treat anything non-string as empty.
        $term = is_string($this->q) ? trim($this->q) : '';
        $results = collect();
        $totalCount = 0;

        if (mb_strlen($term) >= self::MIN_QUERY_LENGTH) {
            $perPage = max(1, min((int) $this->perPage, self::PER_PAGE_MAX));
            $query = $this->searchQuery($term);

            $totalCount = (clone $query)->count();
            $results = $query->with(['translation' => fn ($q) => $q->cardColumns()])->latest()->take($perPage)->get();
        }

        return view('livewire.search', [
            'results' => $results,
            'totalCount' => $totalCount,
            'term' => $term,
        ]);
    }

    /**
     * Published news whose translation in the current locale matches the term
     * in the title, summary or body. LIKE wildcards in user input are escaped.
     */
    protected function searchQuery(string $term)
    {
        $locale = app()->getLocale();
        $like = '%'.addcslashes($term, '%_\\').'%';

        return News::published()->whereHas('translations', function ($query) use ($locale, $like) {
            $query->where('lang', $locale)
                ->where(function ($q) use ($like) {
                    $q->where('title', 'LIKE', $like)
                        ->orWhere('short_description', 'LIKE', $like)
                        ->orWhere('content', 'LIKE', $like);
                });
        });
    }
}
