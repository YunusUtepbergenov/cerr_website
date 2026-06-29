<?php

use App\Livewire\Admin\AccountantDashboard;
use App\Models\OpenData;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;

uses(RefreshDatabase::class);

describe('Accountant dashboard', function () {
    it('lets an accountant view the overview dashboard', function () {
        $accountant = User::factory()->create(['role' => 'accountant']);

        $this->actingAs($accountant)
            ->get(route('admin.overview'))
            ->assertOk();
    });

    it('also lets an admin view the overview dashboard', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('admin.overview'))
            ->assertOk();
    });

    it('forbids roles without open-data access', function () {
        $editor = User::factory()->create(['role' => 'editor']);

        $this->actingAs($editor)
            ->get(route('admin.overview'))
            ->assertForbidden();
    });

    it('redirects guests to the login page', function () {
        $this->get(route('admin.overview'))->assertRedirect(route('login'));
    });

    it('summarises open-data counts and downloads', function () {
        $accountant = User::factory()->create(['role' => 'accountant']);
        OpenData::factory()->count(3)->create(['is_published' => true]);
        OpenData::factory()->count(2)->create(['is_published' => false]);
        OpenData::query()->update(['download_count' => 4]); // 5 rows × 4

        Livewire::actingAs($accountant)->test(AccountantDashboard::class)
            ->assertViewHas('totalCount', 5)
            ->assertViewHas('publishedCount', 3)
            ->assertViewHas('draftCount', 2)
            ->assertViewHas('totalDownloads', 20);
    });
})->group('feature', 'admin');
