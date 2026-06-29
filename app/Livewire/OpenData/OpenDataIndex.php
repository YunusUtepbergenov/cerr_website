<?php

namespace App\Livewire\OpenData;

use App\Models\OpenData;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class OpenDataIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $year = '';

    #[Url]
    public string $quarter = '';

    #[Url]
    public string $sort = 'new';

    public function updating($name): void
    {
        if (in_array($name, ['search', 'year', 'quarter', 'sort'], true)) {
            $this->resetPage();
        }
    }

    public function resetFilters(): void
    {
        $this->reset(['search', 'year', 'quarter']);
        $this->resetPage();
    }

    public function render()
    {
        $query = $this->baseQuery()
            ->with('translations')
            ->when($this->year !== '', fn ($q) => $q->where('year', (int) $this->year))
            ->when($this->quarter !== '', fn ($q) => $q->where('quarter', (int) $this->quarter));

        $this->applySort($query);

        $years = OpenData::published()
            ->select('year')->distinct()->orderByDesc('year')->pluck('year');

        // Year facet counts reflect the search + quarter filters (but not the
        // year filter itself, so the sidebar always shows every year).
        $yearCounts = $this->baseQuery()
            ->when($this->quarter !== '', fn ($q) => $q->where('quarter', (int) $this->quarter))
            ->select('year', DB::raw('count(*) as c'))
            ->groupBy('year')
            ->pluck('c', 'year');

        return view('livewire.open-data.open-data-index', [
            'entries' => $query->paginate(10)->onEachSide(1),
            'years' => $years,
            'yearCounts' => $yearCounts,
        ]);
    }

    /**
     * Published datasets narrowed by the free-text search (matched against any
     * translated title), case-insensitively on both Postgres and SQLite.
     */
    private function baseQuery()
    {
        return OpenData::published()
            ->when($this->search !== '', function ($q) {
                $op = DB::connection()->getDriverName() === 'pgsql' ? 'ilike' : 'like';
                $q->whereHas('translations', fn ($t) => $t->where('title', $op, '%'.trim($this->search).'%'));
            });
    }

    private function applySort($query): void
    {
        match ($this->sort) {
            'old' => $query->orderBy('year')->orderBy('quarter')->orderBy('id'),
            'popular' => $query->orderByDesc('download_count')->orderByDesc('year')->orderByDesc('id'),
            default => $query->orderByDesc('year')->orderByDesc('quarter')->orderByDesc('id'),
        };
    }
}
