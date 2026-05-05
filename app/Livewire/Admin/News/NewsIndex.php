<?php

namespace App\Livewire\Admin\News;

use App\Models\Category;
use App\Models\News;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('News')]
class NewsIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $status = '';

    #[Url(except: '')]
    public string $category = '';

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    public function updatingStatus(): void
    {
        $this->resetPage();
    }

    public function updatingCategory(): void
    {
        $this->resetPage();
    }

    public function delete(int $id): void
    {
        $news = News::with('translations')->findOrFail($id);

        foreach ($news->translations as $translation) {
            if ($translation->image_url && str_starts_with($translation->image_url, 'news/')) {
                Storage::disk('public')->delete($translation->image_url);
            }
        }

        $news->delete();

        session()->flash('status', 'News deleted.');
    }

    public function render()
    {
        $query = News::query()
            ->with(['translations', 'category.translations'])
            ->latest();

        if ($this->status !== '') {
            $query->where('status', $this->status);
        }

        if ($this->category !== '') {
            $query->where('category_id', $this->category);
        }

        if ($this->search !== '') {
            $query->where(function ($q) {
                $q->where('slug', 'like', '%'.$this->search.'%')
                    ->orWhereHas('translations', fn ($t) => $t->where('title', 'like', '%'.$this->search.'%'));
            });
        }

        return view('livewire.admin.news.index', [
            'newsList' => $query->paginate(15),
            'categories' => Category::with('translations')->get(),
        ]);
    }
}
