<?php

namespace App\Livewire;

use App\Models\Page;
use Livewire\Component;

class History extends Component
{
    public $page;

    public function render()
    {
        $this->page = Page::with('translation')->where('slug', 'history')->first();

        return view('livewire.history');
    }
}
