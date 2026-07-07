<?php

namespace App\Http\Controllers\Admin;

use App\Support\ImageOptimizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class MediaUploadController
{
    /**
     * Receive one image upload from FilePond, optimize it, and store it
     * under the requested folder. Returns the stored path so FilePond can
     * track files by id.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
            'folder' => ['required', Rule::in(['news/covers', 'news/inline', 'pages', 'videos'])],
        ]);

        $file = $request->file('file');
        $folder = $request->input('folder');

        $extension = ImageOptimizer::safeExtension($file);
        $filename = Str::uuid()->toString().'.'.$extension;
        $relativePath = $folder.'/'.$filename;
        $absolutePath = Storage::disk('public')->path($relativePath);

        $dir = dirname($absolutePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        app(ImageOptimizer::class)->optimize($file, $absolutePath);

        return response()->json([
            'path' => $relativePath,
            'url' => Storage::disk('public')->url($relativePath),
        ]);
    }
}
