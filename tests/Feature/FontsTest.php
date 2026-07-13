<?php

describe('Site fonts', function () {
    it('loads the self-hosted font stylesheet on public pages', function () {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('css/fonts.css');
    })->group('feature');

    it('cache-busts the mutable stylesheets so css changes reach returning visitors', function () {
        $html = $this->get('/')->assertStatus(200)->getContent();

        foreach (['css/fonts.css', 'css/style.css', 'css/site-pages.css', 'css/news-article.css'] as $css) {
            expect($html)->toContain($css.'?v=');
        }
    })->group('feature');

    it('does not load the public font stylesheet on the admin login page', function () {
        $this->get('/login')
            ->assertStatus(200)
            ->assertDontSee('css/fonts.css');
    })->group('feature');

    it('ships font files for both families with cyrillic coverage', function () {
        foreach ([
            'golos-text-latin-wght-normal.woff2',
            'golos-text-cyrillic-wght-normal.woff2',
            'source-sans-3-latin-wght-normal.woff2',
            'source-sans-3-cyrillic-wght-normal.woff2',
        ] as $file) {
            expect(file_exists(public_path("fonts/{$file}")))->toBeTrue("missing fonts/{$file}");
        }
    })->group('feature');
});
