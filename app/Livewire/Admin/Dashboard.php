<?php

namespace App\Livewire\Admin;

use App\Models\News;
use App\Models\NewsDailyView;
use Illuminate\Support\Collection;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Dashboard extends Component
{
    /** @var array<string, int> */
    private const PERIOD_DAYS = ['day' => 1, 'week' => 7, 'month' => 30, 'year' => 365];

    private const TOP_LIMIT = 5;

    public string $topPeriod = 'week';

    public function setPeriod(string $period): void
    {
        if ($period === 'all' || array_key_exists($period, self::PERIOD_DAYS)) {
            $this->topPeriod = $period;
        }
    }

    public function render()
    {
        $newsCount = News::count();
        $publishedCount = News::where('status', 'published')->count();

        return view('livewire.admin.dashboard', [
            'newsCount' => $newsCount,
            'publishedCount' => $publishedCount,
            'draftCount' => News::where('status', 'draft')->count(),
            'publicationRate' => $newsCount > 0 ? (int) round($publishedCount / $newsCount * 100) : 0,
            'totalViews' => (int) News::sum('view_count'),
            'viewsToday' => (int) NewsDailyView::where('date', today()->toDateString())->sum('views'),
            'trend7d' => $this->viewTrend(7),
            'trend30d' => $this->viewTrend(30),
            'sparkline' => $this->dailyTotals(30),
            'topNews' => $this->topViewed($this->topPeriod),
            'recentNews' => News::with(['translations' => fn ($query) => $query->cardColumns()])->latest('id')->limit(8)->get(),
        ])->title(__('admin.nav.dashboard'));
    }

    /**
     * Total site-wide views per day for the last N days (oldest first),
     * used to render the trend sparkline.
     *
     * @return array<int, int>
     */
    private function dailyTotals(int $days): array
    {
        $start = today()->subDays($days - 1);

        $totals = NewsDailyView::where('date', '>=', $start->toDateString())
            ->groupBy('date')
            ->selectRaw('date, SUM(views) as total')
            ->pluck('total', 'date');

        $series = [];
        for ($i = 0; $i < $days; $i++) {
            $key = $start->copy()->addDays($i)->toDateString();
            $series[] = (int) ($totals[$key] ?? 0);
        }

        return $series;
    }

    /**
     * Site-wide views in the current window vs the previous equal window.
     *
     * @return array{current: int, previous: int, change: ?int}
     */
    private function viewTrend(int $days): array
    {
        $w = $this->windows($days);

        $current = (int) NewsDailyView::whereBetween('date', [$w['currentStart'], $w['currentEnd']])->sum('views');
        $previous = (int) NewsDailyView::whereBetween('date', [$w['prevStart'], $w['prevEnd']])->sum('views');

        return [
            'current' => $current,
            'previous' => $previous,
            'change' => $this->percentChange($current, $previous),
        ];
    }

    /**
     * Top viewed articles for the given period, each with its percentage change
     * versus the previous equal window. The 'all' period uses the lifetime
     * cumulative counter and carries no change.
     */
    private function topViewed(string $period): Collection
    {
        if ($period === 'all') {
            return News::with('translations')
                ->orderByDesc('view_count')
                ->limit(self::TOP_LIMIT)
                ->get()
                ->map(fn (News $news): array => [
                    'news' => $news,
                    'views' => (int) $news->view_count,
                    'change' => null,
                ]);
        }

        $w = $this->windows(self::PERIOD_DAYS[$period] ?? 7);

        $current = NewsDailyView::whereBetween('date', [$w['currentStart'], $w['currentEnd']])
            ->groupBy('news_id')
            ->selectRaw('news_id, SUM(views) as period_views')
            ->orderByDesc('period_views')
            ->limit(self::TOP_LIMIT)
            ->pluck('period_views', 'news_id');

        if ($current->isEmpty()) {
            return collect();
        }

        $previous = NewsDailyView::whereBetween('date', [$w['prevStart'], $w['prevEnd']])
            ->whereIn('news_id', $current->keys())
            ->groupBy('news_id')
            ->selectRaw('news_id, SUM(views) as period_views')
            ->pluck('period_views', 'news_id');

        $newsById = News::with('translations')->whereIn('id', $current->keys())->get()->keyBy('id');

        return $current->map(fn ($views, $newsId): array => [
            'news' => $newsById->get($newsId),
            'views' => (int) $views,
            'change' => $this->percentChange((int) $views, (int) ($previous[$newsId] ?? 0)),
        ])->filter(fn (array $row): bool => $row['news'] !== null)->values();
    }

    /**
     * Inclusive date-string bounds for the current and previous windows of the
     * given length, ending today.
     *
     * @return array{currentStart: string, currentEnd: string, prevStart: string, prevEnd: string}
     */
    private function windows(int $days): array
    {
        $today = today();

        return [
            'currentStart' => $today->copy()->subDays($days - 1)->toDateString(),
            'currentEnd' => $today->toDateString(),
            'prevStart' => $today->copy()->subDays($days * 2 - 1)->toDateString(),
            'prevEnd' => $today->copy()->subDays($days)->toDateString(),
        ];
    }

    /**
     * Percentage change between two totals. Returns null when there is no prior
     * baseline but current activity exists ("new"), and 0 when both are zero.
     */
    private function percentChange(int $current, int $previous): ?int
    {
        if ($previous === 0) {
            return $current > 0 ? null : 0;
        }

        return (int) round(($current - $previous) / $previous * 100);
    }
}
