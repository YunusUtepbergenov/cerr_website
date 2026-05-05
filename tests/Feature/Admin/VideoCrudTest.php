<?php

use App\Livewire\Admin\Videos\VideoIndex;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

describe('Video CRUD', function () {
    it('creates a video', function () {
        Storage::fake('public');

        Livewire::test(VideoIndex::class)
            ->call('startCreate')
            ->set('title', 'My video')
            ->set('url', 'https://youtube.com/watch?v=xyz')
            ->set('imageUpload', UploadedFile::fake()->image('thumb.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        expect(Video::count())->toBe(1);
        expect(Video::first()->image)->toStartWith('videos/');
    })->group('feature', 'admin');

    it('requires a valid URL', function () {
        Livewire::test(VideoIndex::class)
            ->call('startCreate')
            ->set('title', 'X')
            ->set('url', 'not-a-url')
            ->call('save')
            ->assertHasErrors(['url']);
    })->group('feature', 'admin');

    it('deletes a video and its image file', function () {
        Storage::fake('public');
        Storage::disk('public')->put('videos/v.jpg', 'fake');
        $v = Video::factory()->create(['image' => 'videos/v.jpg']);

        Livewire::test(VideoIndex::class)->call('delete', $v->id);

        expect(Video::find($v->id))->toBeNull();
        Storage::disk('public')->assertMissing('videos/v.jpg');
    })->group('feature', 'admin');
});
