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

    it('renders the left-facet catalog with sidebar filters, file badges and download links', function () {
        makeEntry(['is_published' => true, 'year' => 2025, 'quarter' => 2], 'GDP report');

        $this->get(route('open-data.index'))
            ->assertOk()
            ->assertSee('od-catalog', false)
            ->assertSee('od-rail', false)
            ->assertSee('od-card', false)
            ->assertSee('od-badge', false)
            ->assertSee('od-dl', false)
            ->assertSee(__('messages.open_data_year'), false)
            ->assertSee(__('messages.open_data_quarters_all'), false)
            ->assertSee('GDP report')
            ->assertSee(__('messages.open_data_download'));
    })->group('feature', 'public');

    it('filters datasets by a title search', function () {
        makeEntry(['year' => 2025], 'Уникальный отчёт');
        makeEntry(['year' => 2025], 'Другой набор данных');

        Livewire::test(OpenDataIndex::class)
            ->set('search', 'Уникальный')
            ->assertSee('Уникальный отчёт')
            ->assertDontSee('Другой набор данных');
    })->group('feature', 'public');

    it('shows per-year dataset counts in the sidebar', function () {
        makeEntry(['year' => 2025]);
        makeEntry(['year' => 2025]);
        makeEntry(['year' => 2024]);

        Livewire::test(OpenDataIndex::class)
            ->assertViewHas('yearCounts', fn ($c) => (int) ($c[2025] ?? 0) === 2 && (int) ($c[2024] ?? 0) === 1);
    })->group('feature', 'public');

    it('sorts datasets by downloads when requested', function () {
        makeEntry(['year' => 2025, 'download_count' => 5], 'Low');
        $popular = makeEntry(['year' => 2025, 'download_count' => 99], 'High');

        Livewire::test(OpenDataIndex::class)
            ->set('sort', 'popular')
            ->assertViewHas('entries', fn ($e) => $e->first()->id === $popular->id);
    })->group('feature', 'public');

    it('resets all sidebar filters', function () {
        Livewire::test(OpenDataIndex::class)
            ->set('year', '2025')->set('quarter', '2')->set('search', 'x')
            ->call('resetFilters')
            ->assertSet('year', '')
            ->assertSet('quarter', '')
            ->assertSet('search', '');
    })->group('feature', 'public');
});
