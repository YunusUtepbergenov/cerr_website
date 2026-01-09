<?php

use App\Models\Page;
use App\Models\PageTranslation;

describe('Page Model', function () {
    beforeEach(function () {
        setAppLocale('uz');
    });

    it('has translations relationship', function () {
        $page = Page::factory()->create();
        PageTranslation::factory()->count(2)->create(['page_id' => $page->id]);

        expect($page->translations)->toHaveCount(2);
    })->group('unit', 'models');

    it('has translation relationship filtered by locale', function () {
        $page = Page::factory()->create();
        PageTranslation::factory()->create(['page_id' => $page->id, 'language' => 'uz']);
        PageTranslation::factory()->create(['page_id' => $page->id, 'language' => 'en']);

        expect($page->translation)->not->toBeNull()
            ->and($page->translation->language)->toBe('uz');
    })->group('unit', 'models');

    it('translation returns null when locale not found', function () {
        setAppLocale('kr');
        $page = Page::factory()->create();
        PageTranslation::factory()->create(['page_id' => $page->id, 'language' => 'uz']);

        expect($page->translation)->toBeNull();
    })->group('unit', 'models');

    it('has unique slug', function () {
        Page::factory()->create(['slug' => 'test-page-slug']);

        expect(fn () => Page::factory()->create(['slug' => 'test-page-slug']))
            ->toThrow(\Exception::class);
    })->group('unit', 'models');

    it('can create page with translation using helper', function () {
        $page = createPageWithTranslation(['slug' => 'test-page']);

        expect($page)->toBeInstanceOf(Page::class)
            ->and($page->translation)->not->toBeNull()
            ->and($page->translation->language)->toBe('uz');
    })->group('unit', 'models');
});
