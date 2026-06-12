<?php

use App\Models\User;

describe('Admin page titles', function () {
    it('uses translated titles on every admin page', function (string $routeName, string $langKey) {
        app()->setLocale('ru');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route($routeName))
            ->assertOk()
            ->assertSee(__($langKey).' — CERR Admin</title>', false);
    })->with([
        'categories' => ['admin.categories.index', 'admin.categories.title_section'],
        'tags' => ['admin.tags.index', 'admin.tags.title_section'],
        'pages' => ['admin.pages.index', 'admin.pages.title_section'],
        'videos' => ['admin.videos.index', 'admin.videos.title_section'],
        'users' => ['admin.users.index', 'admin.users.title_section'],
        'media' => ['admin.media.index', 'admin.media.title_section'],
        'activity' => ['admin.activity.index', 'admin.activity.title_section'],
    ])->group('feature', 'admin');
});
