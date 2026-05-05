<?php

use App\Livewire\Admin\News\NewsForm;
use App\Livewire\Admin\Pages\PageForm;
use App\Livewire\Admin\Videos\VideoIndex;
use App\Models\User;
use Illuminate\Http\UploadedFile;
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

    it('renders an img tag for a staged page image upload', function () {
        Livewire::test(PageForm::class)
            ->set('imageUpload', UploadedFile::fake()->image('hero.jpg', 800, 600))
            ->assertSeeHtml('<img');
    })->group('feature', 'admin');
});
