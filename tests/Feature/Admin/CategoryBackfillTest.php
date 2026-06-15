<?php

use App\Models\Category;
use App\Models\CategoryTranslation;

/**
 * Regression coverage for the category-name backfill migration
 * (2026_06_15_170949_backfill_untranslated_category_names).
 *
 * Several seeded categories shipped with a slug but no category_translations
 * rows, so the news index and public site fell back to displaying the raw
 * slug. The migration fills a name for every site locale.
 */
beforeEach(function () {
    $this->migration = require database_path('migrations/2026_06_15_170949_backfill_untranslated_category_names.php');
});

it('fills a name in every locale for an untranslated seeded category', function () {
    $category = Category::factory()->create(['slug' => 'mulohaza']);
    expect($category->translations()->count())->toBe(0);

    $this->migration->up();

    $names = $category->translations()->pluck('name', 'language');
    expect($names)->toHaveCount(4)
        ->and($names['uz'])->toBe('Mulohaza')
        ->and($names['kr'])->toBe('Мулоҳаза')
        ->and($names['ru'])->toBe('Мнения')
        ->and($names['en'])->toBe('Commentary');
})->group('feature', 'admin');

it('is idempotent and does not duplicate translations on re-run', function () {
    $category = Category::factory()->create(['slug' => 'bozorlar']);

    $this->migration->up();
    $this->migration->up();

    expect($category->translations()->count())->toBe(4)
        ->and(CategoryTranslation::where('category_id', $category->id)->where('language', 'ru')->count())->toBe(1);
})->group('feature', 'admin');

it('never overwrites a name an editor already supplied', function () {
    $category = Category::factory()->create(['slug' => 'trendlar']);
    $category->translations()->create(['language' => 'ru', 'name' => 'Особое имя']);

    $this->migration->up();

    expect($category->translations()->where('language', 'ru')->value('name'))->toBe('Особое имя')
        ->and($category->translations()->count())->toBe(4);
})->group('feature', 'admin');

it('ignores slugs that are not in the backfill list', function () {
    $category = Category::factory()->create(['slug' => 'something-else']);

    $this->migration->up();

    expect($category->translations()->count())->toBe(0);
})->group('feature', 'admin');
