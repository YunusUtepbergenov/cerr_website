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
            ->toThrow(Exception::class);
    })->group('unit', 'models');

    it('can create page with translation using helper', function () {
        $page = createPageWithTranslation(['slug' => 'test-page']);

        expect($page)->toBeInstanceOf(Page::class)
            ->and($page->translation)->not->toBeNull()
            ->and($page->translation->language)->toBe('uz');
    })->group('unit', 'models');
});

describe('Page public routes', function () {
    it('maps the fixed slugs to their site routes', function () {
        expect(Page::factory()->make(['slug' => 'objectives'])->publicUrl())->toBe(route('about'))
            ->and(Page::factory()->make(['slug' => 'history'])->publicUrl())->toBe(route('history'))
            ->and(Page::factory()->make(['slug' => 'vacancies'])->publicUrl())->toBe(route('vacancies'))
            ->and(Page::factory()->make(['slug' => 'anything-else'])->publicUrl())->toBeNull()
            ->and(Page::factory()->make(['slug' => 'history'])->isSystemPage())->toBeTrue()
            ->and(Page::factory()->make(['slug' => 'anything-else'])->isSystemPage())->toBeFalse();
    })->group('unit', 'models');

    it('falls back to the building photo when no hero image is uploaded', function () {
        expect(Page::factory()->make(['image' => null])->heroImageUrl())->toEndWith('/images/research-staff/cerr-building.webp')
            ->and(Page::factory()->make(['image' => 'pages/a.jpg'])->heroImageUrl())->toEndWith('/storage/pages/a.jpg')
            ->and(Page::factory()->make(['image' => 'https://cdn.example/x.jpg'])->heroImageUrl())->toBe('https://cdn.example/x.jpg');
    })->group('unit', 'models');

    it('formats plain-text translations and sanitizes rich ones', function () {
        $plain = PageTranslation::factory()->make(['content' => "Сарлавҳа\n\nМатн."]);
        $rich = PageTranslation::factory()->make(['content' => '<p>Ok</p><script>bad()</script>']);

        expect($plain->bodyHtml())->toBe("<h2>Сарлавҳа</h2>\n<p>Матн.</p>")
            ->and($rich->bodyHtml())->toBe('<p>Ok</p>')
            ->and(PageTranslation::factory()->make(['content' => ' '])->bodyHtml())->toBe('');
    })->group('unit', 'models');
});
