<?php

namespace App\Livewire\ResearchStaff;

use App\Support\ResearchStaffPrototype;
use Livewire\Component;

class StaffIndex extends Component
{
    public function render()
    {
        $sectors = ResearchStaffPrototype::sectors();
        $people = ResearchStaffPrototype::people();

        return view('livewire.research-staff.index', [
            'sectors' => $sectors,
            'totalPeople' => $people->count(),
            'withDegree' => $people->whereNotNull('degree')->count(),
        ])->layout('components.layouts.app', [
            'title' => __('staff.title').' — CERR',
            'metaDescription' => __('staff.lead'),
        ]);
    }
}
