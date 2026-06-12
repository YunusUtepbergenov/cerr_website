<?php

namespace App\Livewire\Admin\Media;

use App\Livewire\Concerns\HandlesImageUploads;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class MediaIndex extends Component
{
    use HandlesImageUploads, WithFileUploads;

    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $folder = '';

    public array $uploads = [];

    public string $uploadFolder = 'news/covers';

    protected function rules(): array
    {
        return [
            'uploads.*' => ['image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'uploadFolder' => [Rule::in(['news/covers', 'news/inline', 'pages', 'videos'])],
        ];
    }

    public function save(): void
    {
        $this->validate();

        $count = 0;
        foreach ($this->uploads as $upload) {
            $this->storeUploadedImage($upload, $this->uploadFolder);
            $count++;
        }

        $this->uploads = [];
        session()->flash('status', __('admin.media.upload_success', ['count' => $count]));
    }

    public function delete(string $path): void
    {
        if (! $this->isManagedPath($path)) {
            return;
        }

        Storage::disk('public')->delete($path);
    }

    private function isManagedPath(string $path): bool
    {
        foreach (['news/covers/', 'news/inline/', 'pages/', 'videos/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    public function render()
    {
        $folders = $this->folder !== ''
            ? [$this->folder]
            : ['news/covers', 'news/inline', 'pages', 'videos'];

        $files = collect();
        foreach ($folders as $folder) {
            foreach (Storage::disk('public')->files($folder) as $f) {
                $files->push([
                    'path' => $f,
                    'name' => basename($f),
                    'url' => Storage::disk('public')->url($f),
                    'size' => Storage::disk('public')->size($f),
                    'modified' => Storage::disk('public')->lastModified($f),
                    'folder' => $folder,
                ]);
            }
        }

        if ($this->search !== '') {
            $files = $files->filter(fn ($f) => str_contains(strtolower($f['name']), strtolower($this->search)));
        }

        $files = $files->sortByDesc('modified')->values();

        return view('livewire.admin.media.index', [
            'files' => $files,
        ])->title(__('admin.media.title_section'));
    }
}
