<?php

use App\Livewire\Admin\Pages\PageForm;
use App\Models\Page;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

describe('Page CRUD', function () {
    it('creates a page with all 4 translations', function () {
        Storage::fake('public');

        Livewire::test(PageForm::class)
            ->set('slug', 'about-test')
            ->set('translations.kr.title', 'KR')
            ->set('translations.kr.content', '<p>kr</p>')
            ->set('translations.uz.title', 'UZ')
            ->set('translations.uz.content', '<p>uz</p>')
            ->set('translations.ru.title', 'RU')
            ->set('translations.ru.content', '<p>ru</p>')
            ->set('translations.en.title', 'EN')
            ->set('translations.en.content', '<p>en</p>')
            ->set('imageUpload', UploadedFile::fake()->image('p.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $p = Page::where('slug', 'about-test')->first();
        expect($p)->not->toBeNull()->and($p->translations()->count())->toBe(4);
        expect($p->image)->toStartWith('pages/');
    })->group('feature', 'admin');

    it('rejects duplicate slug', function () {
        Page::factory()->create(['slug' => 'taken']);

        Livewire::test(PageForm::class)
            ->set('slug', 'taken')
            ->set('translations.kr.title', 't')
            ->set('translations.kr.content', '<p>c</p>')
            ->call('save')
            ->assertHasErrors(['slug']);
    })->group('feature', 'admin');
});
