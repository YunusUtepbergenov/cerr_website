<?php

use App\Livewire\Videos\VideoIndex;
use App\Models\Video;
use Livewire\Livewire;

describe('Load More Pagination', function () {
    it('handles pagination beyond available items', function () {
        Video::factory()->count(10)->create();

        Livewire::test(VideoIndex::class)
            ->call('loadMore')
            ->call('loadMore')
            ->assertSet('perPage', 24)
            ->assertViewHas('videos', fn ($val) => $val->count() === 10);
    })->group('integration', 'pagination');

    it('shows correct totalCount vs displayed count', function () {
        Video::factory()->count(5)->create();

        Livewire::test(VideoIndex::class)
            ->assertSet('perPage', 8)
            ->assertViewHas('videos', fn ($val) => $val->count() === 5)
            ->assertViewHas('totalCount', 5);
    })->group('integration', 'pagination');

    it('loadMore works when exactly at page boundary', function () {
        Video::factory()->count(16)->create();

        Livewire::test(VideoIndex::class)
            ->call('loadMore')
            ->assertSet('perPage', 16)
            ->assertViewHas('videos', fn ($val) => $val->count() === 16);
    })->group('integration', 'pagination');

    it('perPage increments correctly across multiple calls', function () {
        Video::factory()->count(50)->create();

        $component = Livewire::test(VideoIndex::class);

        expect($component->get('perPage'))->toBe(8);

        $component->call('loadMore');
        expect($component->get('perPage'))->toBe(16);

        $component->call('loadMore');
        expect($component->get('perPage'))->toBe(24);

        $component->call('loadMore');
        expect($component->get('perPage'))->toBe(32);
    })->group('integration', 'pagination');
});
