<?php

namespace App\Livewire\Admin;

use App\Models\News;
use Livewire\Component;
use Livewire\Attributes\{Title};

class Post extends Component
{
    #[Title('Admin Post Page')] 
    public function render()
    {
        return view('livewire.admin.post', [
            'news_list' => News::all()
        ]);
    }
}
