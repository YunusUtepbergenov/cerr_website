<?php

use App\Livewire\ShowAllCategories;
use App\Models\Category;
use Livewire\Livewire;

describe('ShowAllCategories Component', function () {
    it('loads first category only (not all - misleading name)', function () {
        setAppLocale('uz');
        $first = createCategoryWithTranslation(['slug' => 'first']);
        $second = createCategoryWithTranslation(['slug' => 'second']);

        Livewire::test(ShowAllCategories::class)
            ->assertSet('category', fn ($val) => $val->id === $first->id);
    })->group('feature', 'livewire', 'known-issues');

    it('aborts 404 when no categories exist', function () {
        $this->get(route('show.all.category'))
            ->assertNotFound();
    })->group('feature', 'livewire', 'known-issues');

    it('aborts 404 when first category has no translation', function () {
        setAppLocale('uz');
        Category::factory()->create(); // No translation

        $this->get(route('show.all.category'))
            ->assertNotFound();
    })->group('feature', 'livewire', 'known-issues');

    it('loads popular news', function () {
        setAppLocale('uz');
        createCategoryWithTranslation();
        createNewsWithTranslation(['view_count' => 100]);

        Livewire::test(ShowAllCategories::class)
            ->assertSet('popular_news', fn ($val) => $val->count() > 0);
    })->group('feature', 'livewire', 'known-issues');
});
