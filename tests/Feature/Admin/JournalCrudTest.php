<?php

use App\Livewire\Admin\Journals\JournalIndex;
use App\Models\Journal;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

describe('Journal CRUD', function () {
    it('creates a journal with an uploaded cover', function () {
        Storage::fake('public');

        Livewire::test(JournalIndex::class)
            ->call('startCreate')
            ->set('title', 'Iqtisodiy sharh 8(44)')
            ->set('link', 'https://review.uz/journals/view/8-44-2025')
            ->set('published_at', '2025-08-01')
            ->set('coverUpload', UploadedFile::fake()->image('cover.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        expect(Journal::count())->toBe(1)
            ->and(Journal::first()->cover_image)->toStartWith('journals/');
    })->group('feature', 'admin');

    it('requires a title', function () {
        Storage::fake('public');

        Livewire::test(JournalIndex::class)
            ->call('startCreate')
            ->set('link', 'https://review.uz')
            ->set('coverUpload', UploadedFile::fake()->image('c.jpg'))
            ->call('save')
            ->assertHasErrors(['title']);
    })->group('feature', 'admin');

    it('requires a valid link URL', function () {
        Storage::fake('public');

        Livewire::test(JournalIndex::class)
            ->call('startCreate')
            ->set('title', 'X')
            ->set('link', 'not-a-url')
            ->set('coverUpload', UploadedFile::fake()->image('c.jpg'))
            ->call('save')
            ->assertHasErrors(['link']);
    })->group('feature', 'admin');

    it('rejects a link with a non-http scheme', function () {
        Storage::fake('public');

        Livewire::test(JournalIndex::class)
            ->call('startCreate')
            ->set('title', 'X')
            ->set('link', 'javascript://alert(1)')
            ->set('coverUpload', UploadedFile::fake()->image('c.jpg'))
            ->call('save')
            ->assertHasErrors(['link']);
    })->group('feature', 'admin');

    it('requires a cover image when creating', function () {
        Livewire::test(JournalIndex::class)
            ->call('startCreate')
            ->set('title', 'X')
            ->set('link', 'https://review.uz')
            ->call('save')
            ->assertHasErrors(['coverUpload']);
    })->group('feature', 'admin');

    it('keeps the existing cover on edit when no new file is uploaded', function () {
        Storage::fake('public');
        Storage::disk('public')->put('journals/old.jpg', 'fake');
        $journal = Journal::factory()->create(['cover_image' => 'journals/old.jpg']);

        Livewire::test(JournalIndex::class)
            ->call('edit', $journal->id)
            ->set('title', 'Updated title')
            ->call('save')
            ->assertHasNoErrors();

        expect($journal->fresh()->cover_image)->toBe('journals/old.jpg')
            ->and($journal->fresh()->title)->toBe('Updated title');
    })->group('feature', 'admin');

    it('replaces the cover and deletes the old file on edit', function () {
        Storage::fake('public');
        Storage::disk('public')->put('journals/old.jpg', 'fake');
        $journal = Journal::factory()->create(['cover_image' => 'journals/old.jpg']);

        Livewire::test(JournalIndex::class)
            ->call('edit', $journal->id)
            ->set('coverUpload', UploadedFile::fake()->image('new.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        expect($journal->fresh()->cover_image)->toStartWith('journals/')
            ->and($journal->fresh()->cover_image)->not->toBe('journals/old.jpg');
        Storage::disk('public')->assertMissing('journals/old.jpg');
    })->group('feature', 'admin');

    it('deletes a journal and its cover file', function () {
        Storage::fake('public');
        Storage::disk('public')->put('journals/c.jpg', 'fake');
        $journal = Journal::factory()->create(['cover_image' => 'journals/c.jpg']);

        Livewire::test(JournalIndex::class)->call('delete', $journal->id);

        expect(Journal::find($journal->id))->toBeNull();
        Storage::disk('public')->assertMissing('journals/c.jpg');
    })->group('feature', 'admin');

    it('lists journals newest first', function () {
        Journal::factory()->create(['title' => 'Older issue', 'published_at' => '2025-01-01']);
        Journal::factory()->create(['title' => 'Newer issue', 'published_at' => '2025-12-01']);

        Livewire::test(JournalIndex::class)
            ->assertSeeInOrder(['Newer issue', 'Older issue']);
    })->group('feature', 'admin');
});
