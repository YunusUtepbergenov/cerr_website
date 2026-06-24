<?php

use App\Livewire\Home;
use App\Models\News;
use Livewire\Livewire;

/**
 * Regression coverage for the legacy data backfill migration
 * (2026_06_04_122720_backfill_legacy_auto_publish_news).
 *
 * Legacy news rows were imported with status='auto_publish' and a NULL
 * scheduled_at. After News::scopePublished() began enforcing visibility,
 * those rows matched nothing (NULL fails the scheduled_at <= now() check),
 * so the public homepage rendered empty. The migration promotes exactly
 * that orphaned state to 'published'.
 */
beforeEach(function () {
    setAppLocale('uz');
    $this->researchCategory = createCategoryWithTranslation(['slug' => 'research']);
    $this->migration = require database_path('migrations/2026_06_04_122720_backfill_legacy_auto_publish_news.php');
});

it('reproduces the regression: legacy auto_publish news with no schedule is hidden', function () {
    createNewsWithTranslation([
        'category_id' => $this->researchCategory->id,
        'status' => 'auto_publish',
        'scheduled_at' => null,
    ]);

    expect(News::published()->count())->toBe(0);

    Livewire::test(Home::class)
        ->assertSet('latest_news', fn ($val) => $val->isEmpty());
})->group('feature', 'public');

it('backfills legacy auto_publish news to published and restores the homepage', function () {
    $news = createNewsWithTranslation([
        'category_id' => $this->researchCategory->id,
        'status' => 'auto_publish',
        'scheduled_at' => null,
    ]);

    $this->migration->up();

    expect($news->fresh()->status)->toBe('published');
    expect(News::published()->count())->toBe(1);

    Livewire::test(Home::class)
        ->assertSet('latest_news', fn ($val) => $val->count() === 1);
})->group('feature', 'public');

it('does not touch auto_publish news that has a scheduled_at', function () {
    $scheduled = createNewsWithTranslation([
        'category_id' => $this->researchCategory->id,
        'status' => 'auto_publish',
        'scheduled_at' => now()->addDay(),
    ]);

    $this->migration->up();

    expect($scheduled->fresh()->status)->toBe('auto_publish');
})->group('feature', 'public');

it('leaves drafts untouched', function () {
    $draft = createNewsWithTranslation([
        'category_id' => $this->researchCategory->id,
        'status' => 'draft',
        'scheduled_at' => null,
    ]);

    $this->migration->up();

    expect($draft->fresh()->status)->toBe('draft');
})->group('feature', 'public');
