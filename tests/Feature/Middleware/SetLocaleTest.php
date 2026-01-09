<?php

describe('SetLocale Middleware', function () {
    it('sets locale from session', function () {
        session(['locale' => 'en']);

        $this->get('/');

        expect(app()->getLocale())->toBe('en');
    })->group('feature', 'middleware');

    it('falls back to default locale when session not set', function () {
        $this->get('/');

        expect(app()->getLocale())->toBe(config('app.locale'));
    })->group('feature', 'middleware');

    it('applies to all web routes', function () {
        session(['locale' => 'kr']);

        $this->get('/')->assertStatus(200);
        expect(app()->getLocale())->toBe('kr');

        $this->get(route('contact'))->assertStatus(200);
        expect(app()->getLocale())->toBe('kr');
    })->group('feature', 'middleware');

    it('persists locale across multiple requests', function () {
        session(['locale' => 'ru']);

        $this->get('/');
        expect(app()->getLocale())->toBe('ru');

        $this->get(route('contact'));
        expect(app()->getLocale())->toBe('ru');

        $this->get(route('leadership'));
        expect(app()->getLocale())->toBe('ru');
    })->group('feature', 'middleware');

    it('changes locale when session changes', function () {
        session(['locale' => 'uz']);
        $this->get('/');
        expect(app()->getLocale())->toBe('uz');

        session(['locale' => 'en']);
        $this->get('/');
        expect(app()->getLocale())->toBe('en');
    })->group('feature', 'middleware');

    it('handles all supported locales', function (string $locale) {
        session(['locale' => $locale]);

        $this->get('/');

        expect(app()->getLocale())->toBe($locale);
    })->with(['uz', 'kr', 'en', 'ru'])
        ->group('feature', 'middleware');
});
