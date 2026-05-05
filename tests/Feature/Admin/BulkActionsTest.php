<?php

use App\Livewire\Admin\News\NewsIndex;
use App\Models\News;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

describe('Bulk news actions', function () {
    it('publishes selected drafts in bulk', function () {
        $a = News::factory()->create(['status' => 'draft']);
        $b = News::factory()->create(['status' => 'draft']);
        $c = News::factory()->create(['status' => 'draft']);

        Livewire::test(NewsIndex::class)
            ->set('selected', [$a->id, $b->id])
            ->call('bulkPublish');

        expect($a->fresh()->status)->toBe('published');
        expect($b->fresh()->status)->toBe('published');
        expect($c->fresh()->status)->toBe('draft');
    })->group('feature', 'admin');

    it('unpublishes selected items', function () {
        $a = News::factory()->create(['status' => 'published']);

        Livewire::test(NewsIndex::class)
            ->set('selected', [$a->id])
            ->call('bulkUnpublish');

        expect($a->fresh()->status)->toBe('draft');
    })->group('feature', 'admin');

    it('deletes selected items and removes managed cover files', function () {
        Storage::fake('public');
        Storage::disk('public')->put('news/covers/x.jpg', 'fake');
        $a = News::factory()->create();
        $a->translations()->create([
            'lang' => 'kr', 'title' => 't', 'short_description' => 's',
            'content' => '<p>c</p>', 'image_url' => 'news/covers/x.jpg',
        ]);

        Livewire::test(NewsIndex::class)
            ->set('selected', [$a->id])
            ->call('bulkDelete');

        expect(News::find($a->id))->toBeNull();
        Storage::disk('public')->assertMissing('news/covers/x.jpg');
    })->group('feature', 'admin');
});
