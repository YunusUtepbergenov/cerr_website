<?php

use App\Models\News;
use App\Models\User;

describe('Auto-slug behavior', function () {
    it('creates slug attribute is wired with auto-fill on the create form', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.news.create'));

        $response->assertOk()
            ->assertSee('manuallyEdited: false', false)
            // The watcher follows the active language tab, not a fixed locale.
            ->assertSee('$watch(() => $wire.translations[$wire.activeLocale]?.title', false);
    })->group('feature', 'admin');

    it('disables auto-fill on the edit form', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $news = News::factory()->create(['slug' => 'existing-slug', 'scheduled_at' => null]);

        $response = $this->actingAs($admin)->get(route('admin.news.edit', $news));

        $response->assertOk()
            ->assertSee('manuallyEdited: true', false);
    })->group('feature', 'admin');
});
