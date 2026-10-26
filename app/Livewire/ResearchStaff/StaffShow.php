<?php

namespace App\Livewire\ResearchStaff;

use App\Support\ResearchStaffPrototype;
use Livewire\Component;

class StaffShow extends Component
{
    /** @var array<string, mixed> */
    public array $person;

    /** @var array{key: string, slug: string, accent: string} */
    public array $sector;

    public function mount(string $slug): void
    {
        $person = ResearchStaffPrototype::find($slug);
        $sector = $person ? ResearchStaffPrototype::sector($person['sector']) : null;

        if (! $person || ! $sector) {
            abort(404);
        }

        $this->person = $person;
        $this->sector = $sector;
    }

    public function render()
    {
        $colleagues = ResearchStaffPrototype::people()
            ->where('sector', $this->person['sector'])
            ->where('slug', '!=', $this->person['slug'])
            ->sortByDesc('is_head')
            ->take(5)
            ->values();

        $name = ResearchStaffPrototype::text($this->person['name']);

        return view('livewire.research-staff.show', [
            'colleagues' => $colleagues,
            'name' => $name,
        ])->layout('components.layouts.app', [
            'title' => $name.' — '.__('staff.title').' — CERR',
            'metaDescription' => rtrim(__('staff.position_'.$this->person['position']).', '.__('structure.'.$this->sector['key']).'. '.implode(', ', ResearchStaffPrototype::items($this->person['research_areas'])), '. ').'.',
        ]);
    }
}
