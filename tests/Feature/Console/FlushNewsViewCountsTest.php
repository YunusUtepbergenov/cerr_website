<?php

use App\Models\News;
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
});
