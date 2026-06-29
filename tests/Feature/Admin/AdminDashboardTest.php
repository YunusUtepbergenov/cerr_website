<?php

use App\Livewire\Admin\Dashboard;
use App\Models\News;
use App\Models\NewsDailyView;
use App\Models\User;
use Livewire\Livewire;

describe('Admin dashboard', function () {
    beforeEach(function () {
        app()->setLocale('ru');
        $this->admin = User::factory()->create(['role' => 'admin']);
    });

    it('shows view analytics and drops the category/tag counters and activity feed', function () {
        createNewsWithTranslation(['status' => 'published']);

        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertOk()
            ->assertSee(__('admin.dashboard.total_views'))
            ->assertSee(__('admin.dashboard.views_over_time'))
            ->assertSee(__('admin.dashboard.top_viewed'))
            // The "last action" activity feed is gone.
            ->assertDontSee(__('admin.dashboard.recent_activity_sub'))
            // Recent news still renders translated status pills, not raw enums.
            ->assertSee('status-published', false)
            ->assertDontSee('>published<', false);
    })->group('feature', 'admin');

    it('ranks the most viewed news for the selected period', function () {
        $quiet = createNewsWithTranslation();
        $popular = createNewsWithTranslation();

        NewsDailyView::create(['news_id' => $quiet->id, 'date' => today()->toDateString(), 'views' => 50]);
        NewsDailyView::create(['news_id' => $popular->id, 'date' => today()->toDateString(), 'views' => 200]);

        $this->actingAs($this->admin);

        Livewire::test(Dashboard::class)
            ->assertSet('topPeriod', 'week')
            ->assertViewHas('topNews', fn ($top) => $top->first()['news']->id === $popular->id
                && $top->first()['views'] === 200);
    })->group('feature', 'admin');

    it('changes the ranking window when the period is switched', function () {
        $recent = createNewsWithTranslation();
        $older = createNewsWithTranslation();

        NewsDailyView::create(['news_id' => $recent->id, 'date' => today()->toDateString(), 'views' => 10]);
        NewsDailyView::create(['news_id' => $older->id, 'date' => today()->subDays(100)->toDateString(), 'views' => 999]);

        $this->actingAs($this->admin);

        Livewire::test(Dashboard::class)
            ->call('setPeriod', 'week')
            ->assertViewHas('topNews', fn ($top) => $top->count() === 1 && $top->first()['news']->id === $recent->id)
            ->call('setPeriod', 'year')
            ->assertViewHas('topNews', fn ($top) => $top->first()['news']->id === $older->id);
    })->group('feature', 'admin');

    it('computes a rising site-wide 7-day trend', function () {
        $news = createNewsWithTranslation();

        NewsDailyView::create(['news_id' => $news->id, 'date' => today()->subDays(10)->toDateString(), 'views' => 10]);
        NewsDailyView::create(['news_id' => $news->id, 'date' => today()->subDays(2)->toDateString(), 'views' => 30]);

        $this->actingAs($this->admin);

        Livewire::test(Dashboard::class)
            ->assertViewHas('trend7d', fn ($t) => $t['current'] === 30 && $t['previous'] === 10 && $t['change'] === 200);
    })->group('feature', 'admin');

    it('uses the lifetime counter for the all-time ranking', function () {
        $news = createNewsWithTranslation();
        $news->update(['view_count' => 777]);

        $this->actingAs($this->admin);

        Livewire::test(Dashboard::class)
            ->call('setPeriod', 'all')
            ->assertViewHas('topNews', fn ($top) => $top->first()['news']->id === $news->id
                && $top->first()['views'] === 777
                && $top->first()['change'] === null);
    })->group('feature', 'admin');

    it('falls back to created_at when updated_at is missing', function () {
        $news = createNewsWithTranslation();
        $news->timestamps = false;
        $news->forceFill(['updated_at' => null, 'created_at' => now()->subHours(3)])->saveQuietly();

        $this->actingAs($this->admin)->get('/admin')
            ->assertOk()
            ->assertSee(now()->subHours(3)->diffForHumans());
    })->group('feature', 'admin');

    it('computes a declining site-wide 7-day trend', function () {
        $news = createNewsWithTranslation();
        NewsDailyView::create(['news_id' => $news->id, 'date' => today()->subDays(10)->toDateString(), 'views' => 100]);
        NewsDailyView::create(['news_id' => $news->id, 'date' => today()->subDays(2)->toDateString(), 'views' => 50]);

        $this->actingAs($this->admin);

        Livewire::test(Dashboard::class)
            ->assertViewHas('trend7d', fn ($t) => $t['current'] === 50 && $t['previous'] === 100 && $t['change'] === -50);
    })->group('feature', 'admin');

    it('marks an article with no prior baseline as new and renders the new badge', function () {
        $news = createNewsWithTranslation();
        NewsDailyView::create(['news_id' => $news->id, 'date' => today()->toDateString(), 'views' => 25]);

        $this->actingAs($this->admin);

        Livewire::test(Dashboard::class)
            ->call('setPeriod', 'week')
            ->assertViewHas('topNews', fn ($top) => $top->first()['news']->id === $news->id && $top->first()['change'] === null)
            ->assertSee('trend-new', false);
    })->group('feature', 'admin');

    it('uses a today-vs-yesterday window for the day period and the views-today stat', function () {
        $news = createNewsWithTranslation();
        NewsDailyView::create(['news_id' => $news->id, 'date' => today()->toDateString(), 'views' => 8]);
        NewsDailyView::create(['news_id' => $news->id, 'date' => today()->subDay()->toDateString(), 'views' => 99]);

        $this->actingAs($this->admin);

        Livewire::test(Dashboard::class)
            ->assertViewHas('viewsToday', 8)
            ->call('setPeriod', 'day')
            ->assertViewHas('topNews', fn ($top) => $top->count() === 1 && $top->first()['views'] === 8);
    })->group('feature', 'admin');

    it('separates the week and month windows correctly', function () {
        $news = createNewsWithTranslation();
        NewsDailyView::create(['news_id' => $news->id, 'date' => today()->subDays(20)->toDateString(), 'views' => 70]);

        $this->actingAs($this->admin);

        Livewire::test(Dashboard::class)
            ->call('setPeriod', 'week')
            ->assertViewHas('topNews', fn ($top) => $top->isEmpty())
            ->call('setPeriod', 'month')
            ->assertViewHas('topNews', fn ($top) => $top->count() === 1 && $top->first()['views'] === 70);
    })->group('feature', 'admin');

    it('ranks multiple articles in descending order of period views', function () {
        $a = createNewsWithTranslation();
        $b = createNewsWithTranslation();
        $c = createNewsWithTranslation();
        NewsDailyView::create(['news_id' => $a->id, 'date' => today()->toDateString(), 'views' => 30]);
        NewsDailyView::create(['news_id' => $b->id, 'date' => today()->toDateString(), 'views' => 90]);
        NewsDailyView::create(['news_id' => $c->id, 'date' => today()->toDateString(), 'views' => 60]);

        $this->actingAs($this->admin);

        Livewire::test(Dashboard::class)
            ->call('setPeriod', 'week')
            ->assertViewHas('topNews', fn ($top) => $top->pluck('news.id')->all() === [$b->id, $c->id, $a->id]
                && $top->pluck('views')->all() === [90, 60, 30]);
    })->group('feature', 'admin');

    it('handles periods with no view data', function () {
        createNewsWithTranslation();

        $this->actingAs($this->admin);

        Livewire::test(Dashboard::class)
            ->assertViewHas('trend7d', fn ($t) => $t['current'] === 0 && $t['previous'] === 0 && $t['change'] === 0)
            ->assertViewHas('topNews', fn ($top) => $top->isEmpty())
            ->assertViewHas('sparkline', fn ($s) => count($s) === 30 && array_sum($s) === 0);
    })->group('feature', 'admin');

    it('ignores an invalid period and keeps the current one', function () {
        $this->actingAs($this->admin);

        Livewire::test(Dashboard::class)
            ->assertSet('topPeriod', 'week')
            ->call('setPeriod', 'nonsense')
            ->assertSet('topPeriod', 'week');
    })->group('feature', 'admin');

    it('cascades the delete of an article to its daily view rows', function () {
        $news = News::factory()->create();
        NewsDailyView::create(['news_id' => $news->id, 'date' => today()->toDateString(), 'views' => 5]);

        $news->delete();

        expect(NewsDailyView::where('news_id', $news->id)->count())->toBe(0);
    })->group('feature', 'admin');
});
