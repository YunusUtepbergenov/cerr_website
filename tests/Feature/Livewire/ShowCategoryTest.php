<?php

use App\Livewire\ShowCategory;
use App\Models\Category;
use App\Models\News;
use Livewire\Livewire;

describe('ShowCategory Component', function () {
    beforeEach(function () {
        setAppLocale('uz');
    });

    it('displays category by slug', function () {
        $category = createCategoryWithTranslation(['slug' => 'test-category']);

        Livewire::test(ShowCategory::class, ['slug' => 'test-category'])
            ->assertSet('category', fn ($val) => $val->id === $category->id)
            ->assertStatus(200);
    })->group('feature', 'livewire', 'critical');

    it('aborts 404 when category not found', function () {
        $this->get(route('show.category', ['slug' => 'non-existent']))
            ->assertNotFound();
    })->group('feature', 'livewire', 'critical');

    it('aborts 404 when category has no translation', function () {
        setAppLocale('uz');
        $category = Category::factory()->create(['slug' => 'no-translation']);

        $this->get(route('show.category', ['slug' => 'no-translation']))
            ->assertNotFound();
    })->group('feature', 'livewire', 'critical');

    it('loads category news', function () {
        $category = createCategoryWithTranslation(['slug' => 'test-category']);
        $news1 = createNewsWithTranslation(['category_id' => $category->id]);
        $news2 = createNewsWithTranslation(['category_id' => $category->id]);

        Livewire::test(ShowCategory::class, ['slug' => 'test-category'])
            ->assertSet('category', fn ($val) => $val->news->count() === 2);
    })->group('feature', 'livewire', 'critical');

    it('loads popular news sidebar', function () {
        setAppLocale('uz');
        $category = createCategoryWithTranslation(['slug' => 'test-category']);
        $popular = createNewsWithTranslation(['view_count' => 100]);

        Livewire::test(ShowCategory::class, ['slug' => 'test-category'])
            ->assertSet('popular_news', fn ($val) => $val->count() > 0);
    })->group('feature', 'livewire', 'critical');

    it('limits popular news to 6 items', function () {
        setAppLocale('uz');
        $category = createCategoryWithTranslation(['slug' => 'test-category']);

        for ($i = 0; $i < 10; $i++) {
            createNewsWithTranslation(['view_count' => 100 - $i]);
        }

        Livewire::test(ShowCategory::class, ['slug' => 'test-category'])
            ->assertSet('popular_news', fn ($val) => $val->count() === 6);
    })->group('feature', 'livewire', 'critical');

    it('only shows category news with translations', function () {
        $category = createCategoryWithTranslation(['slug' => 'test-category']);
        $newsWithTranslation = createNewsWithTranslation(['category_id' => $category->id]);
        $newsWithoutTranslation = News::factory()->create(['category_id' => $category->id]);

        Livewire::test(ShowCategory::class, ['slug' => 'test-category'])
            ->assertSet('category', function ($val) use ($newsWithTranslation) {
                return $val->news->count() === 1
                    && $val->news->first()->id === $newsWithTranslation->id;
            });
    })->group('feature', 'livewire', 'critical');

    it('category news ordered by latest', function () {
        $category = createCategoryWithTranslation(['slug' => 'test-category']);
        $older = createNewsWithTranslation([
            'category_id' => $category->id,
            'created_at' => now()->subDay(),
        ]);
        $newer = createNewsWithTranslation([
            'category_id' => $category->id,
            'created_at' => now(),
        ]);

        Livewire::test(ShowCategory::class, ['slug' => 'test-category'])
            ->assertSet('category', function ($val) use ($newer) {
                return $val->news->first()->id === $newer->id;
            });
    })->group('feature', 'livewire', 'critical');
});
