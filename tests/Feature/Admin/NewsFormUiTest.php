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

    it('renders the sticky command bar with the save button inside the form', function () {
        app()->setLocale('ru');
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.news.create'))
            ->assertOk()
            ->assertSee('editor-bar', false)
            ->assertSee(__('admin.news.save_article'));

        $html = $response->getContent();
        $form = strpos($html, 'id="news-form"');
        $save = strpos($html, 'editor-save');

        expect($form)->not->toBeFalse()
            ->and($save)->toBeGreaterThan($form)
            ->and($save)->toBeLessThan(strpos($html, '</form>', $form));
    })->group('feature', 'admin');

    it('renders the settings as a single inspector panel with language tabs on the paper', function () {
        app()->setLocale('ru');
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.news.create'))
            ->assertOk()
            ->assertSee('editor-inspector', false)
            ->assertSee('doc-paper', false)
            ->assertSeeInOrder(['doc-tab', 'O‘zbekcha', 'Ўзбекча', 'Русский', 'English'], false)
            ->assertDontSee('position: sticky; top: 80px;', false);
    })->group('feature', 'admin');

    it('checks the slug live without binding it live (so title auto-fill still works)', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.news.create'))
            ->assertOk()
            ->assertSee('wire:model="slug"', false)
            ->assertSee('checkSlugAvailability', false)
            ->assertDontSee('wire:model.live.debounce.400ms="slug"', false);
    })->group('feature', 'admin');

    it('opens the edit page of an article with a scheduled publish time', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $news = createNewsWithTranslation([
            'status' => 'auto_publish',
            'scheduled_at' => now()->addDay(),
        ]);

        $this->actingAs($admin)->get(route('admin.news.edit', $news))
            ->assertOk()
            ->assertSee('wire:model="scheduled_at"', false);
    })->group('feature', 'admin');
});
