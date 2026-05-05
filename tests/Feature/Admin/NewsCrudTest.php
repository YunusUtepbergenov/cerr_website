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
            ->set('translations.kr.title', 'KR Title')
            ->set('translations.kr.short_description', 'KR short')
            ->set('translations.kr.content', '<p>KR body</p>')
            ->set('translations.uz.title', 'UZ Title')
            ->set('translations.uz.short_description', 'UZ short')
            ->set('translations.uz.content', '<p>UZ body</p>')
            ->set('translations.ru.title', 'RU Title')
            ->set('translations.ru.short_description', 'RU short')
            ->set('translations.ru.content', '<p>RU body</p>')
            ->set('translations.en.title', 'EN Title')
            ->set('translations.en.short_description', 'EN short')
            ->set('translations.en.content', '<p>EN body</p>')
            ->set('cover_uploads.kr', UploadedFile::fake()->image('cover.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $news = News::where('slug', 'my-first-news')->first();
        expect($news)->not->toBeNull()
            ->and($news->status)->toBe('published')
            ->and($news->category_id)->toBe($category->id)
            ->and($news->translations()->count())->toBe(4)
            ->and($news->tags->pluck('id')->all())->toContain($tag->id);

        $kr = $news->translations()->where('lang', 'kr')->first();
        expect($kr->title)->toBe('KR Title');
        expect($kr->image_url)->toStartWith('news/covers/');
        Storage::disk('public')->assertExists($kr->image_url);
    })->group('feature', 'admin');

    it('requires kr title', function () {
        Livewire::test(NewsForm::class)
            ->set('slug', 'partial')
            ->set('translations.kr.short_description', 'x')
            ->set('translations.kr.content', '<p>x</p>')
            ->call('save')
            ->assertHasErrors(['translations.kr.title']);
    })->group('feature', 'admin');

    it('rejects duplicate slug', function () {
        News::factory()->create(['slug' => 'taken']);

        Livewire::test(NewsForm::class)
            ->set('slug', 'taken')
            ->set('translations.kr.title', 'x')
            ->set('translations.kr.short_description', 'x')
            ->set('translations.kr.content', '<p>x</p>')
            ->call('save')
            ->assertHasErrors(['slug']);
    })->group('feature', 'admin');

    it('updates an existing news item', function () {
        $news = News::factory()->create(['slug' => 'old-slug']);
        $news->translations()->create([
            'lang' => 'kr',
            'title' => 'Old',
            'short_description' => 'Old',
            'content' => '<p>Old</p>',
            'image_url' => '',
        ]);

        Livewire::test(NewsForm::class, ['news' => $news])
            ->assertSet('slug', 'old-slug')
            ->set('translations.kr.title', 'Updated')
            ->call('save')
            ->assertHasNoErrors();

        expect($news->fresh()->translations()->where('lang', 'kr')->first()->title)->toBe('Updated');
    })->group('feature', 'admin');

    it('sanitizes script tags from content', function () {
        Livewire::test(NewsForm::class)
            ->set('slug', 'sanitized')
            ->set('translations.kr.title', 't')
            ->set('translations.kr.short_description', 's')
            ->set('translations.kr.content', '<p>safe</p><script>alert(1)</script>')
            ->call('save')
            ->assertHasNoErrors();

        $content = News::where('slug', 'sanitized')->first()->translations()->where('lang', 'kr')->first()->content;
        expect($content)->not->toContain('<script>');
        expect($content)->toContain('safe');
    })->group('feature', 'admin');

    it('lists news and supports search filter', function () {
        $news = News::factory()->create(['slug' => 'findme-slug']);
        $news->translations()->create([
            'lang' => 'kr',
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
            'lang' => 'kr',
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
