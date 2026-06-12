<?php

namespace App\Livewire\Admin\Categories;

use App\Models\Category;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class CategoryIndex extends Component
{
    public const LOCALES = ['kr', 'uz', 'ru', 'en'];

    public ?int $editingId = null;

    public string $slug = '';

    public bool $status = true;

    /** @var array<string, string> */
    public array $names = [];

    public bool $showForm = false;

    public function mount(): void
    {
        $this->resetForm();
    }

    protected function rules(): array
    {
        return [
            'slug' => ['required', 'string', 'max:255', Rule::unique('categories', 'slug')->ignore($this->editingId)],
            'status' => ['boolean'],
            'names' => ['array'],
            'names.kr' => ['required', 'string', 'max:255'],
            'names.uz' => ['nullable', 'string', 'max:255'],
            'names.ru' => ['nullable', 'string', 'max:255'],
            'names.en' => ['nullable', 'string', 'max:255'],
        ];
    }

    public function startCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $category = Category::with('translations')->findOrFail($id);
        $this->editingId = $category->id;
        $this->slug = $category->slug;
        $this->status = (bool) $category->status;
        $this->names = [];
        foreach (self::LOCALES as $locale) {
            $this->names[$locale] = optional($category->translations->firstWhere('language', $locale))->name ?? '';
        }
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $category = $this->editingId ? Category::findOrFail($this->editingId) : new Category;
        $isNew = ! $category->exists;
        $category->slug = Str::slug($this->slug);
        $category->status = $this->status;
        $category->save();

        foreach (self::LOCALES as $locale) {
            $name = trim((string) ($this->names[$locale] ?? ''));
            if ($name === '' && $locale !== 'kr') {
                $category->translations()->where('language', $locale)->delete();

                continue;
            }
            $category->translations()->updateOrCreate(
                ['language' => $locale],
                ['name' => $name]
            );
        }

        $changes = array_filter(['slug' => $category->getChanges()['slug'] ?? null]);
        $category->logActivity($isNew ? 'created' : 'updated', $changes);

        session()->flash('status', __('admin.categories.saved_flash'));
        $this->resetForm();
        $this->showForm = false;
    }

    public function delete(int $id): void
    {
        $category = Category::findOrFail($id);
        $category->logActivity('deleted', ['slug' => $category->slug]);
        $category->delete();
        session()->flash('status', __('admin.categories.deleted_flash'));
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->slug = '';
        $this->status = true;
        $this->names = array_fill_keys(self::LOCALES, '');
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.categories.index', [
            'categories' => Category::with('translations')->orderBy('id', 'desc')->get(),
        ])->title(__('admin.categories.title_section'));
    }
}
