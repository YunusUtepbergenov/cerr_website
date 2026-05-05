<?php

namespace App\Livewire\Admin\Videos;

use App\Livewire\Concerns\HandlesImageUploads;
use App\Models\Video;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
#[Title('Видео')]
class VideoIndex extends Component
{
    use HandlesImageUploads, WithFileUploads;

    public ?int $editingId = null;

    public string $title = '';

    public string $url = '';

    public ?string $image = null;

    public $imageUpload = null;

    public bool $showForm = false;

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:500'],
            'imageUpload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ];
    }

    public function startCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $v = Video::findOrFail($id);
        $this->editingId = $v->id;
        $this->title = $v->title;
        $this->url = $v->url;
        $this->image = $v->image;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $v = $this->editingId ? Video::findOrFail($this->editingId) : new Video;
        $v->title = $this->title;
        $v->url = $this->url;
        if ($this->imageUpload) {
            $newImage = $this->storeUploadedImage($this->imageUpload, 'videos');
            $this->deleteStoredImage($v->image);
            $v->image = $newImage;
        }
        $v->save();

        session()->flash('status', __('admin.videos.saved_flash'));
        $this->resetForm();
        $this->showForm = false;
    }

    public function delete(int $id): void
    {
        $v = Video::findOrFail($id);
        $this->deleteStoredImage($v->image);
        $v->delete();
        session()->flash('status', __('admin.videos.deleted_flash'));
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
        $this->url = '';
        $this->image = null;
        $this->imageUpload = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.videos.index', [
            'videos' => Video::orderBy('id', 'desc')->get(),
        ]);
    }
}
