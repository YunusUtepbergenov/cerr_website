<?php

use App\Livewire\OpenData\OpenDataIndex;
use App\Models\OpenData;
use Livewire\Livewire;

function makeEntry(array $attrs, string $title = 'Dataset'): OpenData
{
    $entry = OpenData::factory()->create($attrs);
    $entry->translations()->create(['language' => app()->getLocale(), 'title' => $title]);

    return $entry;
}

describe('Open data public page', function () {
    beforeEach(fn () => setAppLocale('uz'));

    it('renders and lists only published datasets', function () {
        makeEntry(['is_published' => true, 'year' => 2025], 'Visible report');
        makeEntry(['is_published' => false, 'year' => 2025], 'Hidden report');

        $this->get(route('open-data.index'))
            ->assertOk()
            ->assertSee('Visible report')
            ->assertDontSee('Hidden report');
    })->group('feature', 'public');

    it('filters by year and quarter', function () {
        makeEntry(['year' => 2024, 'quarter' => 1], 'Old one');
        makeEntry(['year' => 2025, 'quarter' => 3], 'New one');

        Livewire::test(OpenDataIndex::class)
            ->set('year', '2025')
            ->assertSee('New one')->assertDontSee('Old one')
            ->set('quarter', '1')
            ->assertDontSee('New one');
    })->group('feature', 'public');

    it('shows an empty state when nothing matches', function () {
        $this->get(route('open-data.index'))
            ->assertOk()
            ->assertSee(__('messages.open_data_empty'));
    })->group('feature', 'public');
});
