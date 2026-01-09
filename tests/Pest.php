<?php

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "pest()" function to bind a different classes or traits.
|
*/

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Feature');

pest()->extend(Tests\TestCase::class)
    ->use(Illuminate\Foundation\Testing\RefreshDatabase::class)
    ->in('Unit');

/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/

expect()->extend('toBeOne', function () {
    return $this->toBe(1);
});

/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

/*
|--------------------------------------------------------------------------
| Test Helpers
|--------------------------------------------------------------------------
*/

// Helper to set application locale in tests
function setAppLocale(string $locale = 'uz'): void
{
    session(['locale' => $locale]);
    app()->setLocale($locale);
}

// Helper to create news with translations
function createNewsWithTranslation(array $newsAttrs = [], array $translationAttrs = []): \App\Models\News
{
    $news = \App\Models\News::factory()->create($newsAttrs);
    $news->translations()->create(array_merge([
        'lang' => app()->getLocale(),
        'title' => fake()->sentence(),
        'short_description' => fake()->paragraph(),
        'content' => fake()->text(800),
        'image_url' => '1.jpg',
    ], $translationAttrs));

    return $news->fresh(['translation']);
}

// Helper to create category with translation
function createCategoryWithTranslation(array $categoryAttrs = [], array $translationAttrs = []): \App\Models\Category
{
    $category = \App\Models\Category::factory()->create($categoryAttrs);
    $category->translations()->create(array_merge([
        'language' => app()->getLocale(),
        'name' => fake()->word(),
    ], $translationAttrs));

    return $category->fresh(['translation']);
}

// Helper to create page with translation
function createPageWithTranslation(array $pageAttrs = [], array $translationAttrs = []): \App\Models\Page
{
    $page = \App\Models\Page::factory()->create($pageAttrs);
    $page->translations()->create(array_merge([
        'language' => app()->getLocale(),
        'title' => fake()->sentence(),
        'content' => fake()->paragraphs(3, true),
        'image' => fake()->imageUrl(),
    ], $translationAttrs));

    return $page->fresh(['translation']);
}
