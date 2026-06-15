<?php

use App\Livewire\Admin\Users\UserIndex;
use App\Models\User;
use Livewire\Livewire;

describe('Accountant role', function () {
    it('exposes accountant role helpers', function () {
        $accountant = User::factory()->create(['role' => 'accountant']);

        // Accountant is segregated: NOT in canAccessAdmin (the general admin
        // gate), but has the dedicated open-data capability.
        expect($accountant->isAccountant())->toBeTrue()
            ->and($accountant->canManageOpenData())->toBeTrue()
            ->and($accountant->canAccessAdmin())->toBeFalse()
            ->and($accountant->canManageContent())->toBeFalse()
            ->and($accountant->canManageUsers())->toBeFalse();
    });

    it('lets admins manage open data but not editors', function () {
        expect(User::factory()->make(['role' => 'admin'])->canManageOpenData())->toBeTrue()
            ->and(User::factory()->make(['role' => 'editor'])->canManageOpenData())->toBeFalse()
            ->and(User::factory()->make(['role' => 'writer'])->canManageOpenData())->toBeFalse();
    });

    it('allows creating a user with the accountant role', function () {
        app()->setLocale('ru');
        $admin = User::factory()->create(['role' => 'admin']);

        Livewire::actingAs($admin)->test(UserIndex::class)
            ->call('startCreate')
            ->set('name', 'Buxgalter')
            ->set('email', 'acc@x.test')
            ->set('role', 'accountant')
            ->set('password', 'secret-pass')
            ->call('save')
            ->assertHasNoErrors();

        expect(User::where('email', 'acc@x.test')->value('role'))->toBe('accountant');
    });
})->group('feature', 'admin');
