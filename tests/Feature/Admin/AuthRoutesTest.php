<?php

use Illuminate\Support\Facades\Route;

describe('Exposed auth routes', function () {
    it('keeps login and logout as the only named auth routes', function () {
        $names = collect(Route::getRoutes())
            ->map(fn ($route) => $route->getName())
            ->filter()
            ->filter(fn (string $name) => str_contains($name, 'password')
                || in_array($name, ['login', 'logout', 'register'], true)
                || str_starts_with($name, 'verification.'))
            ->unique()
            ->sort()
            ->values()
            ->all();

        expect($names)->toBe(['login', 'logout']);
    })->group('feature', 'admin');

    // These were routed to Volt views that do not exist in this repo, so each
    // one returned a 500 rather than a page - and `register` would have been
    // public self-signup. They must stay unrouted until a view backs them.
    it('does not route registration, password reset or email verification', function (string $path) {
        $this->get($path)->assertNotFound();
    })->with([
        '/register',
        '/forgot-password',
        '/reset-password/some-token',
        '/verify-email',
        '/confirm-password',
    ])->group('feature', 'admin');
});
