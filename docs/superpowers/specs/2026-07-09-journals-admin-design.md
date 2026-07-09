# Journals admin — design spec

- **Date:** 2026-07-09
- **Status:** Approved (pending implementation plan)
- **Audience for the feature:** administrators and editors (`canManageContent()`)

## Goal

Let administrators and editors add, edit, and delete "journal" issues from the admin
panel, and drive the public home-page journals strip from that data instead of a
hardcoded Blade array.

## Current state (context)

- There is **no `Journal` model or table**. Journals exist only as a hardcoded array of
  4 entries in `resources/views/livewire/home.blade.php` (~lines 202–222), each with just
  an external `href` (a `review.uz` issue link) and an external `cover` image URL.
- The section heading uses the existing `messages.journals` translation key (present in
  all four locales: uz/kr/ru/en).
- Two admin CRUD precedents exist: **News** (rich, per-language translations, uploaded
  covers, two components) and **Videos** (simple, single component + inline modal, one
  uploaded image + external URL, no translations). Journals mirror **Videos**.
- Roles are a plain string `role` column on `users`. `User::canManageContent()` returns
  `isAdmin() || isEditor()` — exactly the required audience. No Policy classes; auth is
  enforced by route middleware (`manage-content`) plus inline `abort_if` guards.
- Image uploads go through `App\Livewire\Concerns\HandlesImageUploads`
  (`storeUploadedImage`, `deleteStoredImage`) which optimizes and stores on the `public`
  disk. `deleteStoredImage` only deletes paths under an allowlist of managed prefixes
  (`news/`, `pages/`, `videos/`).

## Decisions (locked via brainstorming)

1. **Record shape:** flat, single-language — `title`, `cover_image`, `link`,
   `published_at`, `is_active`. No translations table.
2. **Cover source:** uploaded to local storage only (optimized, under `journals/`). No
   external cover URLs.
3. **Public wiring:** the home strip is DB-driven and **starts empty** — the 4 hardcoded
   entries are removed and NOT seeded. Admins add journals fresh.
4. **Admin UI pattern:** Approach A — a single Livewire component with an inline modal
   form (mirrors the Videos admin).
5. **Defaults:** `title` required; `link` required + URL-validated; `cover` required on
   create, optional on edit; `published_at` defaults to today; `is_active` defaults to on.
   Home strip shows active journals, newest `published_at` first, capped at 8.

## Data model

New migration (via `php artisan make:model Journal -m`), table `journals`:

| Column | Type | Constraints / notes |
|---|---|---|
| `id` | bigIncrements | |
| `title` | string | not null |
| `cover_image` | string | not null; storage path e.g. `journals/{uuid}.jpg` |
| `link` | string | not null; external URL |
| `published_at` | date | not null; drives ordering |
| `is_active` | boolean | not null, default `true` |
| `created_at` / `updated_at` | timestamps | |

Add a composite index on `(is_active, published_at)` to serve the public query
(`active + order by published_at desc`).

## Model — `app/Models/Journal.php`

- `protected $fillable = ['title', 'cover_image', 'link', 'published_at', 'is_active'];`
- `casts()`: `'published_at' => 'date'`, `'is_active' => 'boolean'`.
- `use App\Models\Concerns\LogsActivity;` (mirror News/Video).
- `scopeActive(Builder $query): Builder` → `$query->where('is_active', true)`.
- `coverUrl(): ?string` → returns `Storage::disk('public')->url($this->cover_image)` when
  `cover_image` is set, else `null`. (Storage path only — no http passthrough needed,
  since covers are upload-only.)

## Admin component — `app/Livewire/Admin/Journals/JournalIndex.php`

Single self-contained component (mirrors `App\Livewire\Admin\Videos\*`).

- `#[Layout('components.layouts.admin')]`.
- `use WithPagination, WithFileUploads, HandlesImageUploads;`
- **Public state:** `?int $editingId`, `string $title`, `string $link`,
  `string $published_at`, `bool $is_active`, `$cover` (uploaded file), `bool $showModal`.
  Optional `#[Url] string $search` for the list filter.
- **Methods:**
  - `create(): void` — reset form fields, `is_active = true`, `published_at = today`,
    open modal.
  - `edit(Journal $journal): void` — hydrate form from the model, open modal.
  - `save(): void` — `validate()`; resolve the target model
    (`$journal = $this->editingId ? Journal::findOrFail($this->editingId) : new Journal;`);
    if a new `$cover` was uploaded, store via `storeUploadedImage($this->cover, 'journals')`
    and `deleteStoredImage()` the replaced path; `$journal->fill([...])->save();`
    `logActivity('created'|'updated')`; flash `status`; close modal; reset form state.
  - `delete(Journal $journal): void` — `abort_if(! auth()->user()->canManageContent(), 403)`;
    `deleteStoredImage($journal->cover_image)`; `logActivity('deleted')`; delete; flash.
    Triggered through the shared `open-confirm` modal.
  - `render()` — paginate `Journal::query()->latest('published_at')` (with optional title
    search), `->title(__('admin.nav.journals'))`.
- **Validation `rules()`:**
  - `title` → `['required', 'string', 'max:255']`
  - `link` → `['required', 'url', 'max:2048']`
  - `published_at` → `['required', 'date']`
  - `is_active` → `['boolean']`
  - `cover` → create: `['required', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120']`;
    edit: same but `nullable` (keep existing image when no new upload).
- **Authorization:** the route carries `manage-content`; `delete()` also guards with
  `canManageContent()` defensively (matches the News pattern of inline guards).

### View — `resources/views/livewire/admin/journals/index.blade.php`

Mirrors `resources/views/livewire/admin/videos/index.blade.php`:
- `<x-admin.page-header :title subtitle>` with an "Add journal" button (`wire:click="create"`).
- A Bootstrap `.table`: cover thumbnail, title, link (truncated, opens in new tab),
  `published_at`, and a plain active/inactive badge. Note: the existing
  `<x-admin.status-pill>` maps *news* status strings (draft/published/…), so it is NOT
  reused here — use a small inline badge (e.g. green "Active" / grey "Inactive") driven by
  the `is_active` boolean. Edit/delete actions follow (delete dispatches `open-confirm`).
- `<x-admin.empty-state>` in the `@empty`/no-results branch.
- Paginator footer using `__('admin.common.showing_range', ...)`.
- A modal (shown when `$showModal`) with: title input, cover upload (drag/drop + preview,
  mirroring the videos/news cover control), link input, `published_at` date input, an
  `is_active` toggle, and a save button. Validation errors shown per field.

## Route

Add one line inside the existing `Route::middleware(['auth','admin'])->prefix('admin')->name('admin.')`
group in `routes/web.php`, next to the videos route:

```php
Route::get('/journals', JournalIndex::class)->name('journals.index')->middleware('manage-content');
```

Single route only (modal-based create/edit; no separate create/edit routes).

## Sidebar navigation

In `resources/views/components/layouts/admin.blade.php`, inside the
`@if (auth()->user()?->canManageContent())` block (next to Videos):

```blade
<a href="{{ route('admin.journals.index') }}" class="{{ request()->routeIs('admin.journals.*') ? 'active' : '' }}">
    <i class="fa-solid fa-book-open"></i> {{ __('admin.nav.journals') }}
</a>
```

## Public home wiring

- `app/Livewire/Home.php::render()` — pass
  `'journals' => Journal::active()->orderByDesc('published_at')->limit(8)->get()`.
- `resources/views/livewire/home.blade.php` — remove the hardcoded `$journals` array;
  loop the passed collection instead:
  - `href` → `$journal->link`
  - cover `src` → `$journal->coverUrl()`
  - `alt` → `$journal->title`
- Wrap the whole `home-journals` section in `@if ($journals->isNotEmpty())` so it hides
  cleanly while empty. Heading stays `@lang('messages.journals')`; the external
  "review.uz →" more-link is unchanged.

## Supporting changes

- `App\Livewire\Concerns\HandlesImageUploads::deleteStoredImage()` — add `'journals/'`
  to the managed-prefix allowlist so journal covers can be deleted.
- i18n: add `nav.journals` and a `journals.*` label block (add/edit journal, title,
  cover, link, date, active, saved/deleted flashes, etc.) to `lang/{ru,en,uz,kr}/admin.php`.
  Populate `ru` fully; others follow the existing (partly-empty) convention.

## Authorization summary

- Route: `['auth','admin']` group (must be logged in + `canAccessAdmin()`) plus
  `manage-content` (admin || editor).
- Component: `delete()` re-checks `canManageContent()`.
- Result: administrators and editors can manage journals; accountants and any
  unrecognized role are forbidden.

## Testing plan

- `database/factories/JournalFactory.php` — `title`, `cover_image` (a `journals/*` path),
  `link` (fake URL), `published_at`, `is_active` (+ an `inactive()` state).
- `tests/Unit/Models/JournalTest.php` — `scopeActive` filters, `coverUrl()` resolves a
  storage path and returns null when empty, casts (`published_at` date, `is_active` bool).
- `tests/Feature/Admin/JournalCrudTest.php` (mirror `VideoCrudTest`, `Storage::fake('public')`,
  `UploadedFile::fake()->image(...)`):
  - admin creates a journal with an uploaded cover;
  - validation: missing title / missing link / invalid link URL / missing cover on create;
  - edit updates fields and (optionally) replaces the cover, deleting the old file;
  - keeps the existing cover when no new upload on edit;
  - delete removes the row and its cover file;
  - list renders and title search filters.
- `tests/Feature/Admin/RolePermissionsTest.php` (extend) — `editor` and `admin` get
  `assertOk()` on `admin.journals.index`; `accountant`/unrecognized role get
  `assertForbidden()`.
- `tests/Feature/Livewire/HomeTest.php` (update the existing journals-section test) —
  create active journals and assert their covers/links render; assert an inactive journal
  is excluded; assert the section is hidden when there are no active journals. (Replaces
  the current assertions on the hardcoded review.uz links.)

## Out of scope (YAGNI)

- Per-language titles/descriptions (no translations table).
- PDF/file uploads.
- A dedicated public journals archive/index page.
- Drag-to-reorder (ordering is by `published_at`).
- Seeding/migrating the 4 existing hardcoded review.uz entries.

## Implementation notes / risks

- `resources/views/livewire/home.blade.php` and `tests/Feature/Livewire/HomeTest.php`
  already have **uncommitted changes** in the working tree. Edit around them carefully and
  flag any conflict with in-progress work before overwriting.
- The `admin.php` language files are only fully populated for `ru`; the other locales have
  many empty strings. New keys follow that same convention (fill `ru`, leave others as the
  team fills them), so this doesn't regress anything.
