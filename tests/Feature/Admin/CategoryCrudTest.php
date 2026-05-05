<?php

use App\Livewire\Admin\Categories\CategoryIndex;
use App\Models\Category;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

describe('Category CRUD', function () {
    it('creates a category with translations', function () {
        Livewire::test(CategoryIndex::class)
            ->call('startCreate')
            ->set('slug', 'press')
            ->set('names.kr', 'Press KR')
            ->set('names.uz', 'Press UZ')
            ->set('names.ru', 'Press RU')
            ->set('names.en', 'Press EN')
            ->call('save')
            ->assertHasNoErrors();

        $cat = Category::where('slug', 'press')->first();
        expect($cat)->not->toBeNull()
            ->and($cat->translations()->count())->toBe(4);
    })->group('feature', 'admin');

    it('rejects duplicate slug', function () {
        Category::factory()->create(['slug' => 'press']);

        Livewire::test(CategoryIndex::class)
            ->call('startCreate')
            ->set('slug', 'press')
            ->set('names.kr', 'X')
            ->call('save')
            ->assertHasErrors(['slug']);
    })->group('feature', 'admin');

    it('edits an existing category', function () {
        $cat = Category::factory()->create(['slug' => 'edit-me']);
        $cat->translations()->create(['language' => 'kr', 'name' => 'Old']);

        Livewire::test(CategoryIndex::class)
            ->call('edit', $cat->id)
            ->assertSet('slug', 'edit-me')
            ->assertSet('names.kr', 'Old')
            ->set('names.kr', 'New')
            ->call('save')
            ->assertHasNoErrors();

        expect($cat->fresh()->translations()->where('language', 'kr')->first()->name)->toBe('New');
    })->group('feature', 'admin');
});
