<?php

namespace App\Livewire\Admin\Tags;

use App\Models\Tag;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
class TagIndex extends Component
{
    use WithPagination;

    public const PER_PAGE = 20;

    #[Url(except: '')]
    public string $search = '';

    public ?int $editingId = null;

    public string $name = '';

    public bool $showForm = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255', Rule::unique('tags', 'name')->ignore($this->editingId)],
        ];
    }

    public function startCreate(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->resetValidation();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $tag = Tag::findOrFail($id);
        $this->editingId = $tag->id;
        $this->name = $tag->name;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $tag = $this->editingId ? Tag::findOrFail($this->editingId) : new Tag;
        $isNew = ! $tag->exists;
        $tag->name = $this->name;
        $tag->save();

        $changes = array_filter(['name' => $tag->getChanges()['name'] ?? null]);
        $tag->logActivity($isNew ? 'created' : 'updated', $changes);

        session()->flash('status', __('admin.tags.saved_flash'));
        $this->showForm = false;
        $this->editingId = null;
        $this->name = '';
    }

    public function delete(int $id): void
    {
        $tag = Tag::findOrFail($id);
        $tag->logActivity('deleted', ['name' => $tag->name]);
        $tag->delete();
        session()->flash('status', __('admin.tags.deleted_flash'));
    }

    public function cancel(): void
    {
        $this->showForm = false;
        $this->editingId = null;
        $this->name = '';
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.tags.index', [
            'tags' => Tag::withCount('news')
                ->when($this->search !== '', fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
                ->orderBy('name')
                ->paginate(self::PER_PAGE),
        ])->title(__('admin.tags.title_section'));
    }
}
