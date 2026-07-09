# Journals Admin Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Let administrators and editors add/edit/delete journal issues in the admin panel, and drive the public home-page journals strip from that data.

**Architecture:** A flat single-language `Journal` model (title, uploaded cover, external link, date, active flag). One self-contained Livewire admin component with an inline form card — mirroring the existing Videos admin exactly. The public home component loads active journals and the Blade loops them instead of a hardcoded array.

**Tech Stack:** Laravel 12, Livewire 4, Pest 3, PostgreSQL (SQLite `:memory:` in tests), Bootstrap admin UI. Image handling via the existing `HandlesImageUploads` trait + `ImageOptimizer`.

**Reference files to imitate (already in the codebase):**
- Component: `app/Livewire/Admin/Videos/VideoIndex.php`
- View: `resources/views/livewire/admin/videos/index.blade.php`
- Model: `app/Models/Video.php` · Migration: `database/migrations/2025_03_20_051235_create_videos_table.php` · Factory: `database/factories/VideoFactory.php`
- Tests: `tests/Feature/Admin/VideoCrudTest.php`, `tests/Feature/Admin/RolePermissionsTest.php`, `tests/Feature/Livewire/HomeTest.php`
- Trait: `app/Livewire/Concerns/HandlesImageUploads.php`

**Conventions:**
- Run tests with: `php artisan test --compact --filter=<name>` (or a path). Ignore the pre-existing `PDO::MYSQL_ATTR_SSL_CA` deprecation notice — it is environmental (PHP 8.5), not a failure.
- After any PHP change, run `vendor/bin/pint --dirty --format agent` before committing.
- The admin panel is always rendered in Russian (`SetAdminLocale` forces `ru`), so only `lang/ru/admin.php` needs new strings.
- Work happens on the existing `feat/journals-admin` branch.

**Working-tree caution:** `resources/views/livewire/home.blade.php` and `tests/Feature/Livewire/HomeTest.php` already have uncommitted changes from other work. Only touch the journals-related regions described in Task 5. Do not revert or restructure unrelated parts.

---

### Task 1: Journal model, migration, and factory

**Files:**
- Create: `app/Models/Journal.php`
- Create: `database/migrations/<timestamp>_create_journals_table.php` (generated)
- Create: `database/factories/JournalFactory.php` (generated, then edited)
- Test: `tests/Unit/Models/JournalTest.php`

- [ ] **Step 1: Generate the model, migration, and factory**

Run: `php artisan make:model Journal -mf --no-interaction`

Expected: creates `app/Models/Journal.php`, a `..._create_journals_table.php` migration, and `database/factories/JournalFactory.php`.

- [ ] **Step 2: Write the failing unit test**

Create `tests/Unit/Models/JournalTest.php`:

```php
<?php

use App\Models\Journal;
use Illuminate\Support\Carbon;

describe('Journal Model', function () {
    it('active scope returns only active journals', function () {
        Journal::factory()->create();
        Journal::factory()->inactive()->create();

        expect(Journal::active()->count())->toBe(1);
    })->group('unit', 'models');

    it('coverUrl resolves a stored path', function () {
        $journal = Journal::factory()->make(['cover_image' => 'journals/x.jpg']);

        expect($journal->coverUrl())->toContain('journals/x.jpg');
    })->group('unit', 'models');

    it('coverUrl is null when there is no cover', function () {
        $journal = Journal::factory()->make(['cover_image' => '']);

        expect($journal->coverUrl())->toBeNull();
    })->group('unit', 'models');

    it('casts published_at to a date and is_active to a bool', function () {
        $journal = Journal::factory()->create(['is_active' => 1]);

        expect($journal->published_at)->toBeInstanceOf(Carbon::class)
            ->and($journal->is_active)->toBeTrue();
    })->group('unit', 'models');
});
```

- [ ] **Step 3: Run the test to verify it fails**

Run: `php artisan test --compact tests/Unit/Models/JournalTest.php`
Expected: FAIL — `Call to undefined method ...::active()` / missing `inactive` factory state / no `journals` table.

- [ ] **Step 4: Fill in the migration**

Replace the generated migration body with:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('journals', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('cover_image');
            $table->string('link', 2048);
            $table->date('published_at');
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['is_active', 'published_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('journals');
    }
};
```

- [ ] **Step 5: Write the model**

Replace `app/Models/Journal.php` with:

```php
<?php

namespace App\Models;

use App\Models\Concerns\LogsActivity;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;

class Journal extends Model
{
    use HasFactory, LogsActivity;

    protected $fillable = [
        'title',
        'cover_image',
        'link',
        'published_at',
        'is_active',
    ];

    /** @return array<string, string> */
    protected function casts(): array
    {
        return [
            'published_at' => 'date',
            'is_active' => 'boolean',
        ];
    }

    /**
     * Limit the query to journals that should be publicly visible.
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('is_active', true);
    }

    /**
     * Public URL for the uploaded cover image, or null when none is set.
     */
    public function coverUrl(): ?string
    {
        return $this->cover_image
            ? Storage::disk('public')->url($this->cover_image)
            : null;
    }
}
```

- [ ] **Step 6: Write the factory**

Replace `database/factories/JournalFactory.php` with:

```php
<?php

namespace Database\Factories;

use App\Models\Journal;
use Illuminate\Database\Eloquent\Factories\Factory;

class JournalFactory extends Factory
{
    protected $model = Journal::class;

    public function definition(): array
    {
        return [
            'title' => fake()->sentence(3),
            'cover_image' => 'journals/'.fake()->uuid().'.jpg',
            'link' => fake()->url(),
            'published_at' => fake()->dateTimeBetween('-1 year')->format('Y-m-d'),
            'is_active' => true,
        ];
    }

    public function inactive(): static
    {
        return $this->state(fn (): array => ['is_active' => false]);
    }
}
```

- [ ] **Step 7: Run the test to verify it passes**

Run: `php artisan test --compact tests/Unit/Models/JournalTest.php`
Expected: PASS (4 passed).

- [ ] **Step 8: Apply the migration to the dev database**

Run: `php artisan migrate`
Expected: `journals` table created.

- [ ] **Step 9: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Models/Journal.php database/migrations/*_create_journals_table.php database/factories/JournalFactory.php tests/Unit/Models/JournalTest.php
git commit -m "feat(journals): add Journal model, migration, and factory

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 2: Add `journals/` to the managed image prefixes

**Files:**
- Modify: `app/Livewire/Concerns/HandlesImageUploads.php:44`

This lets `deleteStoredImage()` actually delete journal covers. It is verified by the delete test in Task 3, but is a one-line, isolated change so we make it first.

- [ ] **Step 1: Edit the allowlist**

In `app/Livewire/Concerns/HandlesImageUploads.php`, change:

```php
$managedPrefixes = ['news/', 'pages/', 'videos/'];
```

to:

```php
$managedPrefixes = ['news/', 'pages/', 'videos/', 'journals/'];
```

- [ ] **Step 2: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Livewire/Concerns/HandlesImageUploads.php
git commit -m "feat(journals): allow deleting managed journal cover images

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 3: Admin CRUD component, view, and language strings

**Files:**
- Create: `app/Livewire/Admin/Journals/JournalIndex.php`
- Create: `resources/views/livewire/admin/journals/index.blade.php`
- Modify: `lang/ru/admin.php` (add `nav.journals` and a `journals` section)
- Test: `tests/Feature/Admin/JournalCrudTest.php`

- [ ] **Step 1: Write the failing feature test**

Create `tests/Feature/Admin/JournalCrudTest.php`:

```php
<?php

use App\Livewire\Admin\Journals\JournalIndex;
use App\Models\Journal;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

describe('Journal CRUD', function () {
    it('creates a journal with an uploaded cover', function () {
        Storage::fake('public');

        Livewire::test(JournalIndex::class)
            ->call('startCreate')
            ->set('title', 'Iqtisodiy sharh 8(44)')
            ->set('link', 'https://review.uz/journals/view/8-44-2025')
            ->set('published_at', '2025-08-01')
            ->set('coverUpload', UploadedFile::fake()->image('cover.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        expect(Journal::count())->toBe(1)
            ->and(Journal::first()->cover_image)->toStartWith('journals/');
    })->group('feature', 'admin');

    it('requires a title', function () {
        Storage::fake('public');

        Livewire::test(JournalIndex::class)
            ->call('startCreate')
            ->set('link', 'https://review.uz')
            ->set('coverUpload', UploadedFile::fake()->image('c.jpg'))
            ->call('save')
            ->assertHasErrors(['title']);
    })->group('feature', 'admin');

    it('requires a valid link URL', function () {
        Storage::fake('public');

        Livewire::test(JournalIndex::class)
            ->call('startCreate')
            ->set('title', 'X')
            ->set('link', 'not-a-url')
            ->set('coverUpload', UploadedFile::fake()->image('c.jpg'))
            ->call('save')
            ->assertHasErrors(['link']);
    })->group('feature', 'admin');

    it('requires a cover image when creating', function () {
        Livewire::test(JournalIndex::class)
            ->call('startCreate')
            ->set('title', 'X')
            ->set('link', 'https://review.uz')
            ->call('save')
            ->assertHasErrors(['coverUpload']);
    })->group('feature', 'admin');

    it('keeps the existing cover on edit when no new file is uploaded', function () {
        Storage::fake('public');
        Storage::disk('public')->put('journals/old.jpg', 'fake');
        $journal = Journal::factory()->create(['cover_image' => 'journals/old.jpg']);

        Livewire::test(JournalIndex::class)
            ->call('edit', $journal->id)
            ->set('title', 'Updated title')
            ->call('save')
            ->assertHasNoErrors();

        expect($journal->fresh()->cover_image)->toBe('journals/old.jpg')
            ->and($journal->fresh()->title)->toBe('Updated title');
    })->group('feature', 'admin');

    it('replaces the cover and deletes the old file on edit', function () {
        Storage::fake('public');
        Storage::disk('public')->put('journals/old.jpg', 'fake');
        $journal = Journal::factory()->create(['cover_image' => 'journals/old.jpg']);

        Livewire::test(JournalIndex::class)
            ->call('edit', $journal->id)
            ->set('coverUpload', UploadedFile::fake()->image('new.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        expect($journal->fresh()->cover_image)->toStartWith('journals/')
            ->and($journal->fresh()->cover_image)->not->toBe('journals/old.jpg');
        Storage::disk('public')->assertMissing('journals/old.jpg');
    })->group('feature', 'admin');

    it('deletes a journal and its cover file', function () {
        Storage::fake('public');
        Storage::disk('public')->put('journals/c.jpg', 'fake');
        $journal = Journal::factory()->create(['cover_image' => 'journals/c.jpg']);

        Livewire::test(JournalIndex::class)->call('delete', $journal->id);

        expect(Journal::find($journal->id))->toBeNull();
        Storage::disk('public')->assertMissing('journals/c.jpg');
    })->group('feature', 'admin');

    it('lists journals newest first', function () {
        Journal::factory()->create(['title' => 'Older issue', 'published_at' => '2025-01-01']);
        Journal::factory()->create(['title' => 'Newer issue', 'published_at' => '2025-12-01']);

        Livewire::test(JournalIndex::class)
            ->assertSeeInOrder(['Newer issue', 'Older issue']);
    })->group('feature', 'admin');
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact tests/Feature/Admin/JournalCrudTest.php`
Expected: FAIL — class `App\Livewire\Admin\Journals\JournalIndex` not found.

- [ ] **Step 3: Create the Livewire component**

Create `app/Livewire/Admin/Journals/JournalIndex.php`:

```php
<?php

namespace App\Livewire\Admin\Journals;

use App\Livewire\Concerns\HandlesImageUploads;
use App\Models\Journal;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
class JournalIndex extends Component
{
    use HandlesImageUploads, WithFileUploads;

    public ?int $editingId = null;

    public string $title = '';

    public string $link = '';

    public ?string $cover_image = null;

    public $coverUpload = null;

    public string $published_at = '';

    public bool $is_active = true;

    public bool $showForm = false;

    public function mount(): void
    {
        $this->published_at = today()->toDateString();
    }

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'link' => ['required', 'url:http,https', 'max:2048'],
            'published_at' => ['required', 'date'],
            'is_active' => ['boolean'],
            'coverUpload' => [$this->editingId ? 'nullable' : 'required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ];
    }

    public function startCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $journal = Journal::findOrFail($id);
        $this->editingId = $journal->id;
        $this->title = $journal->title;
        $this->link = $journal->link;
        $this->cover_image = $journal->cover_image;
        $this->published_at = $journal->published_at->toDateString();
        $this->is_active = $journal->is_active;
        $this->showForm = true;
    }

    public function save(): void
    {
        abort_if(! auth()->user()?->canManageContent(), 403);

        $this->validate();

        $journal = $this->editingId ? Journal::findOrFail($this->editingId) : new Journal;
        $isNew = ! $journal->exists;
        $journal->title = $this->title;
        $journal->link = $this->link;
        $journal->published_at = $this->published_at;
        $journal->is_active = $this->is_active;

        if ($this->coverUpload) {
            $newCover = $this->storeUploadedImage($this->coverUpload, 'journals');
            $this->deleteStoredImage($journal->cover_image);
            $journal->cover_image = $newCover;
        }

        $journal->save();

        $journal->logActivity($isNew ? 'created' : 'updated', array_filter([
            'title' => $journal->getChanges()['title'] ?? null,
            'link' => $journal->getChanges()['link'] ?? null,
        ]));

        session()->flash('status', __('admin.journals.saved_flash'));
        $this->resetForm();
        $this->showForm = false;
    }

    public function delete(int $id): void
    {
        abort_if(! auth()->user()?->canManageContent(), 403);

        $journal = Journal::findOrFail($id);
        $journal->logActivity('deleted', ['title' => $journal->title]);
        $this->deleteStoredImage($journal->cover_image);
        $journal->delete();

        session()->flash('status', __('admin.journals.deleted_flash'));
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->title = '';
        $this->link = '';
        $this->cover_image = null;
        $this->coverUpload = null;
        $this->published_at = today()->toDateString();
        $this->is_active = true;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.journals.index', [
            'journals' => Journal::orderByDesc('published_at')->orderByDesc('id')->get(),
        ])->title(__('admin.journals.title_section'));
    }
}
```

- [ ] **Step 4: Create the Blade view**

Create `resources/views/livewire/admin/journals/index.blade.php`:

```blade
<div>
    <x-admin.page-header :title="__('admin.journals.title_section')" :subtitle="__('admin.journals.subtitle')">
        @if (! $showForm)
            <button type="button" class="btn btn-primary" wire:click="startCreate">
                <i class="fa-solid fa-plus me-1"></i> {{ __('admin.journals.new_journal') }}
            </button>
        @endif
    </x-admin.page-header>

    @if ($showForm)
        <div class="card mb-3">
            <div class="card-header">{{ $editingId ? __('admin.journals.edit_journal') : __('admin.journals.create_journal') }}</div>
            <div class="card-body">
                <form wire:submit.prevent="save">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('admin.journals.title') }} <span class="text-danger">*</span></label>
                            <input type="text" wire:model="title" class="form-control @error('title') is-invalid @enderror">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('admin.journals.link') }} <span class="text-danger">*</span></label>
                            <input type="url" wire:model="link" class="form-control @error('link') is-invalid @enderror" placeholder="https://...">
                            @error('link') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">{{ __('admin.journals.published_at') }} <span class="text-danger">*</span></label>
                            <input type="date" wire:model="published_at" class="form-control @error('published_at') is-invalid @enderror">
                            @error('published_at') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <div class="form-check mb-2">
                                <input type="checkbox" wire:model="is_active" id="journal-active" class="form-check-input">
                                <label for="journal-active" class="form-check-label">{{ __('admin.journals.is_active') }}</label>
                            </div>
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('admin.journals.cover') }} @if (! $editingId)<span class="text-danger">*</span>@endif</label>
                            @php
                                $journalPreviewUrl = null;
                                if ($coverUpload) {
                                    try { $journalPreviewUrl = $coverUpload->temporaryUrl(); } catch (\Throwable $e) { $journalPreviewUrl = null; }
                                }
                                if (! $journalPreviewUrl && $cover_image) {
                                    $journalPreviewUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($cover_image);
                                }
                            @endphp
                            @if ($journalPreviewUrl)
                                <div class="mb-2"><img src="{{ $journalPreviewUrl }}" alt="" style="width: 120px; height: 160px; object-fit: cover; border-radius: 6px; border: 1px solid var(--admin-border);"></div>
                            @endif
                            <input type="file" wire:model="coverUpload" accept="image/*" class="form-control @error('coverUpload') is-invalid @enderror">
                            @error('coverUpload') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>
                    <div class="sticky-action-bar">
                        <button type="button" class="btn btn-outline-secondary" wire:click="cancel">{{ __('admin.common.cancel') }}</button>
                        <button type="submit" class="btn btn-primary"><i class="fa-solid fa-floppy-disk me-1"></i> {{ __('admin.common.save') }}</button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width: 70px;">{{ __('admin.journals.cover') }}</th>
                        <th>{{ __('admin.journals.title') }}</th>
                        <th>{{ __('admin.journals.link') }}</th>
                        <th style="width: 120px;">{{ __('admin.journals.published_at') }}</th>
                        <th style="width: 90px;">{{ __('admin.journals.is_active') }}</th>
                        <th style="width: 120px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($journals as $journal)
                        <tr wire:key="journal-{{ $journal->id }}">
                            <td>@if ($journal->coverUrl())<img src="{{ $journal->coverUrl() }}" alt="" style="width: 44px; height: 58px; object-fit: cover; border-radius: 4px;">@endif</td>
                            <td class="fw-semibold">{{ $journal->title }}</td>
                            <td><a href="{{ $journal->link }}" target="_blank" rel="noopener" class="text-truncate d-inline-block" style="max-width: 240px;">{{ $journal->link }}</a></td>
                            <td class="text-muted small">{{ $journal->published_at->format('Y-m-d') }}</td>
                            <td>
                                @if ($journal->is_active)
                                    <span class="badge bg-success-subtle text-success">{{ __('admin.common.active') }}</span>
                                @else
                                    <span class="badge bg-secondary-subtle text-secondary">{{ __('admin.common.inactive') }}</span>
                                @endif
                            </td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" wire:click="edit({{ $journal->id }})"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn btn-outline-danger" type="button" x-data
                                            @click="$dispatch('open-confirm', { message: @js(__('admin.journals.confirm_delete')), onConfirm: () => $wire.delete({{ $journal->id }}) })">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><x-admin.empty-state icon="fa-solid fa-book-open" :title="__('admin.journals.no_journals')" /></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
```

- [ ] **Step 5: Add the Russian language strings**

In `lang/ru/admin.php`, add a `journals` key to the `nav` array (right after the `'videos' => 'Видео',` line):

```php
        'journals' => 'Журналы',
```

Then add a new top-level `journals` section (place it right after the closing `],` of the `'videos' => [...]` section):

```php
    'journals' => [
        'title_section' => 'Журналы',
        'subtitle' => 'Выпуски журналов, отображаемые на главной странице.',
        'new_journal' => 'Новый журнал',
        'edit_journal' => 'Редактирование журнала',
        'create_journal' => 'Добавление журнала',
        'title' => 'Название',
        'cover' => 'Обложка',
        'link' => 'Ссылка',
        'published_at' => 'Дата',
        'is_active' => 'Активно',
        'no_journals' => 'Журналов пока нет.',
        'confirm_delete' => 'Удалить этот журнал?',
        'saved_flash' => 'Журнал сохранён.',
        'deleted_flash' => 'Журнал удалён.',
    ],
```

- [ ] **Step 6: Run the test to verify it passes**

Run: `php artisan test --compact tests/Feature/Admin/JournalCrudTest.php`
Expected: PASS (8 passed).

- [ ] **Step 7: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Livewire/Admin/Journals/JournalIndex.php resources/views/livewire/admin/journals/index.blade.php lang/ru/admin.php tests/Feature/Admin/JournalCrudTest.php
git commit -m "feat(journals): admin CRUD for journals

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 4: Route and sidebar navigation

**Files:**
- Modify: `routes/web.php` (import near line 21, route inside the admin group near line 83)
- Modify: `resources/views/components/layouts/admin.blade.php` (nav link inside the `canManageContent()` block)
- Test: `tests/Feature/Admin/RolePermissionsTest.php`

- [ ] **Step 1: Add failing role-authorization assertions**

In `tests/Feature/Admin/RolePermissionsTest.php`, add one line to each of the three `describe` blocks.

In the `removed / unrecognized role` test (after the `admin.activity.index` line):

```php
        $this->actingAs($viewer)->get(route('admin.journals.index'))->assertForbidden();
```

In the `editor role` test (after the `admin.activity.index` line):

```php
        $this->actingAs($editor)->get(route('admin.journals.index'))->assertOk();
```

In the `admin role` "can access every admin route" test (after the `admin.users.index` line):

```php
        $this->actingAs($admin)->get(route('admin.journals.index'))->assertOk();
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact tests/Feature/Admin/RolePermissionsTest.php`
Expected: FAIL — `Route [admin.journals.index] not defined`.

- [ ] **Step 3: Register the route**

In `routes/web.php`, add the import next to the other admin Livewire imports (near the `use App\Livewire\Admin\Videos\VideoIndex as AdminVideoIndex;` line):

```php
use App\Livewire\Admin\Journals\JournalIndex;
```

Inside the `Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(...)` block, right after the videos route (`Route::get('/videos', AdminVideoIndex::class)...`), add:

```php
    Route::get('/journals', JournalIndex::class)->name('journals.index')->middleware('manage-content');
```

- [ ] **Step 4: Add the sidebar link**

In `resources/views/components/layouts/admin.blade.php`, inside the `@if (auth()->user()?->canManageContent())` block, immediately after the Videos `<a>...</a>` nav link, add:

```blade
                <a href="{{ route('admin.journals.index') }}" class="{{ request()->routeIs('admin.journals.*') ? 'active' : '' }}">
                    <i class="fa-solid fa-book-open"></i> {{ __('admin.nav.journals') }}
                </a>
```

- [ ] **Step 5: Run the test to verify it passes**

Run: `php artisan test --compact tests/Feature/Admin/RolePermissionsTest.php`
Expected: PASS.

- [ ] **Step 6: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add routes/web.php resources/views/components/layouts/admin.blade.php tests/Feature/Admin/RolePermissionsTest.php
git commit -m "feat(journals): add admin route and sidebar link

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 5: Drive the public home journals strip from the database

**Files:**
- Modify: `app/Livewire/Home.php` (add `$journals` property + load it in `mount()`)
- Modify: `resources/views/livewire/home.blade.php` (replace the hardcoded `$journals` array, ~lines 202–222)
- Test: `tests/Feature/Livewire/HomeTest.php` (replace the existing journals-section test)

**Reminder:** these two view/test files have unrelated uncommitted changes — edit only the journals region.

- [ ] **Step 1: Replace the journals-section test with a DB-driven one**

In `tests/Feature/Livewire/HomeTest.php`, add these imports at the top (after `use App\Models\Video;`):

```php
use App\Models\Journal;
use Illuminate\Support\Facades\Storage;
```

Replace the existing test block:

```php
    it('renders the journals section with localized heading and issue links', function () {
        Livewire::test(Home::class)
            ->assertSee(__('messages.journals'))
            ->assertSee('https://review.uz/journals/view/8-44-2025')
            ->assertSee('https://review.uz/journals');
    })->group('feature', 'livewire');
```

with:

```php
    it('shows active journals newest first and hides inactive ones', function () {
        Storage::fake('public');
        Storage::disk('public')->put('journals/a.jpg', 'x');
        Journal::factory()->create([
            'title' => 'Active issue',
            'link' => 'https://review.uz/journals/view/active',
            'cover_image' => 'journals/a.jpg',
        ]);
        Journal::factory()->inactive()->create([
            'link' => 'https://review.uz/journals/view/inactive',
        ]);

        Livewire::test(Home::class)
            ->assertSee(__('messages.journals'))
            ->assertSee('https://review.uz/journals/view/active')
            ->assertDontSee('https://review.uz/journals/view/inactive');
    })->group('feature', 'livewire');

    it('hides the journals section when there are no active journals', function () {
        Livewire::test(Home::class)
            ->assertDontSee('home-journals');
    })->group('feature', 'livewire');
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test --compact tests/Feature/Livewire/HomeTest.php`
Expected: FAIL — the section still renders the hardcoded review.uz links / `home-journals` always present, so the new assertions fail.

- [ ] **Step 3: Load journals in the Home component**

In `app/Livewire/Home.php`, add the import (after `use App\Models\Video;`):

```php
use App\Models\Journal;
```

Add a public property (after `public $videos;`):

```php
    public $journals;
```

In `mount()`, after the `$this->videos = ...` line, add:

```php
        $this->journals = Journal::active()->latest('published_at')->take(8)->get();
```

- [ ] **Step 4: Replace the hardcoded journals markup**

In `resources/views/livewire/home.blade.php`, replace the whole hardcoded block (the `@php($journals = [...])` array **and** the `<section class="home-journals">...</section>` that follows it) with:

```blade
    @if ($journals->isNotEmpty())
    <section class="home-journals" aria-labelledby="hj-title">
        <div class="hs-inner">
            <div class="hs-head">
                <h2 class="hs-title" id="hj-title">@lang('messages.journals')</h2>
                <a class="hs-more" href="https://review.uz/journals" target="_blank" rel="noopener noreferrer">review.uz <i class="fa-solid fa-arrow-right hs-more-icon" aria-hidden="true"></i></a>
            </div>
            <div class="hj-grid">
                @foreach ($journals as $journal)
                    <a class="hj-item" href="{{ $journal->link }}" target="_blank" rel="noopener noreferrer">
                        <span class="hj-cover"><img class="hj-img" src="{{ $journal->coverUrl() }}" alt="{{ $journal->title }}" width="200" height="265" loading="lazy"></span>
                    </a>
                @endforeach
            </div>
        </div>
    </section>
    @endif
```

- [ ] **Step 5: Run the test to verify it passes**

Run: `php artisan test --compact tests/Feature/Livewire/HomeTest.php`
Expected: PASS (all Home tests green).

- [ ] **Step 6: Format and commit**

```bash
vendor/bin/pint --dirty --format agent
git add app/Livewire/Home.php resources/views/livewire/home.blade.php tests/Feature/Livewire/HomeTest.php
git commit -m "feat(journals): drive home page journals strip from the database

Co-Authored-By: Claude Opus 4.8 (1M context) <noreply@anthropic.com>"
```

---

### Task 6: Full regression run

- [ ] **Step 1: Run the whole affected surface**

Run: `php artisan test --compact tests/Unit/Models/JournalTest.php tests/Feature/Admin/JournalCrudTest.php tests/Feature/Admin/RolePermissionsTest.php tests/Feature/Livewire/HomeTest.php`
Expected: all PASS (only the environmental `PDO::MYSQL_ATTR_SSL_CA` deprecation notice).

- [ ] **Step 2: Run the broader admin + livewire groups to catch regressions**

Run: `php artisan test --compact --group=admin,livewire`
Expected: all PASS. If anything unrelated fails, check whether it was already failing on `feat/journals-admin` before this work (pre-existing working-tree changes) before treating it as a regression.

---

## Self-Review

**Spec coverage:**
- `journals` table + model (title/cover_image/link/published_at/is_active) → Task 1 ✓
- `scopeActive`, `coverUrl()`, casts, `LogsActivity` → Task 1 ✓
- Admin single-component CRUD (create/edit/delete/list), validation (title/link/date/active, cover required-on-create) → Task 3 ✓
- Cover upload + optimize + delete-old, `journals/` managed prefix → Task 2 + Task 3 ✓
- Route gated by `manage-content` (admin + editor); sidebar link → Task 4 ✓
- Public home strip DB-driven, newest first, hides when empty, starts empty → Task 5 ✓
- i18n (`nav.journals` + `journals.*`, `ru` only per forced admin locale) → Task 3 ✓
- Tests: unit, CRUD, role authorization, home rewrite → Tasks 1, 3, 4, 5 ✓

**Placeholder scan:** No TBD/TODO; every code step contains complete code. ✓

**Type / name consistency:** Property `cover_image` (model column + component prop), upload prop `coverUpload`, methods `startCreate`/`edit`/`save`/`delete`/`cancel`/`resetForm`, scope `active()`, `coverUrl()`, route name `admin.journals.index`, lang namespace `admin.journals.*`, factory state `inactive()` — all consistent across tasks. ✓

**Deviations from the spec (intentional, noted):**
- The admin UI uses an inline expandable form **card** (the actual Videos pattern) rather than a Bootstrap modal — the spec said "modal"; the inline card is the true precedent and the better match.
- The admin list is **not paginated** (mirrors the small, curated Videos list). Ordering is `published_at` desc. If the list grows large later, pagination can be added.
- The media-library picker is **omitted** (spec said "upload only") to keep the form minimal; covers are direct uploads only.
