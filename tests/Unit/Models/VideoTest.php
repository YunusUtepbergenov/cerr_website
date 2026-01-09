<?php

use App\Models\Video;

describe('Video Model', function () {
    it('has fillable attributes', function () {
        $video = Video::factory()->create([
            'title' => 'Test Video Title',
            'image' => 'test-image.jpg',
            'url' => 'https://youtube.com/watch?v=abc123',
        ]);

        expect($video->title)->toBe('Test Video Title')
            ->and($video->image)->toBe('test-image.jpg')
            ->and($video->url)->toContain('youtube.com');
    })->group('unit', 'models');

    it('factory creates valid video', function () {
        $video = Video::factory()->create();

        expect($video)->toBeInstanceOf(Video::class)
            ->and($video->title)->not->toBeEmpty()
            ->and($video->image)->not->toBeEmpty()
            ->and($video->url)->not->toBeEmpty();
    })->group('unit', 'models');

    it('can create multiple videos', function () {
        $videos = Video::factory()->count(3)->create();

        expect($videos)->toHaveCount(3)
            ->and(Video::count())->toBe(3);
    })->group('unit', 'models');

    it('has timestamps', function () {
        $video = Video::factory()->create();

        expect($video->created_at)->not->toBeNull()
            ->and($video->updated_at)->not->toBeNull();
    })->group('unit', 'models');
});
