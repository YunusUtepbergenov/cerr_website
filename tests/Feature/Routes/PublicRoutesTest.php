<?php

use App\Models\Page;
use App\Models\PageTranslation;
use App\Models\Video;

describe('Public Routes', function () {
    it('home route works', function () {
        $this->withoutExceptionHandling();
        $this->get(route('home'))->assertStatus(200);
    })->group('feature', 'routes');

    it('language switch route works', function () {
        $this->get(route('lang.switch', ['locale' => 'en']))
            ->assertRedirect()
            ->assertSessionHas('locale', 'en');
    })->group('feature', 'routes');

    it('show news route works with valid slug', function () {
        $news = createNewsWithTranslation(['slug' => 'test-news']);

        $this->get(route('show.news', ['slug' => 'test-news']))
            ->assertStatus(200);
    })->group('feature', 'routes');

    it('show news route redirects with invalid slug', function () {
        $this->get(route('show.news', ['slug' => 'non-existent']))
            ->assertRedirect('/');
    })->group('feature', 'routes');

    it('show category route works with valid slug', function () {
        setAppLocale('uz');
        $category = createCategoryWithTranslation(['slug' => 'test-cat']);

        $this->get(route('show.category', ['slug' => 'test-cat']))
            ->assertStatus(200);
    })->group('feature', 'routes');

    it('show category route returns 404 with invalid slug', function () {
        $this->get(route('show.category', ['slug' => 'non-existent']))
            ->assertNotFound();
    })->group('feature', 'routes');

    it('show all categories route works', function () {
        setAppLocale('uz');
        createCategoryWithTranslation();

        $this->get(route('show.all.category'))
            ->assertStatus(200);
    })->group('feature', 'routes');

    it('videos index route works', function () {
        $this->get(route('videos.index'))
            ->assertStatus(200);
    })->group('feature', 'routes');

    it('videos show route works with valid id', function () {
        $video = Video::factory()->create();

        $this->get(route('videos.show', ['id' => $video->id]))
            ->assertStatus(200);
    })->group('feature', 'routes');

    it('videos show route returns 404 with invalid id', function () {
        $this->get(route('videos.show', ['id' => 999]))
            ->assertNotFound();
    })->group('feature', 'routes');

    it('history route works', function () {
        setAppLocale('uz');
        $page = Page::factory()->create(['slug' => 'history']);
        PageTranslation::factory()->create(['page_id' => $page->id, 'language' => 'uz']);

        $this->get(route('history'))->assertStatus(200);
    })->group('feature', 'routes');

    it('about route works', function () {
        setAppLocale('uz');
        $page = Page::factory()->create(['slug' => 'objectives']);
        PageTranslation::factory()->create(['page_id' => $page->id, 'language' => 'uz']);

        $this->get(route('about'))->assertStatus(200);
    })->group('feature', 'routes');

    it('leadership route works', function () {
        $this->get(route('leadership'))->assertStatus(200);
    })->group('feature', 'routes');

    it('structure route works', function () {
        $this->get(route('structure'))->assertStatus(200);
    })->group('feature', 'routes');

    it('contact route works', function () {
        $this->get(route('contact'))->assertStatus(200);
    })->group('feature', 'routes');

    it('vacancies route works', function () {
        setAppLocale('uz');
        $page = Page::factory()->create(['slug' => 'vacancies']);
        PageTranslation::factory()->create(['page_id' => $page->id, 'language' => 'uz']);

        $this->get(route('vacancies'))->assertStatus(200);
    })->group('feature', 'routes');
});
