<?php

namespace App\Livewire\Admin\OpenData;

use App\Models\OpenData;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class OpenDataIndex extends Component
{
    use WithFileUploads;

    public const LOCALES = ['uz', 'kr', 'ru', 'en'];

    public const PRIMARY_LOCALE = 'uz';

    public ?int $editingId = null;

    public ?int $year = null;

    public ?string $quarter = null;

    public bool $is_published = true;

    /** @var array<string, string> */
    public array $titles = ['kr' => '', 'uz' => '', 'ru' => '', 'en' => ''];

    public $fileUpload = null;

    public ?string $currentFileName = null;

    public bool $showForm = false;

    /** @return array<string, array<int, mixed>> */
    protected function rules(): array
    {
        return [
            'year' => ['required', 'integer', 'between:2000,2100'],
            'quarter' => ['nullable', 'integer', 'between:1,4'],
            'titles.uz' => ['required', 'string', 'max:255'],
            'titles.kr' => ['nullable', 'string', 'max:255'],
            'titles.ru' => ['nullable', 'string', 'max:255'],
            'titles.en' => ['nullable', 'string', 'max:255'],
            'fileUpload' => [
                $this->editingId ? 'nullable' : 'required',
                'file', 'mimes:pdf,xls,xlsx,csv,doc,docx', 'max:20480',
            ],
        ];
    }

    public function startCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $entry = OpenData::with('translations')->findOrFail($id);
        $this->editingId = $entry->id;
        $this->year = $entry->year;
        $this->quarter = $entry->quarter ? (string) $entry->quarter : null;
        $this->is_published = $entry->is_published;
        $this->currentFileName = $entry->file_name;
        $this->titles = ['kr' => '', 'uz' => '', 'ru' => '', 'en' => ''];
        foreach ($entry->translations as $t) {
            $this->titles[$t->language] = $t->title;
        }
        $this->fileUpload = null;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $entry = $this->editingId ? OpenData::findOrFail($this->editingId) : new OpenData;
        $isNew = ! $entry->exists;

        if ($this->fileUpload) {
            $path = $this->fileUpload->store('open-data', 'local');
            if (! $isNew && $entry->file_path) {
                Storage::disk('local')->delete($entry->file_path);
            }
            $entry->file_path = $path;
            $entry->file_name = $this->fileUpload->getClientOriginalName();
            $entry->file_size = $this->fileUpload->getSize();
            $entry->file_mime = $this->fileUpload->getMimeType();
        }

        $entry->year = $this->year;
        $entry->quarter = $this->quarter !== null && $this->quarter !== '' ? (int) $this->quarter : null;
        $entry->is_published = $this->is_published;
        if ($isNew) {
            $entry->user_id = auth()->id();
        }
        $entry->save();

        foreach (self::LOCALES as $locale) {
            $title = trim((string) ($this->titles[$locale] ?? ''));
            $existing = $entry->translations()->where('language', $locale)->first();
            if ($title === '') {
                $existing?->delete();

                continue;
            }
            if ($existing) {
                $existing->update(['title' => $title]);
            } else {
                $entry->translations()->create(['language' => $locale, 'title' => $title]);
            }
        }

        $entry->logActivity($isNew ? 'created' : 'updated', ['year' => $entry->year]);

        session()->flash('status', __('admin.open_data.saved_flash'));
        $this->resetForm();
        $this->showForm = false;
    }

    public function delete(int $id): void
    {
        $entry = OpenData::findOrFail($id);
        $entry->logActivity('deleted', ['year' => $entry->year]);
        if ($entry->file_path) {
            Storage::disk('local')->delete($entry->file_path);
        }
        $entry->delete();
        session()->flash('status', __('admin.open_data.deleted_flash'));
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->year = null;
        $this->quarter = null;
        $this->is_published = true;
        $this->titles = ['kr' => '', 'uz' => '', 'ru' => '', 'en' => ''];
        $this->fileUpload = null;
        $this->currentFileName = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.open-data.index', [
            'entries' => OpenData::with('translations')
                ->orderByDesc('year')->orderByDesc('quarter')->orderByDesc('id')->get(),
        ])->title(__('admin.open_data.title_section'));
    }
}
