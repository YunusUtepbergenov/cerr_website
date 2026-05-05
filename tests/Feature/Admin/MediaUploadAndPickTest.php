<?php

use App\Livewire\Admin\Media\MediaIndex;
use App\Livewire\Admin\Pages\PageForm;
use App\Livewire\Admin\Videos\VideoIndex;
use App\Models\Page;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    app()->setLocale('ru');
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

describe('Media library upload', function () {
    it('uploads multiple files into the chosen folder', function () {
        Livewire::test(MediaIndex::class)
            ->set('uploadFolder', 'news/covers')
            ->set('uploads', [
                UploadedFile::fake()->image('a.jpg'),
                UploadedFile::fake()->image('b.png'),
            ])
            ->call('save')
            ->assertHasNoErrors();

        expect(Storage::disk('public')->files('news/covers'))->toHaveCount(2);
    })->group('feature', 'admin');

    it('rejects non-images', function () {
        Livewire::test(MediaIndex::class)
            ->set('uploadFolder', 'news/covers')
            ->set('uploads', [UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf')])
            ->call('save')
            ->assertHasErrors(['uploads.0']);
    })->group('feature', 'admin');

    it('rejects unknown folders', function () {
        Livewire::test(MediaIndex::class)
            ->set('uploadFolder', 'something/else')
            ->set('uploads', [UploadedFile::fake()->image('x.jpg')])
            ->call('save')
            ->assertHasErrors(['uploadFolder']);
    })->group('feature', 'admin');
});

describe('Video form picker integration', function () {
    it('sets video image from a picked path without re-uploading', function () {
        Storage::disk('public')->put('videos/existing.jpg', 'fake');

        Livewire::test(VideoIndex::class)
            ->call('startCreate')
            ->set('title', 'Conference')
            ->set('url', 'https://youtube.com/watch?v=xyz')
            ->set('image', 'videos/existing.jpg')
            ->call('save')
            ->assertHasNoErrors();

        expect(Video::where('title', 'Conference')->first()->image)->toBe('videos/existing.jpg');
    })->group('feature', 'admin');
});

describe('Page form picker integration', function () {
    it('sets page image from a picked path without re-uploading', function () {
        Storage::disk('public')->put('pages/existing.jpg', 'fake');

        Livewire::test(PageForm::class)
            ->set('slug', 'about')
            ->set('image', 'pages/existing.jpg')
            ->set('translations.kr.title', 'About')
            ->set('translations.kr.content', '<p>about</p>')
            ->call('save')
            ->assertHasNoErrors();

        expect(Page::where('slug', 'about')->first()->image)->toBe('pages/existing.jpg');
    })->group('feature', 'admin');
});
