<?php

namespace App\Http\Controllers;

use App\Models\OpenData;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\StreamedResponse;

class OpenDataDownloadController extends Controller
{
    public function __invoke(OpenData $openData): StreamedResponse
    {
        abort_unless($openData->is_published, 404);
        abort_unless(Storage::disk('local')->exists($openData->file_path), 404);

        $openData->increment('download_count');

        return Storage::disk('local')->download($openData->file_path, $openData->file_name);
    }
}
