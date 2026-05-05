<?php

use App\Livewire\Admin\Tags\TagIndex;
use App\Models\Tag;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

describe('Tag CRUD', function () {
    it('creates a tag', function () {
        Livewire::test(TagIndex::class)
            ->call('startCreate')
            ->set('name', 'economy')
            ->call('save')
            ->assertHasNoErrors();

        expect(Tag::where('name', 'economy')->exists())->toBeTrue();
    })->group('feature', 'admin');

    it('rejects duplicate name', function () {
        Tag::factory()->create(['name' => 'taken']);

        Livewire::test(TagIndex::class)
            ->call('startCreate')
            ->set('name', 'taken')
            ->call('save')
            ->assertHasErrors(['name']);
    })->group('feature', 'admin');

    it('deletes a tag', function () {
        $tag = Tag::factory()->create();

        Livewire::test(TagIndex::class)
            ->call('delete', $tag->id);

        expect(Tag::find($tag->id))->toBeNull();
    })->group('feature', 'admin');
});
