<?php

use App\Livewire\Admin\News\NewsForm;
use App\Livewire\Admin\News\NewsIndex;
use App\Models\Category;
use App\Models\News;
use App\Models\Tag;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    // Admin requests are forced to Russian by SetAdminLocale; mirror that here
    // so translated validation messages resolve (other locale files are empty).
    app()->setLocale('ru');
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

describe('News CRUD', function () {
    it('creates news with all 4 translations and a cover image', function () {
        Storage::fake('public');

        $category = Category::factory()->create();
        $tag = Tag::factory()->create();

        Livewire::test(NewsForm::class)
            ->set('slug', 'My First News')
            ->set('category_id', $category->id)
            ->set('status', 'published')
            ->set('tag_ids', [$tag->id])
            ->set('translations.uz.title', 'UZ Title')
            ->set('translations.uz.short_description', 'UZ short')
            ->set('translations.uz.content', '<p>UZ body</p>')
            ->set('translations.kr.title', 'KR Title')
            ->set('translations.kr.short_description', 'KR short')
            ->set('translations.kr.content', '<p>KR body</p>')
            ->set('translations.ru.title', 'RU Title')
            ->set('translations.ru.short_description', 'RU short')
            ->set('translations.ru.content', '<p>RU body</p>')
            ->set('translations.en.title', 'EN Title')
            ->set('translations.en.short_description', 'EN short')
            ->set('translations.en.content', '<p>EN body</p>')
            ->set('cover_uploads.uz', UploadedFile::fake()->image('cover.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $news = News::where('slug', 'my-first-news')->first();
        expect($news)->not->toBeNull()
            ->and($news->status)->toBe('published')
            ->and($news->category_id)->toBe($category->id)
            ->and($news->translations()->count())->toBe(4)
            ->and($news->tags->pluck('id')->all())->toContain($tag->id);

        $primary = $news->translations()->where('lang', 'uz')->first();
        expect($primary->title)->toBe('UZ Title');
        expect($primary->image_url)->toStartWith('news/covers/');
        Storage::disk('public')->assertExists($primary->image_url);
    })->group('feature', 'admin');

    it('requires at least one complete language', function () {
        Livewire::test(NewsForm::class)
            ->set('slug', 'partial')
            ->set('translations.uz.short_description', 'x')
            ->set('translations.uz.content', '<p>x</p>')
            ->call('save')
            ->assertHasErrors('translations');

        expect(News::where('slug', 'partial')->exists())->toBeFalse();
    })->group('feature', 'admin');

    it('rejects a language that is only partially filled', function () {
        Livewire::test(NewsForm::class)
            ->set('slug', 'partial-ru')
            ->set('translations.ru.title', 'Only a title')
            ->call('save')
            ->assertHasErrors('translations');
    })->group('feature', 'admin');

    it('creates news when only a non-primary language is filled', function () {
        Livewire::test(NewsForm::class)
            ->set('slug', 'ru only')
            ->set('status', 'published')
            ->set('translations.ru.title', 'RU Title')
            ->set('translations.ru.short_description', 'RU short')
            ->set('translations.ru.content', '<p>RU body</p>')
            ->call('save')
            ->assertHasNoErrors();

        $news = News::where('slug', 'ru-only')->first();
        expect($news)->not->toBeNull()
            ->and($news->translations()->count())->toBe(1)
            ->and($news->translations()->where('lang', 'ru')->first()->title)->toBe('RU Title')
            ->and($news->translations()->where('lang', 'uz')->exists())->toBeFalse();
    })->group('feature', 'admin');

    it('rejects duplicate slug', function () {
        News::factory()->create(['slug' => 'taken']);

        Livewire::test(NewsForm::class)
            ->set('slug', 'taken')
            ->set('translations.uz.title', 'x')
            ->set('translations.uz.short_description', 'x')
            ->set('translations.uz.content', '<p>x</p>')
            ->call('save')
            ->assertHasErrors(['slug']);
    })->group('feature', 'admin');

    it('auto-generates a unique slug from the title', function () {
        Livewire::test(NewsForm::class)
            ->call('regenerateSlug', 'Hello World')
            ->assertSet('slug', 'hello-world');

        News::factory()->create(['slug' => 'hello-world']);

        Livewire::test(NewsForm::class)
            ->call('regenerateSlug', 'Hello World')
            ->assertSet('slug', 'hello-world-2');
    })->group('feature', 'admin');

    it('flags a duplicate slug live, before the form is submitted', function () {
        News::factory()->create(['slug' => 'taken']);

        Livewire::test(NewsForm::class)
            ->set('slug', 'taken')
            ->call('checkSlugAvailability')
            ->assertHasErrors('slug');
    })->group('feature', 'admin');

    it('live-checks the normalized form without rewriting what the user typed', function () {
        News::factory()->create(['slug' => 'my-cool-article']);

        Livewire::test(NewsForm::class)
            ->set('slug', 'My Cool Article')
            ->call('checkSlugAvailability')
            ->assertSet('slug', 'My Cool Article')
            ->assertHasErrors('slug');
    })->group('feature', 'admin');

    it('normalizes the slug to its stored form on blur', function () {
        Livewire::test(NewsForm::class)
            ->set('slug', 'My Cool Article!')
            ->call('normalizeSlug')
            ->assertSet('slug', 'my-cool-article')
            ->assertHasNoErrors('slug');
    })->group('feature', 'admin');

    it('does not flag the current article as a duplicate of itself while editing', function () {
        $news = News::factory()->create(['slug' => 'self-slug']);

        Livewire::test(NewsForm::class, ['news' => $news])
            ->set('slug', 'self-slug')
            ->call('checkSlugAvailability')
            ->assertHasNoErrors('slug');
    })->group('feature', 'admin');

    it('clears the live duplicate-slug error once a unique slug is entered', function () {
        News::factory()->create(['slug' => 'taken']);

        Livewire::test(NewsForm::class)
            ->set('slug', 'taken')
            ->call('checkSlugAvailability')
            ->assertHasErrors('slug')
            ->set('slug', 'free-slug')
            ->call('checkSlugAvailability')
            ->assertHasNoErrors('slug');
    })->group('feature', 'admin');

    it('flags collisions with another article when editing, even before normalization', function () {
        $articleA = News::factory()->create(['slug' => 'article-a']);
        News::factory()->create(['slug' => 'article-b']);

        Livewire::test(NewsForm::class, ['news' => $articleA])
            ->set('slug', 'Article B')
            ->call('checkSlugAvailability')
            ->assertHasErrors('slug');
    })->group('feature', 'admin');

    it('updates an existing news item', function () {
        $news = News::factory()->create(['slug' => 'old-slug']);
        $news->translations()->create([
            'lang' => 'uz',
            'title' => 'Old',
            'short_description' => 'Old',
            'content' => '<p>Old</p>',
            'image_url' => '',
        ]);

        Livewire::test(NewsForm::class, ['news' => $news])
            ->assertSet('slug', 'old-slug')
            ->set('translations.uz.title', 'Updated')
            ->call('save')
            ->assertHasNoErrors();

        expect($news->fresh()->translations()->where('lang', 'uz')->first()->title)->toBe('Updated');
    })->group('feature', 'admin');

    it('sanitizes script tags from content', function () {
        Livewire::test(NewsForm::class)
            ->set('slug', 'sanitized')
            ->set('translations.uz.title', 't')
            ->set('translations.uz.short_description', 's')
            ->set('translations.uz.content', '<p>safe</p><script>alert(1)</script>')
            ->call('save')
            ->assertHasNoErrors();

        $content = News::where('slug', 'sanitized')->first()->translations()->where('lang', 'uz')->first()->content;
        expect($content)->not->toContain('<script>');
        expect($content)->toContain('safe');
    })->group('feature', 'admin');

    it('lists news and supports search filter', function () {
        $news = News::factory()->create(['slug' => 'findme-slug']);
        $news->translations()->create([
            'lang' => 'uz',
            'title' => 'Findme Title',
            'short_description' => 's',
            'content' => '<p>c</p>',
            'image_url' => '',
        ]);
        $other = News::factory()->create(['slug' => 'other-slug']);

        Livewire::test(NewsIndex::class)
            ->set('search', 'Findme')
            ->assertSee('Findme Title')
            ->assertDontSee('other-slug');
    })->group('feature', 'admin');

    it('deletes a news item and removes managed cover files', function () {
        Storage::fake('public');
        $news = News::factory()->create();
        Storage::disk('public')->put('news/covers/will-die.jpg', 'fake');
        $news->translations()->create([
            'lang' => 'uz',
            'title' => 't',
            'short_description' => 's',
            'content' => '<p>c</p>',
            'image_url' => 'news/covers/will-die.jpg',
        ]);

        Livewire::test(NewsIndex::class)
            ->call('delete', $news->id);

        expect(News::find($news->id))->toBeNull();
        Storage::disk('public')->assertMissing('news/covers/will-die.jpg');
    })->group('feature', 'admin');
});
