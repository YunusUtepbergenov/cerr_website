<?php

use App\Models\OpenData;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

describe('Open data download', function () {
    beforeEach(function () {
        Storage::fake('local');
    });

    it('streams the file and increments the download count', function () {
        $path = UploadedFile::fake()->create('report.pdf', 10)->store('open-data', 'local');
        $entry = OpenData::factory()->create([
            'file_path' => $path,
            'file_name' => 'report.pdf',
            'is_published' => true,
            'download_count' => 4,
        ]);

        $response = $this->get(route('open-data.download', $entry));

        $response->assertOk()->assertDownload('report.pdf');
        expect($entry->fresh()->download_count)->toBe(5);
    })->group('feature', 'public');

    it('404s for an unpublished entry and does not count', function () {
        $entry = OpenData::factory()->create(['is_published' => false, 'download_count' => 2]);

        $this->get(route('open-data.download', $entry))->assertNotFound();
        expect($entry->fresh()->download_count)->toBe(2);
    })->group('feature', 'public');

    it('404s when the file is missing on disk', function () {
        $entry = OpenData::factory()->create([
            'file_path' => 'open-data/missing.pdf',
            'is_published' => true,
        ]);

        $this->get(route('open-data.download', $entry))->assertNotFound();
    })->group('feature', 'public');
});
