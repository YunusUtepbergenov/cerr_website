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

    /** @var array<int> */
    public array $selected = [];

    public bool $selectAll = false;

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

    public function updatedSelectAll(bool $value): void
    {
        $this->selected = $value ? $this->currentPageIds() : [];
    }

    private function currentPageIds(): array
    {
        return $this->renderedQuery()->paginate(15)->pluck('id')->all();
    }

    private function renderedQuery()
    {
        $query = News::query()->latest();

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

        $news->logActivity('deleted', ['slug' => $news->slug]);

        foreach ($news->translations as $translation) {
            if ($translation->image_url && str_starts_with($translation->image_url, 'news/')) {
                Storage::disk('public')->delete($translation->image_url);
            }
        }

        $news->delete();

        session()->flash('status', __('admin.news.deleted_flash'));
    }

    public function bulkPublish(): void
    {
        if (! $this->selected) {
            return;
        }

        News::whereIn('id', $this->selected)->update(['status' => 'published']);

        foreach (News::whereIn('id', $this->selected)->get() as $n) {
            $n->logActivity('published', ['status' => $n->status]);
        }

        session()->flash('status', __('admin.bulk.published_count', ['count' => count($this->selected)]));
        $this->selected = [];
        $this->selectAll = false;
    }

    public function bulkUnpublish(): void
    {
        if (! $this->selected) {
            return;
        }

        News::whereIn('id', $this->selected)->update(['status' => 'draft']);

        foreach (News::whereIn('id', $this->selected)->get() as $n) {
            $n->logActivity('unpublished', ['status' => $n->status]);
        }

        session()->flash('status', __('admin.bulk.unpublished_count', ['count' => count($this->selected)]));
        $this->selected = [];
        $this->selectAll = false;
    }

    public function bulkDelete(): void
    {
        if (! $this->selected) {
            return;
        }

        foreach ($this->selected as $id) {
            $this->delete($id);
        }

        $this->selected = [];
        $this->selectAll = false;
    }

    public function render()
    {
        return view('livewire.admin.news.index', [
            'newsList' => $this->renderedQuery()->with(['translations', 'category.translations'])->paginate(15),
            'categories' => Category::with('translations')->get(),
        ]);
    }
}
