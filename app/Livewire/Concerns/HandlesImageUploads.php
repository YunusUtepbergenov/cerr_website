<?php

namespace App\Livewire\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesImageUploads
{
    /**
     * Store an uploaded image on the public disk in the given folder.
     * Returns the storage path (e.g. "news/covers/{uuid}.jpg").
     */
    protected function storeUploadedImage(UploadedFile $file, string $folder): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $filename = Str::uuid()->toString().'.'.$extension;

        return $file->storeAs($folder, $filename, 'public');
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

        $managedPrefixes = ['news/', 'pages/', 'videos/'];
        foreach ($managedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                Storage::disk('public')->delete($path);

                return;
            }
        }
    }
}
