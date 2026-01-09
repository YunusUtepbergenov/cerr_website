<?php

use App\Livewire\Home;
use App\Models\Video;
use Livewire\Livewire;

describe('Home Component', function () {
    beforeEach(function () {
        setAppLocale('uz');
    });

    it('can render home page', function () {
        Livewire::test(Home::class)
            ->assertStatus(200);
    })->group('feature', 'livewire', 'critical');

    it('loads research news when category exists', function () {
        $research = createCategoryWithTranslation(['slug' => 'research']);
        $news = createNewsWithTranslation(['category_id' => $research->id]);

        Livewire::test(Home::class)
            ->assertSet('latest_news', fn ($val) => $val->count() === 1);
    })->group('feature', 'livewire', 'critical');

    it('loads events news when category exists', function () {
        $events = createCategoryWithTranslation(['slug' => 'events']);
        $news = createNewsWithTranslation(['category_id' => $events->id]);

        Livewire::test(Home::class)
            ->assertSet('events', fn ($val) => $val->count() === 1);
    })->group('feature', 'livewire', 'critical');

    it('loads infographics when category exists', function () {
        $infographics = createCategoryWithTranslation(['slug' => 'infografikalar']);
        $news = createNewsWithTranslation(['category_id' => $infographics->id]);

        Livewire::test(Home::class)
            ->assertSet('infographics', fn ($val) => $val->count() === 1);
    })->group('feature', 'livewire', 'critical');

    it('returns empty collection when category not found', function () {
        Livewire::test(Home::class)
            ->assertSet('latest_news', fn ($val) => $val->isEmpty())
            ->assertSet('events', fn ($val) => $val->isEmpty())
            ->assertSet('infographics', fn ($val) => $val->isEmpty());
    })->group('feature', 'livewire', 'critical');

    it('loads 4 latest videos', function () {
        Video::factory()->count(6)->create();

        Livewire::test(Home::class)
            ->assertSet('videos', fn ($val) => $val->count() === 4);
    })->group('feature', 'livewire', 'critical');

    it('limits news to 10 per category', function () {
        $research = createCategoryWithTranslation(['slug' => 'research']);

        for ($i = 0; $i < 15; $i++) {
            createNewsWithTranslation(['category_id' => $research->id]);
        }

        Livewire::test(Home::class)
            ->assertSet('latest_news', fn ($val) => $val->count() === 10);
    })->group('feature', 'livewire', 'critical');

    it('loads categories with relationships', function () {
        $category = createCategoryWithTranslation();
        createNewsWithTranslation(['category_id' => $category->id]);

        Livewire::test(Home::class)
            ->assertSet('categories', fn ($val) => $val->count() === 1)
            ->assertSet('categories', fn ($val) => $val->first()->news->count() === 1);
    })->group('feature', 'livewire', 'critical');

    it('only shows news with translations', function () {
        $research = createCategoryWithTranslation(['slug' => 'research']);
        $newsWithTranslation = createNewsWithTranslation(['category_id' => $research->id]);
        $newsWithoutTranslation = \App\Models\News::factory()->create(['category_id' => $research->id]);

        Livewire::test(Home::class)
            ->assertSet('latest_news', fn ($val) => $val->count() === 1)
            ->assertSet('latest_news', fn ($val) => $val->first()->id === $newsWithTranslation->id);
    })->group('feature', 'livewire', 'critical');
});
