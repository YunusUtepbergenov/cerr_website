<?php

use App\Models\Page;
use App\Models\PageTranslation;

/**
 * Create an about-section page in the active locale with the given body.
 */
function makeAboutPage(string $slug, string $content, array $pageAttrs = []): Page
{
    $page = Page::factory()->create(array_merge(['slug' => $slug, 'image' => null], $pageAttrs));
    PageTranslation::factory()->create([
        'page_id' => $page->id,
        'language' => app()->getLocale(),
        'title' => 'Марказ тарихи',
        'content' => $content,
    ]);

    return $page;
}

describe('About section pages', function () {
    beforeEach(function () {
        setAppLocale('kr');
    });

    it('renders the hero, section tabs and the body in order', function () {
        makeAboutPage('objectives', '<p>Body text</p>');

        $this->get(route('about'))
            ->assertOk()
            ->assertSeeInOrder([
                'ab-hero', 'article-breadcrumb', '<h1 class="ab-title">Марказ тарихи</h1>', 'ab-hero-photo', 'cerr-building.webp',
                'ab-tabs', 'ab-body', 'Body text',
            ], false)
            ->assertSee('about-pages.css')
            ->assertDontSee('rs-page');
    })->group('feature', 'livewire');

    it('marks the current page in the tabs and links the sibling pages', function () {
        makeAboutPage('history', '<p>x</p>');

        $html = $this->get(route('history'))->assertOk()->getContent();

        expect($html)->toContain('class="ab-tab is-active" aria-current="page">'.__('messages.history').'</a>')
            ->and($html)->toContain('href="'.route('about').'" wire:navigate class="ab-tab">'.__('messages.objectives').'</a>')
            ->and($html)->toContain('href="'.route('leadership').'"')
            ->and($html)->toContain('href="'.route('structure').'"')
            ->and($html)->toContain('href="'.route('open-data.index').'"')
            ->and(substr_count($html, 'aria-current="page"'))->toBe(1);
    })->group('feature', 'livewire');

    it('lists the chapters of a body with several h2 and anchors them', function () {
        makeAboutPage('history', '<p>Intro</p><h2>Трансформация</h2><p>a</p><h2>Янги йўналишлар</h2><p>b</p>');

        $response = $this->get(route('history'))->assertOk();
        $html = $response->getContent();

        expect(substr_count($html, 'class="ab-toc"'))->toBe(1)
            ->and($html)->toContain('<a href="#chapter-1">Трансформация</a>')
            ->and($html)->toContain('<a href="#chapter-2">Янги йўналишлар</a>')
            ->and($html)->toContain('<h2 id="chapter-1">Трансформация</h2>')
            ->and($html)->toContain(__('messages.on_this_page'));
        $response->assertSeeInOrder(['ab-toc', 'ab-body', 'id="chapter-1"', 'id="chapter-2"'], false);
    })->group('feature', 'livewire');

    it('shows no chapter list for a single heading', function () {
        makeAboutPage('history', '<h2>Only one</h2><p>a</p>');

        $this->get(route('history'))->assertOk()->assertDontSee('ab-toc');
    })->group('feature', 'livewire');

    it('formats legacy plain-text bodies into paragraphs, headings and lists', function () {
        makeAboutPage('objectives', "Часть задач сохранена, а именно:\n\n- проведение исследований;\n\n- обеспечение материалами.\n\nНовые задачи\n\nВ соответствии с ПП-4471 (https://lex.uz/ru/docs/4552322) на Центр возложены задачи.\n\nДополнительные задачи\n\nЕщё.");

        $html = $this->get(route('about'))->assertOk()->getContent();

        expect($html)->toContain('<p>Часть задач сохранена, а именно:</p>')
            ->and($html)->toContain('<li>проведение исследований;</li>')
            ->and($html)->toContain('<h2 id="chapter-1">Новые задачи</h2>')
            ->and($html)->toContain('<a href="https://lex.uz/ru/docs/4552322" target="_blank" rel="noopener noreferrer">lex.uz</a>')
            ->and($html)->toContain('ab-body ab-body--tasks')
            ->and($html)->toContain('class="ab-toc"');
    })->group('feature', 'livewire');

    it('still sanitizes rich bodies and keeps unsafe markup out', function () {
        makeAboutPage('objectives', '<p>Safe</p><script>alert(1)</script><p onclick="x()">Click</p>');

        $html = $this->get(route('about'))->assertOk()->getContent();

        expect($html)->toContain('<p>Safe</p>')
            ->and($html)->not->toContain('alert(1)')
            ->and($html)->not->toContain('onclick');
    })->group('feature', 'livewire');

    it('uses the image uploaded in the admin as the hero photo', function () {
        makeAboutPage('history', '<p>x</p>', ['image' => 'pages/hall.jpg']);

        $html = $this->get(route('history'))->assertOk()->getContent();

        expect($html)->toContain('/storage/pages/hall.jpg')
            ->and($html)->not->toContain('cerr-building');
    })->group('feature', 'livewire');

    it('does not class the history body as a task list', function () {
        makeAboutPage('history', '<ul><li>a</li></ul>');

        $this->get(route('history'))->assertOk()->assertDontSee('ab-body--tasks');
    })->group('feature', 'livewire');

    it('never shows the ИТИМ abbreviation on the Uzbek about pages', function () {
        setAppLocale('uz');
        makeAboutPage('history', '<p>Markaz</p>');

        $html = $this->get(route('history'))->assertOk()->getContent();

        expect($html)->not->toContain('ITIM')->and($html)->not->toContain('ИТИМ');
    })->group('feature', 'livewire');
});
