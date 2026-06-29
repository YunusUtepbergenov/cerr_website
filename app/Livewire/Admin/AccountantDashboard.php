<?php

namespace App\Livewire\Admin;

use App\Models\OpenData;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class AccountantDashboard extends Component
{
    public function render()
    {
        $totalCount = OpenData::count();
        $publishedCount = OpenData::where('is_published', true)->count();

        return view('livewire.admin.accountant-dashboard', [
            'totalCount' => $totalCount,
            'publishedCount' => $publishedCount,
            'draftCount' => OpenData::where('is_published', false)->count(),
            'totalDownloads' => (int) OpenData::sum('download_count'),
            'recentEntries' => OpenData::with('translations')->latest('id')->limit(8)->get(),
        ])->title(__('admin.accountant.title'));
    }
}
