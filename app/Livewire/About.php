<?php

namespace App\Livewire;

use App\Models\Page;
use Livewire\Component;

class About extends Component
{
    public $page;

    public function mount()
    {
        $this->page = Page::with('translation')->where('slug', 'objectives')->first();

        if (! $this->page || ! $this->page->translation) {
            abort(404);
        }
    }

    public function render()
    {
        return view('livewire.about');
    }
}
