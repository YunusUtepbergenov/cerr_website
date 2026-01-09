<?php

use App\Models\Category;
use App\Models\CategoryTranslation;
use App\Models\News;
use App\Models\NewsTranslation;
use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\User;

describe('News Status Validation', function () {
    it('accepts valid status values', function (string $status) {
        $news = News::factory()->create(['status' => $status]);

        expect($news->status)->toBe($status);
    })->with(['draft', 'published', 'auto_publish', 'disabled'])
        ->group('unit', 'validation');

    it('rejects invalid status values', function () {
        expect(fn () => News::factory()->create(['status' => 'invalid']))
            ->toThrow(\Exception::class);
    })->group('unit', 'validation');

    it('has draft as one of valid statuses', function () {
        $news = News::factory()->create(['status' => 'draft']);
        expect($news->exists)->toBeTrue();
    })->group('unit', 'validation');
});

describe('User Role Validation', function () {
    it('accepts valid role values', function (string $role) {
        $user = User::factory()->create(['role' => $role]);

        expect($user->role)->toBe($role);
    })->with(['admin', 'writer', 'editor', 'viewer'])
        ->group('unit', 'validation');

    it('rejects invalid role values', function () {
        expect(fn () => User::factory()->create(['role' => 'superuser']))
            ->toThrow(\Exception::class);
    })->group('unit', 'validation');
});

describe('Translation Language Validation', function () {
    it('NewsTranslation accepts valid language codes', function (string $lang) {
        $news = News::factory()->create();
        $translation = NewsTranslation::factory()->create([
            'news_id' => $news->id,
            'lang' => $lang,
        ]);

        expect($translation->lang)->toBe($lang);
    })->with(['uz', 'kr', 'en', 'ru'])
        ->group('unit', 'validation');

    it('CategoryTranslation accepts valid language codes', function (string $lang) {
        $category = Category::factory()->create();
        $translation = CategoryTranslation::factory()->create([
            'category_id' => $category->id,
            'language' => $lang,
        ]);

        expect($translation->language)->toBe($lang);
    })->with(['uz', 'kr', 'en', 'ru'])
        ->group('unit', 'validation');

    it('PageTranslation accepts valid language codes', function (string $lang) {
        $page = Page::factory()->create();
        $translation = PageTranslation::factory()->create([
            'page_id' => $page->id,
            'language' => $lang,
        ]);

        expect($translation->language)->toBe($lang);
    })->with(['uz', 'kr', 'en', 'ru'])
        ->group('unit', 'validation');
});

describe('Slug Validation', function () {
    it('news requires unique slug', function () {
        News::factory()->create(['slug' => 'unique-slug']);

        expect(fn () => News::factory()->create(['slug' => 'unique-slug']))
            ->toThrow(\Exception::class);
    })->group('unit', 'validation');

    it('category requires unique slug', function () {
        Category::factory()->create(['slug' => 'unique-cat-slug']);

        expect(fn () => Category::factory()->create(['slug' => 'unique-cat-slug']))
            ->toThrow(\Exception::class);
    })->group('unit', 'validation');

    it('page requires unique slug', function () {
        Page::factory()->create(['slug' => 'unique-page-slug']);

        expect(fn () => Page::factory()->create(['slug' => 'unique-page-slug']))
            ->toThrow(\Exception::class);
    })->group('unit', 'validation');
});
