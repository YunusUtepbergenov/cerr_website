<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;

describe('Accountant admin chrome', function () {
    beforeEach(fn () => app()->setLocale('ru'));

    it('403s an accountant on the dashboard and other admin sections', function () {
        $accountant = User::factory()->create(['role' => 'accountant']);

        $this->actingAs($accountant)->get(route('admin.dashboard'))->assertForbidden();
        $this->actingAs($accountant)->get(route('admin.news.index'))->assertForbidden();
    })->group('feature', 'admin');

    it('shows an accountant only the open data nav item', function () {
        $response = $this->actingAs(User::factory()->create(['role' => 'accountant']))
            ->get(route('admin.open-data.index'));

        $response->assertOk();
        $response->assertSee(route('admin.open-data.index'), false);
        $response->assertDontSee(route('admin.news.index'), false);
        $response->assertDontSee(route('admin.users.index'), false);
        $response->assertDontSee(route('admin.activity.index'), false);
    })->group('feature', 'admin');

    it('shows an admin both open data and the rest', function () {
        $response = $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.open-data.index'));

        $response->assertOk();
        $response->assertSee(route('admin.open-data.index'), false);
        $response->assertSee(route('admin.news.index'), false);
    })->group('feature', 'admin');

    it('redirects an accountant to the overview dashboard on login', function () {
        User::factory()->create([
            'email' => 'acc@cerr.uz',
            'password' => Hash::make('secret-pass'),
            'role' => 'accountant',
        ]);

        $this->get('/login');

        Volt::test('auth.login')
            ->set('email', 'acc@cerr.uz')
            ->set('password', 'secret-pass')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.overview'));
    })->group('feature', 'admin');

    it('sends an accountant to the overview even when a forbidden admin url was intended', function () {
        User::factory()->create([
            'email' => 'acc2@cerr.uz',
            'password' => Hash::make('secret-pass'),
            'role' => 'accountant',
        ]);

        // Reaching /login by first hitting /admin stores it as the intended URL;
        // honoring it would 403 the accountant via EnsureAdmin.
        session()->put('url.intended', route('admin.dashboard'));

        Volt::test('auth.login')
            ->set('email', 'acc2@cerr.uz')
            ->set('password', 'secret-pass')
            ->call('login')
            ->assertHasNoErrors()
            ->assertRedirect(route('admin.overview'));
    })->group('feature', 'admin');
})->group('feature', 'admin');
