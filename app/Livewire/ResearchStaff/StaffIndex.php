<?php

namespace App\Livewire\ResearchStaff;

use App\Support\ResearchStaffPrototype;
use Livewire\Component;

class StaffIndex extends Component
{
    public function render()
    {
        return view('livewire.research-staff.index', [
            'sectors' => ResearchStaffPrototype::sectors(),
        ])->layout('components.layouts.app', [
            'title' => __('staff.title').' — CERR',
            'metaDescription' => __('staff.lead'),
        ]);
    }
}
