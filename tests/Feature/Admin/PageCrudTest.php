<?php

use App\Livewire\Admin\Pages\PageForm;
use App\Livewire\Admin\Pages\PageIndex;
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

describe('Page list and system pages', function () {
    it('shows the title and the public link of pages bound to site routes', function () {
        $page = Page::factory()->create(['slug' => 'objectives']);
        $page->translations()->create(['language' => 'kr', 'title' => 'Марказ вазифалари', 'content' => '<p>x</p>', 'image' => '']);
        $free = Page::factory()->create(['slug' => 'free-form']);
        $free->translations()->create(['language' => 'kr', 'title' => 'Эркин саҳифа', 'content' => '<p>y</p>', 'image' => '']);

        $html = $this->get(route('admin.pages.index'))->assertOk()->getContent();

        expect($html)->toContain('Марказ вазифалари')
            ->and($html)->toContain('data-public-url="objectives"')
            ->and($html)->toContain('href="'.route('about').'"')
            ->and($html)->toContain('data-system-page="objectives"')
            ->and($html)->toContain('Эркин саҳифа')
            ->and($html)->not->toContain('data-public-url="free-form"')
            ->and($html)->not->toContain('data-system-page="free-form"');
    })->group('feature', 'admin');

    it('refuses to delete a page bound to a site route', function () {
        $page = Page::factory()->create(['slug' => 'history']);

        Livewire::test(PageIndex::class)
            ->call('delete', $page->id);

        expect(Page::find($page->id))->not->toBeNull();
    })->group('feature', 'admin');

    it('still deletes free-form pages', function () {
        $page = Page::factory()->create(['slug' => 'free-form']);

        Livewire::test(PageIndex::class)
            ->call('delete', $page->id);

        expect(Page::find($page->id))->toBeNull();
    })->group('feature', 'admin');

    it('offers an open-on-site button when editing a system page', function () {
        $page = Page::factory()->create(['slug' => 'history']);
        $page->translations()->create(['language' => 'kr', 'title' => 'Тарих', 'content' => '<p>x</p>', 'image' => '']);

        $html = $this->get(route('admin.pages.edit', $page))->assertOk()->getContent();

        expect($html)->toContain('href="'.route('history').'"')
            ->and($html)->toContain(__('admin.pages.open_on_site'))
            ->and($html)->toContain(__('admin.pages.system_page_help'));
    })->group('feature', 'admin');
});
