<?php

namespace App\Livewire\OpenData;

use App\Models\OpenData;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class OpenDataIndex extends Component
{
    use WithPagination;

    #[Url]
    public string $year = '';

    #[Url]
    public string $quarter = '';

    public function updating($name): void
    {
        if (in_array($name, ['year', 'quarter'], true)) {
            $this->resetPage();
        }
    }

    public function render()
    {
        $entries = OpenData::published()
            ->with('translations')
            ->when($this->year !== '', fn ($q) => $q->where('year', (int) $this->year))
            ->when($this->quarter !== '', fn ($q) => $q->where('quarter', (int) $this->quarter))
            ->orderByDesc('year')
            ->orderByDesc('quarter')
            ->orderByDesc('id')
            ->paginate(10);

        $years = OpenData::published()
            ->select('year')
            ->distinct()
            ->orderByDesc('year')
            ->pluck('year');

        return view('livewire.open-data.open-data-index', [
            'entries' => $entries,
            'years' => $years,
        ]);
    }
}
