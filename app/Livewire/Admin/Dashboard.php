<?php

namespace App\Livewire\Admin;

use App\Models\Category;
use App\Models\News;
use App\Models\Tag;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Dashboard')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard', [
            'newsCount' => News::count(),
            'publishedCount' => News::where('status', 'published')->count(),
            'draftCount' => News::where('status', 'draft')->count(),
            'categoryCount' => Category::count(),
            'tagCount' => Tag::count(),
            'recentNews' => News::with('translations')->latest()->limit(8)->get(),
        ]);
    }
}
