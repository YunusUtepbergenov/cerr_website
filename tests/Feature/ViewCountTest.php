<?php

use App\Models\News;

describe('View Count Functionality', function () {
    it('popular news ordered by view count', function () {
        setAppLocale('uz');
        $high = createNewsWithTranslation(['view_count' => 1000]);
        $medium = createNewsWithTranslation(['view_count' => 500]);
        $low = createNewsWithTranslation(['view_count' => 100]);

        $popularNews = News::whereHas('translations', fn ($q) => $q->where('lang', 'uz'))
            ->orderBy('view_count', 'DESC')
            ->limit(3)
            ->get();

        expect($popularNews->first()->id)->toBe($high->id)
            ->and($popularNews->last()->id)->toBe($low->id);
    })->group('integration', 'view-count');

    it('view count can be incremented', function () {
        $news = createNewsWithTranslation(['view_count' => 10]);

        $news->increment('view_count');

        expect($news->fresh()->view_count)->toBe(11);
    })->group('integration', 'view-count');

    it('news with zero view count appears last in popular list', function () {
        setAppLocale('uz');
        $popular = createNewsWithTranslation(['view_count' => 100]);
        $unpopular = createNewsWithTranslation(['view_count' => 0]);

        $popularNews = News::whereHas('translations', fn ($q) => $q->where('lang', 'uz'))
            ->orderBy('view_count', 'DESC')
            ->get();

        expect($popularNews->first()->id)->toBe($popular->id)
            ->and($popularNews->last()->id)->toBe($unpopular->id);
    })->group('integration', 'view-count');

    it('view count maintains order across multiple queries', function () {
        setAppLocale('uz');
        createNewsWithTranslation(['view_count' => 100]);
        createNewsWithTranslation(['view_count' => 200]);
        createNewsWithTranslation(['view_count' => 150]);

        $query1 = News::whereHas('translations', fn ($q) => $q->where('lang', 'uz'))
            ->orderBy('view_count', 'DESC')
            ->get();

        $query2 = News::whereHas('translations', fn ($q) => $q->where('lang', 'uz'))
            ->orderBy('view_count', 'DESC')
            ->get();

        expect($query1->pluck('id')->toArray())->toBe($query2->pluck('id')->toArray());
    })->group('integration', 'view-count');
});
