<?php

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\News;

describe('Category Model', function () {
    beforeEach(function () {
        setAppLocale('uz');
    });

    it('has translations relationship', function () {
        $category = Category::factory()->create();
        CategoryTranslation::factory()->count(2)->create(['category_id' => $category->id]);

        expect($category->translations)->toHaveCount(2);
    })->group('unit', 'models');

    it('has translation relationship filtered by locale', function () {
        $category = Category::factory()->create();
        CategoryTranslation::factory()->create(['category_id' => $category->id, 'language' => 'uz']);
        CategoryTranslation::factory()->create(['category_id' => $category->id, 'language' => 'en']);

        expect($category->translation)->not->toBeNull()
            ->and($category->translation->language)->toBe('uz');
    })->group('unit', 'models');

    it('has news relationship', function () {
        $category = createCategoryWithTranslation();
        $news1 = createNewsWithTranslation(['category_id' => $category->id]);
        $news2 = createNewsWithTranslation(['category_id' => $category->id]);

        expect($category->news)->toHaveCount(2);
    })->group('unit', 'models');

    it('news relationship only returns news with translations', function () {
        $category = createCategoryWithTranslation();
        $newsWithTranslation = createNewsWithTranslation(['category_id' => $category->id]);
        $newsWithoutTranslation = News::factory()->create(['category_id' => $category->id]);

        expect($category->news)->toHaveCount(1)
            ->and($category->news->first()->id)->toBe($newsWithTranslation->id);
    })->group('unit', 'models');

    it('getLatestNews returns latest 3 news', function () {
        $category = createCategoryWithTranslation();

        // Create 5 news items
        for ($i = 0; $i < 5; $i++) {
            createNewsWithTranslation(['category_id' => $category->id]);
        }

        expect($category->getLatestNews()->get())->toHaveCount(3);
    })->group('unit', 'models');

    it('news relationship orders by latest', function () {
        $category = createCategoryWithTranslation();
        $older = createNewsWithTranslation([
            'category_id' => $category->id,
            'created_at' => now()->subDay(),
        ]);
        $newer = createNewsWithTranslation([
            'category_id' => $category->id,
            'created_at' => now(),
        ]);

        $categoryNews = $category->fresh(['news'])->news;
        expect($categoryNews->first()->id)->toBe($newer->id);
    })->group('unit', 'models');

    it('translation returns null when locale not found', function () {
        setAppLocale('kr');
        $category = Category::factory()->create();
        CategoryTranslation::factory()->create(['category_id' => $category->id, 'language' => 'uz']);

        expect($category->translation)->toBeNull();
    })->group('unit', 'models');
});
