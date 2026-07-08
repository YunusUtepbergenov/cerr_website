<?php

use App\Models\Video;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;

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

describe('Video thumbnails', function () {
    it('uses the maxresdefault YouTube thumbnail when available', function () {
        Http::fake(['img.youtube.com/*' => Http::response('', 200)]);

        $video = Video::factory()->make(['image' => null, 'url' => 'https://www.youtube.com/watch?v=YlytocIqHxo']);

        expect($video->thumbnailUrl())->toBe('https://img.youtube.com/vi/YlytocIqHxo/maxresdefault.jpg');
    })->group('unit', 'models');

    it('falls back to hqdefault when maxresdefault does not exist', function () {
        Http::fake(['img.youtube.com/*' => Http::response('', 404)]);

        $video = Video::factory()->make(['image' => null, 'url' => 'https://www.youtube.com/watch?v=YlytocIqHxo']);

        expect($video->thumbnailUrl())->toBe('https://img.youtube.com/vi/YlytocIqHxo/hqdefault.jpg');
    })->group('unit', 'models');

    it('caches the maxresdefault probe so repeated calls make one request', function () {
        Http::fake(['img.youtube.com/*' => Http::response('', 200)]);

        $video = Video::factory()->make(['image' => null, 'url' => 'https://www.youtube.com/watch?v=YlytocIqHxo']);
        $video->thumbnailUrl();
        $video->thumbnailUrl();

        Http::assertSentCount(1);
    })->group('unit', 'models');

    it('falls back to hqdefault when the probe cannot connect and does not re-probe on every render', function () {
        Http::fake(['img.youtube.com/*' => fn () => throw new ConnectionException('timed out')]);

        $video = Video::factory()->make(['image' => null, 'url' => 'https://www.youtube.com/watch?v=YlytocIqHxo']);

        expect($video->thumbnailUrl())->toBe('https://img.youtube.com/vi/YlytocIqHxo/hqdefault.jpg')
            // The failure is cached, so subsequent renders skip the probe.
            ->and(Cache::get('video-maxres:YlytocIqHxo'))->toBeFalse();
    })->group('unit', 'models');

    it('prefers an uploaded local image over the YouTube thumbnail', function () {
        Http::fake();

        $filename = 'phpunit-thumb-test.jpg';
        file_put_contents(public_path('images/video/'.$filename), 'stub');

        try {
            $video = Video::factory()->make(['image' => $filename, 'url' => 'https://www.youtube.com/watch?v=YlytocIqHxo']);

            expect($video->thumbnailUrl())->toBe(asset('images/video/'.$filename));
            Http::assertNothingSent();
        } finally {
            unlink(public_path('images/video/'.$filename));
        }
    })->group('unit', 'models');

    it('extracts the YouTube id from common URL shapes', function (string $url) {
        $video = Video::factory()->make(['url' => $url]);

        expect($video->youtubeId())->toBe('YlytocIqHxo');
    })->group('unit', 'models')->with([
        'https://www.youtube.com/watch?v=YlytocIqHxo',
        'https://youtu.be/YlytocIqHxo',
        'https://www.youtube.com/embed/YlytocIqHxo',
        'https://www.youtube.com/shorts/YlytocIqHxo',
    ]);

    it('returns null thumbnail when there is no image and no recognisable URL', function () {
        $video = Video::factory()->make(['image' => null, 'url' => 'https://example.com/video.mp4']);

        expect($video->thumbnailUrl())->toBeNull();
    })->group('unit', 'models');
});
