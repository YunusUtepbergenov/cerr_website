<?php

namespace App\Livewire\Admin;

use App\Models\Activity;
use App\Models\Category;
use App\Models\News;
use App\Models\OpenData;
use App\Models\Tag;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Dashboard extends Component
{
    public function render()
    {
        $newsCount = News::count();
        $publishedCount = News::where('status', 'published')->count();

        return view('livewire.admin.dashboard', [
            'newsCount' => $newsCount,
            'publishedCount' => $publishedCount,
            'draftCount' => News::where('status', 'draft')->count(),
            'publicationRate' => $newsCount > 0 ? (int) round($publishedCount / $newsCount * 100) : 0,
            'categoryCount' => Category::count(),
            'tagCount' => Tag::count(),
            'pendingOpenData' => OpenData::where('is_published', false)->count(),
            'recentNews' => News::with('translations')->latest('id')->limit(8)->get(),
            'recentActivity' => auth()->user()->canViewActivity()
                ? Activity::with('user')->latest('created_at')->limit(8)->get()
                : collect(),
        ])->title(__('admin.nav.dashboard'));
    }
}
