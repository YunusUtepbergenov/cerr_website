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

    private function renderedQuery()
    {
        $query = News::query()->latest();

        if (auth()->user()->isWriter()) {
            $query->where('user_id', auth()->id());
        }

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

        return $query;
    }

    public function delete(int $id): void
    {
        $news = News::with('translations')->findOrFail($id);

        abort_if(! auth()->user()->canEditNews($news), 403);

        $news->logActivity('deleted', ['slug' => $news->slug]);

        foreach ($news->translations as $translation) {
            if ($translation->image_url && str_starts_with($translation->image_url, 'news/')) {
                Storage::disk('public')->delete($translation->image_url);
            }
        }

        $news->delete();

        session()->flash('status', __('admin.news.deleted_flash'));
    }

    public function render()
    {
        return view('livewire.admin.news.index', [
            'newsList' => $this->renderedQuery()->with(['translations', 'category.translations'])->paginate(15),
            'categories' => Category::with('translations')->get(),
        ]);
    }
}
