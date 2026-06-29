<?php

use App\Livewire\Admin\AccountSettings;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('Account settings', function () {
    it('is reachable by any authenticated role, including accountants', function () {
        $accountant = User::factory()->create(['role' => 'accountant']);

        $this->actingAs($accountant)
            ->get(route('admin.settings'))
            ->assertOk();
    });

    it('also lets a content role (editor) open settings', function () {
        $editor = User::factory()->create(['role' => 'editor']);

        $this->actingAs($editor)
            ->get(route('admin.settings'))
            ->assertOk();
    });

    // No remaining role lacks panel access, so this guards deny-by-default:
    // a user with a removed / unrecognized role ('viewer') is still forbidden.
    it('forbids a user with a removed / unrecognized role', function () {
        $user = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($user)
            ->get(route('admin.settings'))
            ->assertForbidden();
    });

    it('redirects guests to the login page', function () {
        $this->get(route('admin.settings'))->assertRedirect(route('login'));
    });

    it('updates the profile name and email', function () {
        $user = User::factory()->create(['name' => 'Old', 'email' => 'old@x.test']);

        Livewire::actingAs($user)->test(AccountSettings::class)
            ->set('name', 'New Name')
            ->set('email', 'new@x.test')
            ->call('updateProfile')
            ->assertHasNoErrors();

        $user->refresh();
        expect($user->name)->toBe('New Name')
            ->and($user->email)->toBe('new@x.test');
    });

    it('clears email verification when the email changes', function () {
        $user = User::factory()->create(['email' => 'old@x.test', 'email_verified_at' => now()]);

        Livewire::actingAs($user)->test(AccountSettings::class)
            ->set('email', 'changed@x.test')
            ->call('updateProfile')
            ->assertHasNoErrors();

        expect($user->refresh()->email_verified_at)->toBeNull();
    });

    it('keeps email verification when the email is unchanged', function () {
        $user = User::factory()->create(['email_verified_at' => now()]);

        Livewire::actingAs($user)->test(AccountSettings::class)
            ->set('name', 'Renamed')
            ->call('updateProfile')
            ->assertHasNoErrors();

        expect($user->refresh()->email_verified_at)->not->toBeNull();
    });

    it('rejects an email already used by another user', function () {
        User::factory()->create(['email' => 'taken@x.test']);
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(AccountSettings::class)
            ->set('email', 'taken@x.test')
            ->call('updateProfile')
            ->assertHasErrors('email');
    });

    it('changes the password when the current password is correct', function () {
        $user = User::factory()->create(); // factory password is 'password'

        Livewire::actingAs($user)->test(AccountSettings::class)
            ->set('current_password', 'password')
            ->set('password', 'Str0ng#Pass2026')
            ->set('password_confirmation', 'Str0ng#Pass2026')
            ->call('updatePassword')
            ->assertHasNoErrors();

        expect(Hash::check('Str0ng#Pass2026', $user->refresh()->password))->toBeTrue();
    });

    it('rejects a wrong current password', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(AccountSettings::class)
            ->set('current_password', 'not-the-password')
            ->set('password', 'Str0ng#Pass2026')
            ->set('password_confirmation', 'Str0ng#Pass2026')
            ->call('updatePassword')
            ->assertHasErrors('current_password');

        expect(Hash::check('Str0ng#Pass2026', $user->refresh()->password))->toBeFalse();
    });

    it('rejects a new password that fails the strength policy', function () {
        $user = User::factory()->create();

        Livewire::actingAs($user)->test(AccountSettings::class)
            ->set('current_password', 'password')
            ->set('password', 'weak')
            ->set('password_confirmation', 'weak')
            ->call('updatePassword')
            ->assertHasErrors('password');
    });
})->group('feature', 'admin');
