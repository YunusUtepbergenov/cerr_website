<?php

namespace App\Livewire\ResearchStaff;

use App\Support\ResearchStaffPrototype;
use Livewire\Component;

class StaffSector extends Component
{
    /** @var array<string, mixed> */
    public array $sector;

    public function mount(string $slug): void
    {
        $sector = ResearchStaffPrototype::findSector($slug);

        if (! $sector) {
            abort(404);
        }

        $this->sector = $sector;
    }

    public function render()
    {
        $name = ResearchStaffPrototype::sectorName($this->sector['key']);

        return view('livewire.research-staff.sector', [
            'name' => $name,
            'sectors' => ResearchStaffPrototype::sectors(),
        ])->layout('components.layouts.app', [
            'title' => $name.' — '.__('staff.title').' — CERR',
            'metaDescription' => __('staff.sector_desc_'.$this->sector['key']),
        ]);
    }
}
