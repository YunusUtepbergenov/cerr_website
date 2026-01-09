<?php

use App\Livewire\About;
use App\Livewire\History;
use App\Livewire\Vacancies;
use App\Models\Page;
use App\Models\PageTranslation;
use Livewire\Livewire;

describe('About Component', function () {
    it('loads page by objectives slug', function () {
        setAppLocale('uz');
        $page = Page::factory()->create(['slug' => 'objectives']);
        PageTranslation::factory()->create(['page_id' => $page->id, 'language' => 'uz']);

        Livewire::test(About::class)
            ->assertSet('page', fn ($val) => $val->slug === 'objectives')
            ->assertStatus(200);
    })->group('feature', 'livewire');

    it('aborts 404 when objectives page not found', function () {
        setAppLocale('uz');

        $this->get(route('about'))
            ->assertNotFound();
    })->group('feature', 'livewire');

    it('aborts 404 when objectives page has no translation', function () {
        setAppLocale('uz');
        Page::factory()->create(['slug' => 'objectives']);

        $this->get(route('about'))
            ->assertNotFound();
    })->group('feature', 'livewire');
});

describe('History Component', function () {
    it('loads page by history slug', function () {
        setAppLocale('uz');
        $page = Page::factory()->create(['slug' => 'history']);
        PageTranslation::factory()->create(['page_id' => $page->id, 'language' => 'uz']);

        Livewire::test(History::class)
            ->assertSet('page', fn ($val) => $val->slug === 'history')
            ->assertStatus(200);
    })->group('feature', 'livewire');

    it('aborts 404 when history page not found', function () {
        setAppLocale('uz');

        $this->get(route('history'))
            ->assertNotFound();
    })->group('feature', 'livewire');
});

describe('Vacancies Component', function () {
    it('loads page by vacancies slug', function () {
        setAppLocale('uz');
        $page = Page::factory()->create(['slug' => 'vacancies']);
        PageTranslation::factory()->create(['page_id' => $page->id, 'language' => 'uz']);

        Livewire::test(Vacancies::class)
            ->assertSet('page', fn ($val) => $val->slug === 'vacancies')
            ->assertStatus(200);
    })->group('feature', 'livewire');

    it('loads popular news', function () {
        setAppLocale('uz');
        $page = Page::factory()->create(['slug' => 'vacancies']);
        PageTranslation::factory()->create(['page_id' => $page->id, 'language' => 'uz']);
        createNewsWithTranslation(['view_count' => 100]);

        Livewire::test(Vacancies::class)
            ->assertSet('popular_news', fn ($val) => $val->count() > 0);
    })->group('feature', 'livewire');

    it('limits popular news to 8 items', function () {
        setAppLocale('uz');
        $page = Page::factory()->create(['slug' => 'vacancies']);
        PageTranslation::factory()->create(['page_id' => $page->id, 'language' => 'uz']);

        for ($i = 0; $i < 15; $i++) {
            createNewsWithTranslation(['view_count' => 100 - $i]);
        }

        Livewire::test(Vacancies::class)
            ->assertSet('popular_news', fn ($val) => $val->count() === 8);
    })->group('feature', 'livewire');
});
