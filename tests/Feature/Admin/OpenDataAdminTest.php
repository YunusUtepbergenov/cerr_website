<?php

use App\Livewire\Admin\OpenData\OpenDataIndex;
use App\Models\OpenData;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

describe('Open data admin access', function () {
    it('lets accountants and admins in, but not editors', function () {
        $this->actingAs(User::factory()->create(['role' => 'accountant']))
            ->get(route('admin.open-data.index'))->assertOk();
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('admin.open-data.index'))->assertOk();
        $this->actingAs(User::factory()->create(['role' => 'editor']))
            ->get(route('admin.open-data.index'))->assertForbidden();
    })->group('feature', 'admin');

    it('forbids accountants from other admin sections', function () {
        $accountant = User::factory()->create(['role' => 'accountant']);

        $this->actingAs($accountant)->get(route('admin.news.index'))->assertForbidden();
        $this->actingAs($accountant)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($accountant)->get(route('admin.media.index'))->assertForbidden();
    })->group('feature', 'admin');
});

describe('Open data admin CRUD', function () {
    beforeEach(function () {
        app()->setLocale('ru');
        Storage::fake('local');
        $this->accountant = User::factory()->create(['role' => 'accountant']);
    });

    it('creates an entry with a file and four titles', function () {
        Livewire::actingAs($this->accountant)->test(OpenDataIndex::class)
            ->call('startCreate')
            ->set('year', 2025)
            ->set('quarter', '2')
            ->set('titles.uz', 'Hisobot UZ')
            ->set('titles.ru', 'Отчёт RU')
            ->set('fileUpload', UploadedFile::fake()->create('report.pdf', 20, 'application/pdf'))
            ->call('save')
            ->assertHasNoErrors();

        $entry = OpenData::first();
        expect($entry)->not->toBeNull()
            ->and($entry->year)->toBe(2025)
            ->and($entry->quarter)->toBe(2)
            ->and($entry->user_id)->toBe($this->accountant->id)
            ->and($entry->translations->pluck('title', 'language')['uz'])->toBe('Hisobot UZ');
        Storage::disk('local')->assertExists($entry->file_path);
    })->group('feature', 'admin');

    it('rejects a missing year, a bad mime and an oversized file', function () {
        Livewire::actingAs($this->accountant)->test(OpenDataIndex::class)
            ->call('startCreate')
            ->set('titles.uz', 'x')
            ->set('fileUpload', UploadedFile::fake()->create('a.exe', 10))
            ->call('save')
            ->assertHasErrors(['year', 'fileUpload']);
    })->group('feature', 'admin');

    it('requires the primary uz title', function () {
        Livewire::actingAs($this->accountant)->test(OpenDataIndex::class)
            ->call('startCreate')
            ->set('year', 2025)
            ->set('fileUpload', UploadedFile::fake()->create('a.pdf', 10, 'application/pdf'))
            ->call('save')
            ->assertHasErrors(['titles.uz']);
    })->group('feature', 'admin');

    it('replaces the file on edit and deletes the old one', function () {
        $oldPath = UploadedFile::fake()->create('old.pdf', 10)->store('open-data', 'local');
        $entry = OpenData::factory()->create(['file_path' => $oldPath, 'file_name' => 'old.pdf']);
        $entry->translations()->create(['language' => 'uz', 'title' => 'Eski']);

        Livewire::actingAs($this->accountant)->test(OpenDataIndex::class)
            ->call('edit', $entry->id)
            ->set('fileUpload', UploadedFile::fake()->create('new.pdf', 10, 'application/pdf'))
            ->call('save')
            ->assertHasNoErrors();

        Storage::disk('local')->assertMissing($oldPath);
        expect($entry->fresh()->file_name)->toBe('new.pdf');
    })->group('feature', 'admin');

    it('deletes an entry and its file', function () {
        $path = UploadedFile::fake()->create('d.pdf', 10)->store('open-data', 'local');
        $entry = OpenData::factory()->create(['file_path' => $path]);

        Livewire::actingAs($this->accountant)->test(OpenDataIndex::class)
            ->call('delete', $entry->id);

        expect(OpenData::find($entry->id))->toBeNull();
        Storage::disk('local')->assertMissing($path);
    })->group('feature', 'admin');
});
