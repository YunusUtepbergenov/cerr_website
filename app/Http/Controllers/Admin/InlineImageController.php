<?php

namespace App\Http\Controllers\Admin;

use App\Support\ImageOptimizer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class InlineImageController
{
    /**
     * Handle TinyMCE inline image uploads.
     *
     * Returns a JSON payload of the form expected by TinyMCE's
     * `images_upload_url` integration: { "location": "https://..." }.
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'file' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:5120'],
        ]);

        $file = $request->file('file');
        $extension = ImageOptimizer::safeExtension($file);
        $filename = Str::uuid()->toString().'.'.$extension;
        $relativePath = 'news/inline/'.$filename;
        $absolutePath = Storage::disk('public')->path($relativePath);

        $dir = dirname($absolutePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        app(ImageOptimizer::class)->optimize($file, $absolutePath);

        return response()->json([
            'location' => Storage::disk('public')->url($relativePath),
        ]);
    }
}
