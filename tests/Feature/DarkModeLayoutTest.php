<?php

describe('Dark mode layout integration', function () {
    it('loads a version-stamped dark.css after the page-level styles stack', function () {
        $html = $this->get('/')->assertStatus(200)->getContent();

        expect($html)->toContain('css/dark.css?v=')
            ->and(strpos($html, 'css/dark.css'))
            ->toBeGreaterThan(strpos($html, 'css/site-pages.css'));
    })->group('feature');

    it('applies the stored theme before the first stylesheet to prevent a light flash', function () {
        $html = $this->get('/')->assertStatus(200)->getContent();

        $prePaintScript = strpos($html, 'echo-theme');
        $firstStylesheet = strpos($html, '<link rel="stylesheet"');

        expect($prePaintScript)->not->toBeFalse()
            ->and($firstStylesheet)->not->toBeFalse()
            ->and($prePaintScript)->toBeLessThan($firstStylesheet);
    })->group('feature');

    it('renders the data-theme attribute the dark styles are scoped to', function () {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('data-theme', false);
    })->group('feature');
});
