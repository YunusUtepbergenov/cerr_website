<?php

use App\Models\Category;
use App\Models\News;
use App\Models\NewsTranslation;
use App\Models\Tag;
use App\Models\User;

describe('News Model', function () {
    beforeEach(function () {
        setAppLocale('uz');
    });

    it('has translations relationship', function () {
        $news = News::factory()->create();
        NewsTranslation::factory()->count(3)->create(['news_id' => $news->id]);

        expect($news->translations)->toHaveCount(3);
    })->group('unit', 'models');

    it('has translation relationship filtered by locale', function () {
        $news = News::factory()->create();
        NewsTranslation::factory()->create(['news_id' => $news->id, 'lang' => 'uz']);
        NewsTranslation::factory()->create(['news_id' => $news->id, 'lang' => 'en']);

        expect($news->translation)->not->toBeNull()
            ->and($news->translation->lang)->toBe('uz');
    })->group('unit', 'models');

    it('returns null translation when locale not found', function () {
        setAppLocale('kr');
        $news = News::factory()->create();
        NewsTranslation::factory()->create(['news_id' => $news->id, 'lang' => 'uz']);

        expect($news->translation)->toBeNull();
    })->group('unit', 'models');

    it('belongs to category', function () {
        $category = Category::factory()->create();
        $news = News::factory()->create(['category_id' => $category->id]);

        expect($news->category)->toBeInstanceOf(Category::class)
            ->and($news->category->id)->toBe($category->id);
    })->group('unit', 'models');

    it('belongs to user', function () {
        $user = User::factory()->create();
        $news = News::factory()->create(['user_id' => $user->id]);

        expect($news->user)->toBeInstanceOf(User::class)
            ->and($news->user->id)->toBe($user->id);
    })->group('unit', 'models');

    it('has many-to-many relationship with tags', function () {
        $news = News::factory()->create();
        $tag1 = Tag::factory()->create();
        $tag2 = Tag::factory()->create();

        $news->tags()->attach([$tag1->id, $tag2->id]);

        expect($news->tags)->toHaveCount(2)
            ->and($news->tags->pluck('id')->toArray())->toContain($tag1->id, $tag2->id);
    })->group('unit', 'models');

    it('has default view count of zero', function () {
        $news = News::factory()->create();
        expect($news->view_count)->toBe(0);
    })->group('unit', 'models');

    it('has unique slug', function () {
        News::factory()->create(['slug' => 'test-slug']);

        expect(fn () => News::factory()->create(['slug' => 'test-slug']))
            ->toThrow(\Exception::class);
    })->group('unit', 'models');

    it('can increment view count', function () {
        $news = News::factory()->create(['view_count' => 10]);

        $news->increment('view_count');

        expect($news->fresh()->view_count)->toBe(11);
    })->group('unit', 'models');

    it('accepts valid status values', function (string $status) {
        $news = News::factory()->create(['status' => $status]);
        expect($news->status)->toBe($status);
    })->with(['draft', 'published', 'auto_publish', 'disabled'])
        ->group('unit', 'models');
});
