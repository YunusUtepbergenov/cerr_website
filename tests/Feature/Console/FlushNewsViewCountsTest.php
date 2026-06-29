<?php

use App\Models\News;
use App\Models\NewsDailyView;
use Illuminate\Support\Facades\Redis;

describe('FlushNewsViewCounts Command', function () {
    afterEach(function () {
        Redis::del('news:views');
    });

    it('flushes redis view counts to the database', function () {
        $news1 = News::factory()->create(['view_count' => 10]);
        $news2 = News::factory()->create(['view_count' => 5]);

        Redis::hset('news:views', $news1->id, 3);
        Redis::hset('news:views', $news2->id, 7);

        $this->artisan('news:flush-views')->assertSuccessful();

        expect($news1->fresh()->view_count)->toBe(13)
            ->and($news2->fresh()->view_count)->toBe(12);
    })->group('feature', 'console');

    it('clears redis counters after flushing', function () {
        $news = News::factory()->create();
        Redis::hset('news:views', $news->id, 5);

        $this->artisan('news:flush-views')->assertSuccessful();

        expect((int) Redis::hget('news:views', $news->id))->toBe(0);
    })->group('feature', 'console');

    it('does nothing when there are no pending views', function () {
        $news = News::factory()->create(['view_count' => 10]);

        $this->artisan('news:flush-views')->assertSuccessful();

        expect($news->fresh()->view_count)->toBe(10);
    })->group('feature', 'console');

    it('records flushed views into the daily totals table', function () {
        $news = News::factory()->create();
        Redis::hset('news:views', $news->id, 4);

        $this->artisan('news:flush-views')->assertSuccessful();

        $row = NewsDailyView::where('news_id', $news->id)->where('date', today()->toDateString())->first();
        expect($row)->not->toBeNull()
            ->and($row->views)->toBe(4);
    })->group('feature', 'console');

    it('accumulates daily views across multiple flushes on the same day', function () {
        $news = News::factory()->create();

        Redis::hset('news:views', $news->id, 4);
        $this->artisan('news:flush-views')->assertSuccessful();

        Redis::hset('news:views', $news->id, 6);
        $this->artisan('news:flush-views')->assertSuccessful();

        expect((int) NewsDailyView::where('news_id', $news->id)->sum('views'))->toBe(10);
    })->group('feature', 'console');

    it('records several articles in a single flush', function () {
        $a = News::factory()->create();
        $b = News::factory()->create();
        Redis::hset('news:views', $a->id, 3);
        Redis::hset('news:views', $b->id, 8);

        $this->artisan('news:flush-views')->assertSuccessful();

        $today = today()->toDateString();
        expect((int) NewsDailyView::where('news_id', $a->id)->where('date', $today)->value('views'))->toBe(3)
            ->and((int) NewsDailyView::where('news_id', $b->id)->where('date', $today)->value('views'))->toBe(8);
    })->group('feature', 'console');

    it('skips non-positive buffered counts without touching the counters', function () {
        $news = News::factory()->create(['view_count' => 10]);
        Redis::hset('news:views', $news->id, -4);

        $this->artisan('news:flush-views')->assertSuccessful();

        expect($news->fresh()->view_count)->toBe(10)
            ->and(NewsDailyView::where('news_id', $news->id)->count())->toBe(0);
    })->group('feature', 'console');
});
