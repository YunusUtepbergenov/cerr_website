<?php

use App\Models\Activity;
use App\Models\News;
use App\Models\User;

describe('Admin dashboard', function () {
    it('shows translated status pills and split stat cards', function () {
        app()->setLocale('ru');
        $admin = User::factory()->create(['role' => 'admin']);
        createNewsWithTranslation(['status' => 'published']);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        // Translated pill, not the raw enum value.
        $response->assertSee('status-published', false);
        $response->assertDontSee('>published<', false);
        // Categories and tags are separate cards now.
        $response->assertDontSee(__('admin.dashboard.categories_tags'));
        $response->assertSee(__('admin.dashboard.categories'));
        $response->assertSee(__('admin.dashboard.tags'));
        // Quick actions present.
        $response->assertSee(route('admin.news.create'));
    })->group('feature', 'admin');

    it('shows recent activity to admins', function () {
        app()->setLocale('ru');
        $admin = User::factory()->create(['role' => 'admin']);
        $news = createNewsWithTranslation();
        Activity::create([
            'user_id' => $admin->id,
            'subject_type' => News::class,
            'subject_id' => $news->id,
            'action' => 'created',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertSee(__('admin.dashboard.recent_activity'));
        $response->assertSee(__('admin.activity.action_created'));
    })->group('feature', 'admin');

    it('falls back to created_at when updated_at is missing', function () {
        app()->setLocale('ru');
        $admin = User::factory()->create(['role' => 'admin']);
        $news = createNewsWithTranslation();
        $news->timestamps = false;
        $news->forceFill(['updated_at' => null, 'created_at' => now()->subHours(3)])->saveQuietly();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertSee(now()->subHours(3)->diffForHumans());
    })->group('feature', 'admin');
});
