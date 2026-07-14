<?php

use App\Livewire\ShowAllCategories;
use App\Livewire\ShowCategory;
use App\Models\News;
use Illuminate\Support\Facades\DB;
use Livewire\Livewire;

describe('News::isPubliclyVisible', function () {
    it('is true for published news', function () {
        $news = News::factory()->create(['status' => 'published']);

        expect($news->isPubliclyVisible())->toBeTrue();
    })->group('feature');

    it('is false for draft news', function () {
        $news = News::factory()->create(['status' => 'draft']);

        expect($news->isPubliclyVisible())->toBeFalse();
    })->group('feature');

    it('is true for auto_publish news whose scheduled_at has passed', function () {
        $news = News::factory()->create([
            'status' => 'auto_publish',
            'scheduled_at' => now()->subHour(),
        ]);

        expect($news->isPubliclyVisible())->toBeTrue();
    })->group('feature');

    it('is false for auto_publish news scheduled in the future', function () {
        $news = News::factory()->create([
            'status' => 'auto_publish',
            'scheduled_at' => now()->addHour(),
        ]);

        expect($news->isPubliclyVisible())->toBeFalse();
    })->group('feature');

    it('is false for auto_publish news with no scheduled_at', function () {
        $news = News::factory()->create([
            'status' => 'auto_publish',
            'scheduled_at' => null,
        ]);

        expect($news->isPubliclyVisible())->toBeFalse();
    })->group('feature');

    it('matches the published() scope for the same rows', function () {
        News::factory()->create(['status' => 'published']);
        News::factory()->create(['status' => 'draft']);
        News::factory()->create(['status' => 'auto_publish', 'scheduled_at' => now()->subHour()]);
        News::factory()->create(['status' => 'auto_publish', 'scheduled_at' => now()->addHour()]);

        $scopeVisibleIds = News::published()->pluck('id')->sort()->values();
        $inMemoryVisibleIds = News::all()->filter->isPubliclyVisible()->pluck('id')->sort()->values();

        expect($inMemoryVisibleIds->all())->toBe($scopeVisibleIds->all());
    })->group('feature');
});

describe('cardColumns eager load', function () {
    it('omits the article body from card translations but keeps title and cover', function () {
        setAppLocale('uz');
        $news = createNewsWithTranslation([], [
            'title' => 'Card title',
            'short_description' => 'Card standfirst',
            'content' => '<p>The full body that cards never render.</p>',
            'image_url' => 'cover.jpg',
        ]);

        $loaded = News::with(['translation' => fn ($q) => $q->cardColumns()])->find($news->id);
        $translation = $loaded->translation;

        expect($translation->title)->toBe('Card title')
            ->and($translation->short_description)->toBe('Card standfirst')
            ->and($translation->image_url)->toBe('cover.jpg')
            ->and($translation->content)->toBeNull()
            ->and($translation->seo_title)->toBeNull();
    })->group('feature');
});

describe('list pages avoid N+1 translation loads', function () {
    it('renders ShowAllCategories with a bounded query count regardless of item volume', function () {
        setAppLocale('uz');
        $category = createCategoryWithTranslation();

        // Category news (rendered via $category->news) and a separate popular pool.
        for ($i = 0; $i < 10; $i++) {
            createNewsWithTranslation(['category_id' => $category->id]);
        }
        for ($i = 0; $i < 10; $i++) {
            createNewsWithTranslation(['view_count' => 100 + $i]);
        }

        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        Livewire::test(ShowAllCategories::class)->assertStatus(200);

        // With the eager loads in place this is a small constant (~6-8). Without
        // them each of the ~10 category items and ~6 popular items lazy-loads its
        // translation, pushing the count past 20. A ceiling well below that pins
        // the fix without being brittle about the exact constant.
        expect($queryCount)->toBeLessThan(15);
    })->group('feature');

    it('renders ShowCategory with a bounded query count regardless of item volume', function () {
        setAppLocale('uz');
        $category = createCategoryWithTranslation(['slug' => 'perf-cat']);

        for ($i = 0; $i < 10; $i++) {
            createNewsWithTranslation(['category_id' => $category->id]);
        }
        for ($i = 0; $i < 10; $i++) {
            createNewsWithTranslation(['view_count' => 100 + $i]);
        }

        $queryCount = 0;
        DB::listen(function () use (&$queryCount) {
            $queryCount++;
        });

        Livewire::test(ShowCategory::class, ['slug' => 'perf-cat'])->assertStatus(200);

        expect($queryCount)->toBeLessThan(15);
    })->group('feature');
});
