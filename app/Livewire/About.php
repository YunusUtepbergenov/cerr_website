<?php

namespace App\Livewire;

use App\Models\Page;
use Livewire\Component;

class About extends Component
{
    public $page;
    public function render()
    {
        $this->page = Page::with('translation')->where('slug', 'objectives')->first();

        return view('livewire.about');
    }
}
