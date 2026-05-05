<?php

namespace App\Livewire\Admin\Media;

use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;
use Livewire\Component;

class MediaPicker extends Component
{
    public bool $open = false;

    public string $search = '';

    public string $folder = 'news/covers';

    public string $contextEvent = 'media-picked';

    public function show(string $folder = 'news/covers', string $contextEvent = 'media-picked'): void
    {
        $this->folder = in_array($folder, ['news/covers', 'news/inline', 'pages', 'videos'], true)
            ? $folder
            : 'news/covers';
        $this->contextEvent = $contextEvent;
        $this->open = true;
    }

    #[On('show-picker')]
    public function showFromEvent(string $folder = 'news/covers'): void
    {
        $this->show($folder);
    }

    public function pick(string $path): void
    {
        $url = Storage::disk('public')->url($path);
        $this->dispatch($this->contextEvent, path: $path, url: $url);
        $this->open = false;
    }

    public function render()
    {
        $files = collect(Storage::disk('public')->files($this->folder))
            ->map(fn ($p) => [
                'path' => $p,
                'name' => basename($p),
                'url' => Storage::disk('public')->url($p),
                'modified' => Storage::disk('public')->lastModified($p),
            ])
            ->when($this->search !== '', fn ($c) => $c->filter(fn ($f) => str_contains(strtolower($f['name']), strtolower($this->search))))
            ->sortByDesc('modified')
            ->values();

        return view('livewire.admin.media.picker', ['files' => $files]);
    }
}
