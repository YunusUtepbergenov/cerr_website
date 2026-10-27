<?php

use App\Livewire\Admin\News\NewsForm;
use App\Livewire\Admin\Pages\PageForm;
use App\Livewire\Admin\Videos\VideoIndex;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    app()->setLocale('ru');
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

describe('Live image preview', function () {
    it('renders an img tag for a staged news cover upload', function () {
        Livewire::test(NewsForm::class)
            ->set('cover_uploads.kr', UploadedFile::fake()->image('preview.jpg', 800, 600))
            ->assertSeeHtml('<img');
    })->group('feature', 'admin');

    it('renders an img tag for a staged video thumbnail upload', function () {
        Livewire::test(VideoIndex::class)
            ->call('startCreate')
            ->set('imageUpload', UploadedFile::fake()->image('thumb.jpg', 800, 600))
            ->assertSeeHtml('<img');
    })->group('feature', 'admin');

    it('shows the resolved thumbnail in the video list and edit form instead of a storage path', function () {
        Http::fake(['img.youtube.com/*' => Http::response('', 200)]);
        Storage::disk('public')->put('videos/uploaded.jpg', 'stub');

        $fromYoutube = Video::factory()->create(['image' => 'https://i.ytimg.com/vi/YlytocIqHxo/mqdefault.jpg', 'url' => 'https://www.youtube.com/embed/YlytocIqHxo']);
        $uploaded = Video::factory()->create(['image' => 'videos/uploaded.jpg', 'url' => 'https://www.youtube.com/embed/abcdefghijk']);

        Livewire::test(VideoIndex::class)
            ->assertSeeHtml('src="https://img.youtube.com/vi/YlytocIqHxo/maxresdefault.jpg"')
            ->assertSeeHtml('src="'.Storage::disk('public')->url('videos/uploaded.jpg').'"')
            ->assertDontSeeHtml('/storage/https://')
            ->call('edit', $fromYoutube->id)
            ->assertSeeHtml('src="https://img.youtube.com/vi/YlytocIqHxo/maxresdefault.jpg"')
            ->assertDontSeeHtml('/storage/https://')
            ->call('edit', $uploaded->id)
            ->assertSeeHtml('src="'.Storage::disk('public')->url('videos/uploaded.jpg').'"');
    })->group('feature', 'admin');

    it('renders an img tag for a staged page image upload', function () {
        Livewire::test(PageForm::class)
            ->set('imageUpload', UploadedFile::fake()->image('hero.jpg', 800, 600))
            ->assertSeeHtml('<img');
    })->group('feature', 'admin');
});
