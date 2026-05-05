<?php

namespace App\Http\Controllers\Admin;

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
            'file' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:5120'],
        ]);

        $file = $request->file('file');

        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $filename = Str::uuid()->toString().'.'.$extension;
        $path = $file->storeAs('news/inline', $filename, 'public');

        return response()->json([
            'location' => Storage::disk('public')->url($path),
        ]);
    }
}
