<?php

use App\Models\NewsTranslation;

describe('NewsTranslation::coverUrl', function () {
    it('returns null when image_url is empty', function () {
        $t = new NewsTranslation(['image_url' => '']);
        expect($t->coverUrl())->toBeNull();
    })->group('feature', 'admin');

    it('returns absolute URLs unchanged', function () {
        $t = new NewsTranslation(['image_url' => 'https://cdn.example.com/x.jpg']);
        expect($t->coverUrl())->toBe('https://cdn.example.com/x.jpg');
    })->group('feature', 'admin');

    it('resolves storage paths via the public disk', function () {
        $t = new NewsTranslation(['image_url' => 'news/covers/abc.jpg']);
        expect($t->coverUrl())->toContain('/storage/news/covers/abc.jpg');
    })->group('feature', 'admin');

    it('resolves legacy bare filenames via /images/news/', function () {
        $t = new NewsTranslation(['image_url' => 'old.jpg']);
        expect($t->coverUrl())->toContain('/images/news/old.jpg');
    })->group('feature', 'admin');
});
