<?php

namespace App\Livewire\Concerns;

use App\Support\ImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesImageUploads
{
    /**
     * Store an uploaded image on the public disk in the given folder, after
     * passing it through ImageOptimizer (resize, re-encode, strip EXIF).
     * Returns the storage path (e.g. "news/covers/{uuid}.jpg").
     */
    protected function storeUploadedImage(UploadedFile $file, string $folder): string
    {
        $extension = ImageOptimizer::safeExtension($file);
        $filename = Str::uuid()->toString().'.'.$extension;
        $relativePath = $folder.'/'.$filename;
        $absolutePath = Storage::disk('public')->path($relativePath);

        $dir = dirname($absolutePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        app(ImageOptimizer::class)->optimize($file, $absolutePath);

        return $relativePath;
    }

    /**
     * Delete a previously stored image from the public disk if it is a
     * managed path under one of our storage subfolders.
     */
    protected function deleteStoredImage(?string $path): void
    {
        if (! $path) {
            return;
        }

        $managedPrefixes = ['news/', 'pages/', 'videos/', 'journals/'];
        foreach ($managedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                Storage::disk('public')->delete($path);

                return;
            }
        }
    }
}
