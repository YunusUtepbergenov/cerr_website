<?php

use App\Livewire\ShowNews;
use App\Models\News;
use Livewire\Livewire;

describe('ShowNews Component', function () {
    beforeEach(function () {
        setAppLocale('uz');
    });

    it('displays news by slug', function () {
        $news = createNewsWithTranslation(['slug' => 'test-news-slug']);

        Livewire::test(ShowNews::class, ['slug' => 'test-news-slug'])
            ->assertSet('news', fn ($val) => $val->id === $news->id)
            ->assertStatus(200);
    })->group('feature', 'livewire', 'critical');

    it('redirects when news not found', function () {
        $response = $this->get(route('show.news', ['slug' => 'non-existent']));

        $response->assertRedirect('/');
    })->group('feature', 'livewire', 'critical');

    it('redirects when news has no translation', function () {
        setAppLocale('uz');
        $news = News::factory()->create(['slug' => 'no-translation']);

        $response = $this->get(route('show.news', ['slug' => 'no-translation']));

        $response->assertRedirect('/');
    })->group('feature', 'livewire', 'critical');

    it('loads popular news by view count', function () {
        setAppLocale('uz');
        $news = createNewsWithTranslation(['slug' => 'main-news']);

        // Create popular news
        $popular1 = createNewsWithTranslation(['view_count' => 100]);
        $popular2 = createNewsWithTranslation(['view_count' => 50]);
        $unpopular = createNewsWithTranslation(['view_count' => 10]);

        Livewire::test(ShowNews::class, ['slug' => 'main-news'])
            ->assertSet('popular_news', function ($val) use ($popular1) {
                return $val->first()->id === $popular1->id;
            });
    })->group('feature', 'livewire', 'critical');

    it('limits popular news to 6 items', function () {
        setAppLocale('uz');
        $news = createNewsWithTranslation(['slug' => 'main-news']);

        for ($i = 0; $i < 10; $i++) {
            createNewsWithTranslation(['view_count' => 100 - $i]);
        }

        Livewire::test(ShowNews::class, ['slug' => 'main-news'])
            ->assertSet('popular_news', fn ($val) => $val->count() === 6);
    })->group('feature', 'livewire', 'critical');

    it('popular news respects current locale', function () {
        setAppLocale('uz');
        $news = createNewsWithTranslation(['slug' => 'main-news']);

        // News with UZ translation
        $uzNews = createNewsWithTranslation(['view_count' => 100], ['lang' => 'uz']);

        // News with only EN translation (should not appear)
        $enNewsOnly = News::factory()->create(['view_count' => 200]);
        \App\Models\NewsTranslation::factory()->create(['news_id' => $enNewsOnly->id, 'lang' => 'en']);

        Livewire::test(ShowNews::class, ['slug' => 'main-news'])
            ->assertSet('popular_news', fn ($val) => ! $val->contains('id', $enNewsOnly->id));
    })->group('feature', 'livewire', 'critical');

    it('popular news ordered by view count descending', function () {
        setAppLocale('uz');
        $news = createNewsWithTranslation(['slug' => 'main-news']);

        $news1 = createNewsWithTranslation(['view_count' => 50]);
        $news2 = createNewsWithTranslation(['view_count' => 100]);
        $news3 = createNewsWithTranslation(['view_count' => 75]);

        Livewire::test(ShowNews::class, ['slug' => 'main-news'])
            ->assertSet('popular_news', function ($val) use ($news2, $news1, $news) {
                return $val->count() === 4
                    && $val->first()->id === $news2->id
                    && $val->last()->id === $news->id;
            });
    })->group('feature', 'livewire', 'critical');
});
