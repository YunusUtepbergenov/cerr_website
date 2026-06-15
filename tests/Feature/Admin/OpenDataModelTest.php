<?php

use App\Models\OpenData;
use App\Models\OpenDataTranslation;

describe('OpenData model', function () {
    it('relates to its translations and uploader', function () {
        $entry = OpenData::factory()->create(['year' => 2025, 'quarter' => 2]);
        $entry->translations()->create(['language' => 'uz', 'title' => 'Hisobot']);

        expect($entry->translations)->toHaveCount(1)
            ->and($entry->translations->first())->toBeInstanceOf(OpenDataTranslation::class);
    });

    it('resolves the current-locale title with a fallback', function () {
        app()->setLocale('ru');
        $entry = OpenData::factory()->create();
        $entry->translations()->create(['language' => 'uz', 'title' => 'Faqat uz']);

        expect($entry->fresh()->title())->toBe('Faqat uz'); // falls back when ru missing

        $entry->translations()->create(['language' => 'ru', 'title' => 'Русский']);
        expect($entry->fresh()->title())->toBe('Русский');
    });

    it('only returns published entries from the published scope', function () {
        OpenData::factory()->create(['is_published' => true]);
        OpenData::factory()->create(['is_published' => false]);

        expect(OpenData::published()->count())->toBe(1);
    });

    it('exposes a human file size, extension and quarter label', function () {
        $entry = OpenData::factory()->create([
            'file_name' => 'report.xlsx',
            'file_size' => 2048,
            'quarter' => 3,
        ]);

        expect($entry->fileExtension())->toBe('XLSX')
            ->and($entry->fileSizeForHumans())->toContain('KB')
            ->and($entry->quarterLabel())->toBe('III');
    });
})->group('feature', 'admin');
