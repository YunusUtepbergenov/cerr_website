<?php

use App\Livewire\Videos\VideoIndex;
use App\Models\Video;
use Livewire\Livewire;

describe('VideoIndex Component', function () {
    beforeEach(function () {
        setAppLocale('uz');
    });

    it('initially loads 8 videos', function () {
        Video::factory()->count(15)->create();

        Livewire::test(VideoIndex::class)
            ->assertSet('perPage', 8)
            ->assertViewHas('videos', fn ($val) => $val->count() === 8);
    })->group('feature', 'livewire', 'critical');

    it('loadMore increases perPage by 8', function () {
        Video::factory()->count(20)->create();

        Livewire::test(VideoIndex::class)
            ->assertSet('perPage', 8)
            ->call('loadMore')
            ->assertSet('perPage', 16)
            ->assertViewHas('videos', fn ($val) => $val->count() === 16);
    })->group('feature', 'livewire', 'critical');

    it('can call loadMore multiple times', function () {
        Video::factory()->count(30)->create();

        Livewire::test(VideoIndex::class)
            ->call('loadMore')
            ->call('loadMore')
            ->assertSet('perPage', 24)
            ->assertViewHas('videos', fn ($val) => $val->count() === 24);
    })->group('feature', 'livewire', 'critical');

    it('shows total count in view', function () {
        Video::factory()->count(15)->create();

        Livewire::test(VideoIndex::class)
            ->assertViewHas('totalCount', 15);
    })->group('feature', 'livewire', 'critical');

    it('loads popular news', function () {
        createNewsWithTranslation(['view_count' => 100]);

        Livewire::test(VideoIndex::class)
            ->assertViewHas('popular_news', fn ($val) => $val->count() > 0);
    })->group('feature', 'livewire', 'critical');

    it('limits popular news to 5 items', function () {
        for ($i = 0; $i < 10; $i++) {
            createNewsWithTranslation(['view_count' => 100 - $i]);
        }

        Livewire::test(VideoIndex::class)
            ->assertViewHas('popular_news', fn ($val) => $val->count() === 5);
    })->group('feature', 'livewire', 'critical');

    it('handles pagination beyond available items', function () {
        Video::factory()->count(10)->create();

        Livewire::test(VideoIndex::class)
            ->call('loadMore')
            ->call('loadMore')
            ->assertSet('perPage', 24)
            ->assertViewHas('videos', fn ($val) => $val->count() === 10);
    })->group('feature', 'livewire', 'critical');

    it('videos ordered by latest', function () {
        $older = Video::factory()->create(['created_at' => now()->subDay()]);
        $newer = Video::factory()->create(['created_at' => now()]);

        Livewire::test(VideoIndex::class)
            ->assertViewHas('videos', function ($val) use ($newer) {
                return $val->first()->id === $newer->id;
            });
    })->group('feature', 'livewire', 'critical');
});
