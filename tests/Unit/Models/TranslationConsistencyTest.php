<?php

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\News;
use App\Models\NewsTranslation;
use App\Models\Page;
use App\Models\PageTranslation;

describe('Translation Models Consistency', function () {
    it('NewsTranslation uses lang field', function () {
        $news = News::factory()->create();
        $translation = NewsTranslation::factory()->create([
            'news_id' => $news->id,
            'lang' => 'uz',
        ]);

        expect($translation->lang)->toBe('uz');
    })->group('unit', 'models', 'consistency-issues');

    it('CategoryTranslation uses language field', function () {
        $category = Category::factory()->create();
        $translation = CategoryTranslation::factory()->create([
            'category_id' => $category->id,
            'language' => 'uz',
        ]);

        expect($translation->language)->toBe('uz');
    })->group('unit', 'models', 'consistency-issues');

    it('PageTranslation uses language field', function () {
        $page = Page::factory()->create();
        $translation = PageTranslation::factory()->create([
            'page_id' => $page->id,
            'language' => 'uz',
        ]);

        expect($translation->language)->toBe('uz');
    })->group('unit', 'models', 'consistency-issues');

    it('all translation models support uz locale', function () {
        $news = News::factory()->create();
        $category = Category::factory()->create();
        $page = Page::factory()->create();

        NewsTranslation::factory()->create(['news_id' => $news->id, 'lang' => 'uz']);
        CategoryTranslation::factory()->create(['category_id' => $category->id, 'language' => 'uz']);
        PageTranslation::factory()->create(['page_id' => $page->id, 'language' => 'uz']);

        expect(NewsTranslation::where('lang', 'uz')->count())->toBe(1)
            ->and(CategoryTranslation::where('language', 'uz')->count())->toBe(1)
            ->and(PageTranslation::where('language', 'uz')->count())->toBe(1);
    })->group('unit', 'models', 'consistency-issues');
});
