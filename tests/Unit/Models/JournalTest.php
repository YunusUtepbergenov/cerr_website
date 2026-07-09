<?php

use App\Models\Journal;
use Illuminate\Support\Carbon;

describe('Journal Model', function () {
    it('active scope returns only active journals', function () {
        Journal::factory()->create();
        Journal::factory()->inactive()->create();

        expect(Journal::active()->count())->toBe(1);
    })->group('unit', 'models');

    it('coverUrl resolves a stored path', function () {
        $journal = Journal::factory()->make(['cover_image' => 'journals/x.jpg']);

        expect($journal->coverUrl())->toContain('journals/x.jpg');
    })->group('unit', 'models');

    it('coverUrl is null when there is no cover', function () {
        $journal = Journal::factory()->make(['cover_image' => '']);

        expect($journal->coverUrl())->toBeNull();
    })->group('unit', 'models');

    it('casts published_at to a date and is_active to a bool', function () {
        $journal = Journal::factory()->create(['is_active' => 1]);

        expect($journal->published_at)->toBeInstanceOf(Carbon::class)
            ->and($journal->is_active)->toBeTrue();
    })->group('unit', 'models');
});
