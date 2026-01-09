<?php

use App\Livewire\Videos\VideoShow;
use App\Models\Video;
use Livewire\Livewire;

describe('VideoShow Component', function () {
    it('displays video by id', function () {
        setAppLocale('uz');
        $video = Video::factory()->create();

        Livewire::test(VideoShow::class, ['id' => $video->id])
            ->assertSet('video', fn ($val) => $val->id === $video->id)
            ->assertStatus(200);
    })->group('feature', 'livewire');

    it('throws 404 when video not found', function () {
        $this->get(route('videos.show', ['id' => 999]))
            ->assertNotFound();
    })->group('feature', 'livewire');

    it('loads popular news', function () {
        setAppLocale('uz');
        $video = Video::factory()->create();
        createNewsWithTranslation(['view_count' => 100]);

        Livewire::test(VideoShow::class, ['id' => $video->id])
            ->assertSet('popular_news', fn ($val) => $val->count() > 0);
    })->group('feature', 'livewire');

    it('limits popular news to 5 items', function () {
        setAppLocale('uz');
        $video = Video::factory()->create();

        for ($i = 0; $i < 10; $i++) {
            createNewsWithTranslation(['view_count' => 100 - $i]);
        }

        Livewire::test(VideoShow::class, ['id' => $video->id])
            ->assertSet('popular_news', fn ($val) => $val->count() === 5);
    })->group('feature', 'livewire');
});
