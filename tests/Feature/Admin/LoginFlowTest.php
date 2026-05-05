<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;

describe('Login flow', function () {
    it('renders the login page', function () {
        $this->get('/login')->assertOk()->assertSee('Sign in');
    })->group('feature', 'admin');

    it('logs in an admin and redirects to /admin', function () {
        User::factory()->create([
            'email' => 'boss@cerr.uz',
            'password' => Hash::make('secret-pass'),
            'role' => 'admin',
        ]);

        $this->get('/login');

        Volt::test('auth.login')
            ->set('email', 'boss@cerr.uz')
            ->set('password', 'secret-pass')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.dashboard'));

        $this->assertAuthenticated();
    })->group('feature', 'admin');

    it('logs in a non-admin and redirects to home', function () {
        User::factory()->create([
            'email' => 'reader@cerr.uz',
            'password' => Hash::make('secret-pass'),
            'role' => 'viewer',
        ]);

        $this->get('/login');

        Volt::test('auth.login')
            ->set('email', 'reader@cerr.uz')
            ->set('password', 'secret-pass')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect('/');

        $this->assertAuthenticated();
    })->group('feature', 'admin');

    it('rejects bad credentials', function () {
        User::factory()->create([
            'email' => 'boss@cerr.uz',
            'password' => Hash::make('secret-pass'),
        ]);

        $this->get('/login');

        Volt::test('auth.login')
            ->set('email', 'boss@cerr.uz')
            ->set('password', 'wrong-password')
            ->call('login')
            ->assertHasErrors(['email']);

        $this->assertGuest();
    })->group('feature', 'admin');
});
