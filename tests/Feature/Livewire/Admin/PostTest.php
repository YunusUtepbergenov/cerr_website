<?php

use App\Livewire\Admin\Post;
use Livewire\Livewire;

describe('Admin Post Component', function () {
    it('loads all news', function () {
        setAppLocale('uz');
        createNewsWithTranslation();
        createNewsWithTranslation();

        Livewire::test(Post::class)
            ->assertViewHas('news_list', fn ($val) => $val->count() === 2);
    })->group('feature', 'livewire', 'admin');

    it('renders successfully', function () {
        Livewire::test(Post::class)
            ->assertStatus(200);
    })->group('feature', 'livewire', 'admin');
});
