<?php

namespace App\Livewire\Admin\Tags;

use App\Models\Tag;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Tags')]
class TagIndex extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public bool $showForm = false;

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
        $tag->name = $this->name;
        $tag->save();

        session()->flash('status', 'Tag saved.');
        $this->showForm = false;
        $this->editingId = null;
        $this->name = '';
    }

    public function delete(int $id): void
    {
        Tag::findOrFail($id)->delete();
        session()->flash('status', 'Tag deleted.');
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
            'tags' => Tag::withCount('news')->orderBy('name')->get(),
        ]);
    }
}
