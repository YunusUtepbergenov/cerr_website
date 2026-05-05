<?php

namespace App\Livewire\Admin\Pages;

use App\Livewire\Concerns\HandlesImageUploads;
use App\Models\Page;
use App\Support\HtmlSanitizer;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
#[Title('Редактирование страницы')]
class PageForm extends Component
{
    use HandlesImageUploads, WithFileUploads;

    public const LOCALES = ['kr', 'uz', 'ru', 'en'];

    public ?Page $page = null;

    public string $slug = '';

    public ?string $image = null;

    public $imageUpload = null;

    /** @var array<string, array{title: string, content: string, seo_title: ?string, seo_description: ?string}> */
    public array $translations = [];

    public string $activeLocale = 'kr';

    public function mount(?Page $page = null): void
    {
        if ($page && $page->exists) {
            $page->load('translations');
            $this->page = $page;
            $this->slug = $page->slug;
            $this->image = $page->image;

            foreach (self::LOCALES as $locale) {
                $t = $page->translations->firstWhere('language', $locale);
                $this->translations[$locale] = [
                    'title' => $t->title ?? '',
                    'content' => $t->content ?? '',
                    'image' => $t->image ?? '',
                    'seo_title' => $t->seo_title ?? null,
                    'seo_description' => $t->seo_description ?? null,
                ];
            }
        } else {
            foreach (self::LOCALES as $locale) {
                $this->translations[$locale] = [
                    'title' => '',
                    'content' => '',
                    'image' => '',
                    'seo_title' => null,
                    'seo_description' => null,
                ];
            }
        }
    }

    public function setLocale(string $locale): void
    {
        if (in_array($locale, self::LOCALES, true)) {
            $this->activeLocale = $locale;
        }
    }

    protected function rules(): array
    {
        $rules = [
            'slug' => ['required', 'string', 'max:255', Rule::unique('pages', 'slug')->ignore($this->page?->id)],
            'imageUpload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ];
        foreach (self::LOCALES as $locale) {
            $req = $locale === 'kr' ? 'required' : 'nullable';
            $rules["translations.$locale.title"] = [$req, 'string', 'max:255'];
            $rules["translations.$locale.content"] = [$req, 'string'];
        }

        return $rules;
    }

    public function save(): void
    {
        $this->validate();

        $page = $this->page ?? new Page;
        $isNew = ! $page->exists;
        $page->slug = Str::slug($this->slug);
        if ($this->imageUpload) {
            $newImage = $this->storeUploadedImage($this->imageUpload, 'pages');
            $this->deleteStoredImage($page->image);
            $page->image = $newImage;
        }
        $page->save();

        foreach (self::LOCALES as $locale) {
            $data = $this->translations[$locale];
            $hasContent = trim((string) ($data['title'] ?? '')) !== '';
            if (! $hasContent && $locale !== 'kr') {
                $page->translations()->where('language', $locale)->delete();

                continue;
            }
            $page->translations()->updateOrCreate(
                ['language' => $locale],
                [
                    'title' => $data['title'] ?? '',
                    'content' => HtmlSanitizer::sanitize($data['content'] ?? ''),
                    'image' => $data['image'] ?? '',
                    'seo_title' => $data['seo_title'] ?? null,
                    'seo_description' => $data['seo_description'] ?? null,
                ]
            );
        }

        $this->page = $page->fresh('translations');
        $this->image = $page->image;
        $this->imageUpload = null;

        $changes = [];
        foreach ($page->getChanges() as $key => $value) {
            if (in_array($key, ['slug'], true)) {
                $changes[$key] = $value;
            }
        }
        if ($page->translations->isNotEmpty()) {
            $changes['translations'] = 'changed';
        }
        $page->logActivity($isNew ? 'created' : 'updated', $changes);

        session()->flash('status', __('admin.pages.saved_flash'));

        $this->redirectRoute('admin.pages.edit', ['page' => $page->id], navigate: false);
    }

    public function render()
    {
        return view('livewire.admin.pages.form');
    }
}
