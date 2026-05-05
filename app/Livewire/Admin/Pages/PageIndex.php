<?php

namespace App\Livewire\Admin\Pages;

use App\Models\Page;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Страницы')]
class PageIndex extends Component
{
    public function delete(int $id): void
    {
        $page = Page::with('translations')->findOrFail($id);

        if ($page->image) {
            Storage::disk('public')->delete($page->image);
        }

        $page->delete();

        session()->flash('status', __('admin.pages.deleted_flash'));
    }

    public function render()
    {
        return view('livewire.admin.pages.index', [
            'pages' => Page::with('translations')->orderBy('id')->get(),
        ]);
    }
}
