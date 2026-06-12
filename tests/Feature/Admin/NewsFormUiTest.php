<?php

use App\Models\Tag;
use App\Models\User;

describe('News form UI', function () {
    it('uses a translated page title on create and edit', function () {
        app()->setLocale('ru');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.news.create'))
            ->assertOk()
            ->assertDontSee('News form</title>', false)
            ->assertSee(__('admin.news.new_article').' — CERR Admin</title>', false);

        $news = createNewsWithTranslation();
        $this->actingAs($admin)->get(route('admin.news.edit', $news))
            ->assertOk()
            ->assertSee(__('admin.news.edit_article').' — CERR Admin</title>', false);
    })->group('feature', 'admin');

    it('renders tags as toggle chips', function () {
        app()->setLocale('ru');
        $admin = User::factory()->create(['role' => 'admin']);
        Tag::factory()->create(['name' => 'economy']);

        $this->actingAs($admin)->get(route('admin.news.create'))
            ->assertOk()
            ->assertSee('tag-chip-list', false);
    })->group('feature', 'admin');

    it('renders the sticky form action bar', function () {
        app()->setLocale('ru');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.news.create'))
            ->assertOk()
            ->assertSee('form-action-bar', false);
    })->group('feature', 'admin');
});
