<?php

use App\Models\User;

describe('SetAdminLocale middleware', function () {
    it('forces Russian on admin routes regardless of session locale', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        session(['locale' => 'en']);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        expect(app()->getLocale())->toBe('ru');
    })->group('feature', 'admin');

    it('does not change locale on public routes', function () {
        session(['locale' => 'en']);

        $this->get('/')->assertOk();

        expect(app()->getLocale())->toBe('en');
    })->group('feature', 'admin');
});
