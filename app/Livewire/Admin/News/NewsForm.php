<?php

namespace App\Livewire\Admin\News;

use App\Livewire\Concerns\HandlesImageUploads;
use App\Models\Category;
use App\Models\News;
use App\Models\Tag;
use App\Support\HtmlSanitizer;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class NewsForm extends Component
{
    use HandlesImageUploads, WithFileUploads;

    public const LOCALES = ['uz', 'kr', 'ru', 'en'];

    public const PRIMARY_LOCALE = 'uz';

    public ?News $news = null;

    public string $slug = '';

    public ?int $category_id = null;

    public string $status = 'draft';

    public bool $is_main = false;

    public ?string $scheduled_at = null;

    /** @var array<int> */
    public array $tag_ids = [];

    /** @var array<string, array{title: string, short_description: string, content: string, seo_title: ?string, seo_description: ?string, image_url: ?string}> */
    public array $translations = [];

    /** @var array<string, mixed> */
    public array $cover_uploads = [];

    public string $activeLocale = 'uz';

    public function mount(?News $news = null): void
    {
        if ($news && $news->exists) {
            abort_if(! auth()->user()->canEditNews($news), 403);

            $news->load(['translations', 'tags']);
            $this->news = $news;
            $this->slug = $news->slug;
            $this->category_id = $news->category_id;
            $this->status = $news->status;
            $this->is_main = (bool) $news->is_main;
            $this->scheduled_at = $news->scheduled_at?->format('Y-m-d\TH:i');
            $this->tag_ids = $news->tags->pluck('id')->all();

            foreach (self::LOCALES as $locale) {
                $t = $news->translations->firstWhere('lang', $locale);
                $this->translations[$locale] = [
                    'title' => $t->title ?? '',
                    'short_description' => $t->short_description ?? '',
                    'content' => $t->content ?? '',
                    'seo_title' => $t->seo_title ?? null,
                    'seo_description' => $t->seo_description ?? null,
                    'image_url' => $t->image_url ?? null,
                ];
            }
        } else {
            $this->scheduled_at = now()->format('Y-m-d\TH:i');

            foreach (self::LOCALES as $locale) {
                $this->translations[$locale] = [
                    'title' => '',
                    'short_description' => '',
                    'content' => '',
                    'seo_title' => null,
                    'seo_description' => null,
                    'image_url' => null,
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

    /**
     * Generate a URL-safe slug from the supplied title and ensure it's unique
     * across the news table. If a clash is detected, suffix with -2, -3, etc.
     * Used by the Alpine watcher in the form to auto-fill the slug field.
     */
    public function regenerateSlug(?string $title): void
    {
        if ($this->news?->exists) {
            return;
        }

        $base = Str::slug((string) $title);

        if ($base === '') {
            $this->slug = '';

            return;
        }

        $candidate = $base;
        $i = 2;

        while (News::where('slug', $candidate)->exists()) {
            $candidate = $base.'-'.$i;
            $i++;
        }

        $this->slug = $candidate;
    }

    /**
     * @return array<string, mixed>
     */
    protected function rules(): array
    {
        $newsId = $this->news?->id;

        $rules = [
            'slug' => ['required', 'string', 'max:255', Rule::unique('news', 'slug')->ignore($newsId)],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'status' => ['required', Rule::in(['draft', 'published', 'auto_publish', 'disabled'])],
            'is_main' => ['boolean'],
            'scheduled_at' => ['nullable', 'date'],
            'tag_ids' => ['array'],
            'tag_ids.*' => ['integer', 'exists:tags,id'],
        ];

        foreach (self::LOCALES as $locale) {
            $required = $locale === self::PRIMARY_LOCALE ? 'required' : 'nullable';
            $rules["translations.$locale.title"] = [$required, 'string', 'max:255'];
            $rules["translations.$locale.short_description"] = [$required, 'string', 'max:1000'];
            $rules["translations.$locale.content"] = [$required, 'string'];
            $rules["translations.$locale.seo_title"] = ['nullable', 'string', 'max:255'];
            $rules["translations.$locale.seo_description"] = ['nullable', 'string', 'max:500'];
            $rules["cover_uploads.$locale"] = ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'];
        }

        return $rules;
    }

    public function save(): void
    {
        if (! auth()->user()->canPublishNews()) {
            $this->status = 'draft';
        }

        $this->validate();

        $news = $this->news ?? new News;
        $news->slug = Str::slug($this->slug);
        $news->category_id = $this->category_id;
        $news->status = $this->status;
        $news->is_main = $this->is_main;
        $news->scheduled_at = $this->scheduled_at ?: null;
        $news->user_id = $news->user_id ?: auth()->id();
        $isNew = ! $news->exists;
        $news->save();

        foreach (self::LOCALES as $locale) {
            $data = $this->translations[$locale];
            $hasContent = trim((string) ($data['title'] ?? '')) !== ''
                || trim((string) ($data['content'] ?? '')) !== '';

            $upload = $this->cover_uploads[$locale] ?? null;
            $existing = $news->translations()->where('lang', $locale)->first();
            $imagePath = $existing?->image_url;

            if ($upload) {
                $imagePath = $this->storeUploadedImage($upload, 'news/covers');
                if ($existing && $existing->image_url !== $imagePath) {
                    $this->deleteStoredImage($existing->image_url);
                }
            }

            if (! $hasContent && ! $existing) {
                continue;
            }

            $news->translations()->updateOrCreate(
                ['lang' => $locale],
                [
                    'title' => $data['title'] ?? '',
                    'short_description' => $data['short_description'] ?? '',
                    'content' => HtmlSanitizer::sanitize($data['content'] ?? ''),
                    'image_url' => $imagePath ?? '',
                    'seo_title' => $data['seo_title'] ?? null,
                    'seo_description' => $data['seo_description'] ?? null,
                ]
            );
        }

        $news->tags()->sync($this->tag_ids);

        $this->cover_uploads = [];
        $this->news = $news->fresh(['translations', 'tags']);

        $news->logActivity($isNew ? 'created' : 'updated', $this->summarizeChanges($news));

        session()->flash('status', __('admin.news.saved_flash'));

        $this->redirectRoute('admin.news.edit', ['news' => $news->id], navigate: false);
    }

    /**
     * @return array<string, mixed>
     */
    private function summarizeChanges(News $news): array
    {
        $tracked = ['slug', 'category_id', 'status', 'is_main', 'scheduled_at'];
        $diff = [];
        foreach ($news->getChanges() as $key => $value) {
            if (in_array($key, $tracked, true)) {
                $diff[$key] = $value;
            }
        }
        if ($news->translations->isNotEmpty()) {
            $diff['translations'] = 'changed';
        }

        return $diff;
    }

    public function clearCover(string $locale): void
    {
        if (! in_array($locale, self::LOCALES, true)) {
            return;
        }

        $existing = $this->news?->translations()->where('lang', $locale)->first();
        $this->deleteStoredImage($existing?->image_url);
        if ($existing) {
            $existing->update(['image_url' => '']);
        }

        $this->translations[$locale]['image_url'] = null;
        $this->cover_uploads[$locale] = null;
    }

    public function render()
    {
        return view('livewire.admin.news.form', [
            'categories' => Category::with('translations')->get(),
            'allTags' => Tag::orderBy('name')->get(),
        ])->title($this->news?->exists ? __('admin.news.edit_article') : __('admin.news.new_article'));
    }
}
