<?php

namespace App\Livewire\Admin\Journals;

use App\Livewire\Concerns\HandlesImageUploads;
use App\Models\Journal;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class JournalIndex extends Component
{
    use HandlesImageUploads, WithFileUploads;

    public ?int $editingId = null;

    public string $title = '';

    public string $link = '';

    public ?string $cover_image = null;

    public $coverUpload = null;

    public string $published_at = '';

    public bool $is_active = true;

    public bool $showForm = false;

    public function mount(): void
    {
        $this->published_at = today()->toDateString();
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'link' => ['required', 'url:http,https', 'max:2048'],
            'published_at' => ['required', 'date'],
            'is_active' => ['boolean'],
            'coverUpload' => [$this->editingId ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ];
    }

    public function startCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $journal = Journal::findOrFail($id);
        $this->editingId = $journal->id;
        $this->title = $journal->title;
        $this->link = $journal->link;
        $this->cover_image = $journal->cover_image;
        $this->published_at = $journal->published_at->toDateString();
        $this->is_active = $journal->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        abort_if(! auth()->user()?->canManageContent(), 403);

        $this->validate();

        $journal = $this->editingId ? Journal::findOrFail($this->editingId) : new Journal;
        $isNew = ! $journal->exists;
        $journal->title = $this->title;
        $journal->link = $this->link;
        $journal->published_at = $this->published_at;
        $journal->is_active = $this->is_active;

        if ($this->coverUpload) {
            $newCover = $this->storeUploadedImage($this->coverUpload, 'journals');
            $this->deleteStoredImage($journal->cover_image);
            $journal->cover_image = $newCover;
        }

        $journal->save();

        $journal->logActivity($isNew ? 'created' : 'updated', array_filter([
            'title' => $journal->getChanges()['title'] ?? null,
            'link' => $journal->getChanges()['link'] ?? null,
        ]));

        session()->flash('status', __('admin.journals.saved_flash'));
        $this->resetForm();
        $this->showForm = false;
    }

    public function delete(int $id): void
    {
        abort_if(! auth()->user()?->canManageContent(), 403);

        $journal = Journal::findOrFail($id);
        $journal->logActivity('deleted', ['title' => $journal->title]);
        $this->deleteStoredImage($journal->cover_image);
        $journal->delete();

        session()->flash('status', __('admin.journals.deleted_flash'));
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->link = '';
        $this->cover_image = null;
        $this->coverUpload = null;
        $this->published_at = today()->toDateString();
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.journals.index', [
            'journals' => Journal::orderByDesc('published_at')->orderByDesc('id')->get(),
        ])->title(__('admin.journals.title_section'));
    }
}
