<?php

namespace App\Livewire\Admin\Media;

use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Медиатека')]
class MediaIndex extends Component
{
    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $folder = '';

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
        ]);
    }
}
