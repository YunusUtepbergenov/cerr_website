<?php

use App\Models\User;

describe('News index UI', function () {
    it('shows translated status pills and the translated page title', function () {
        app()->setLocale('ru');
        $admin = User::factory()->create(['role' => 'admin']);
        createNewsWithTranslation(['status' => 'draft']);

        $response = $this->actingAs($admin)->get(route('admin.news.index'));

        $response->assertOk();
        $response->assertSee('status-draft', false);
        $response->assertDontSee('>draft<', false);
        // Breadcrumb/title no longer the raw English class title.
        $response->assertDontSee('News</title>', false);
        $response->assertSee(__('admin.news.title_section').' — CERR Admin</title>', false);
    })->group('feature', 'admin');

    it('renders the category as a badge when present', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        app()->setLocale('ru');
        $category = createCategoryWithTranslation();
        createNewsWithTranslation(['category_id' => $category->id]);

        $response = $this->actingAs($admin)->get(route('admin.news.index'));

        $response->assertOk();
        $response->assertSee('cat-badge', false);
    })->group('feature', 'admin');
});
