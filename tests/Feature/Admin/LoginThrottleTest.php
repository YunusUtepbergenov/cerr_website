<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Livewire\Volt\Volt;

/**
 * Brute-force defenses on the login form. Two independent rate limiters:
 * a per-email limiter (stops distributed attacks on one account, even from
 * many IPs) and a per-IP limiter (stops credential stuffing across accounts).
 */
beforeEach(fn () => app()->setLocale('ru'));

const STRONG_PW = 'Str0ng#Pass2026';

it('locks a single account after 5 failed attempts, even with the correct password', function () {
    User::factory()->create(['email' => 'boss@cerr.uz', 'password' => Hash::make(STRONG_PW)]);
    $this->get('/login');

    for ($i = 0; $i < 5; $i++) {
        Volt::test('auth.login')
            ->set('email', 'boss@cerr.uz')
            ->set('password', 'wrong'.$i)
            ->call('login')
            ->assertHasErrors(['email']);
    }

    // 6th attempt with the CORRECT password is still blocked by the throttle.
    Volt::test('auth.login')
        ->set('email', 'boss@cerr.uz')
        ->set('password', STRONG_PW)
        ->call('login')
        ->assertHasErrors(['email']);

    $this->assertGuest();
})->group('feature', 'admin');

it('throttles by IP across many different accounts (credential stuffing)', function () {
    $this->get('/login');

    // 20 failed attempts, each a UNIQUE email so no single email reaches its 5-limit.
    for ($i = 0; $i < 20; $i++) {
        Volt::test('auth.login')
            ->set('email', "user{$i}@x.test")
            ->set('password', 'whatever')
            ->call('login')
            ->assertHasErrors(['email']);
    }

    // A fresh account with the correct password is blocked by the IP limiter.
    User::factory()->create(['email' => 'legit@cerr.uz', 'password' => Hash::make(STRONG_PW)]);
    Volt::test('auth.login')
        ->set('email', 'legit@cerr.uz')
        ->set('password', STRONG_PW)
        ->call('login')
        ->assertHasErrors(['email']);

    $this->assertGuest();
})->group('feature', 'admin');

it('clears both throttle counters on a successful login', function () {
    User::factory()->create(['email' => 'boss@cerr.uz', 'password' => Hash::make(STRONG_PW)]);
    $this->get('/login');

    for ($i = 0; $i < 4; $i++) {
        Volt::test('auth.login')
            ->set('email', 'boss@cerr.uz')
            ->set('password', 'wrong'.$i)
            ->call('login')
            ->assertHasErrors(['email']);
    }

    Volt::test('auth.login')
        ->set('email', 'boss@cerr.uz')
        ->set('password', STRONG_PW)
        ->call('login')
        ->assertHasNoErrors();

    expect(RateLimiter::attempts('login:'.sha1('boss@cerr.uz')))->toBe(0)
        ->and(RateLimiter::attempts('login-ip:'.sha1('127.0.0.1')))->toBe(0);
})->group('feature', 'admin');
