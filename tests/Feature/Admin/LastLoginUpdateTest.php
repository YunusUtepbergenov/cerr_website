<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;

describe('last_login_at tracking', function () {
    it('updates last_login_at on successful login', function () {
        User::factory()->create([
            'email' => 'lastlogin@test.com',
            'password' => Hash::make('secret-pass'),
            'last_login_at' => null,
        ]);

        $this->get('/login');

        Volt::test('auth.login')
            ->set('email', 'lastlogin@test.com')
            ->set('password', 'secret-pass')
            ->call('login');

        $user = User::where('email', 'lastlogin@test.com')->first();
        expect($user->last_login_at)->not->toBeNull();
    })->group('feature', 'admin');
});
