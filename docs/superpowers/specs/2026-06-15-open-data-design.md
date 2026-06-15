# Open Data Page + Accountant Role — Design Spec

**Date:** 2026-06-15
**Feature:** A public "Open data" (Ochiq ma'lumotlar) page where visitors browse and download datasets published by the Center, plus a new `accountant` admin role whose sole job is uploading and managing those datasets. Modeled on the layout of https://uzsuv.uz/oz/open-data.

## Background

The site is a Laravel 12 + Livewire 4 multilingual CMS (locales: `kr` Cyrillic Uzbek, `uz` Latin Uzbek, `ru`, `en`). Public pages are full-page Livewire components on the `components.layouts.app` theme; admin pages live under `/admin` on `components.layouts.admin`. Content models (News, Page, Category) keep titles in a sibling `*_translations` table. Roles are stored in `users.role` as **free-text** (Postgres `text`, default `viewer`, **no DB constraint**) and enforced purely in application code via `User` helper methods and route middleware.

The reference page shows a vertical list of dataset cards — each a title plus an expandable download area and view/download counts — filtered by **year** and **quarter**. Those datasets are quarterly procurement/financial reports, which is why uploading them is an accountant's responsibility.

## Decisions (locked)

1. **Entry shape:** each open-data entry has a title translated into all four locales and **one** attached downloadable file.
2. **Organization/filter:** by **year + quarter**.
3. **Accountant scope:** the admin panel, but **only** the Open Data manager — no news, users, media, etc. Admins can also manage open data.
4. **Counters:** **download count only** (no view count, no Redis).

## Data Model

### `open_data` table
- `id`
- `year` — integer, required (sane range enforced in validation, e.g. 2000–2100)
- `quarter` — unsigned tinyint, **nullable**, values 1–4 (null = annual / unspecified)
- `file_path` — string, path on the `local` disk relative to `storage/app`
- `file_name` — string, original filename (used as the download name)
- `file_size` — unsigned big integer, bytes
- `file_mime` — string
- `download_count` — unsigned integer, default 0
- `is_published` — boolean, default `true`
- `user_id` — nullable FK to `users` (uploader), `nullOnDelete`
- timestamps

### `open_data_translations` table
- `id`
- `open_data_id` — FK to `open_data`, `cascadeOnDelete`
- `language` — string (`kr`/`uz`/`ru`/`en`) — **note:** column is named `language`, matching `CategoryTranslation`/`PageTranslation` (not News's `lang`)
- `title` — string
- timestamps
- index on `(open_data_id, language)`

### Models
- `App\Models\OpenData`
  - `$fillable`: year, quarter, file_path, file_name, file_size, file_mime, is_published, user_id
  - `casts()`: `year` int, `quarter` int, `file_size` int, `download_count` int, `is_published` bool
  - `use HasFactory, LogsActivity`
  - relations: `translations(): HasMany`, `translation(): HasOne` (current locale), `uploader(): BelongsTo(User)`
  - `scopePublished($q)` → `where('is_published', true)`
  - `title(): string` — current-locale title, falling back to the first available translation, then `''`
  - `fileSizeForHumans(): string`, `fileExtension(): string` (upper-cased, for the format badge)
  - `downloadUrl(): string` → `route('open-data.download', $this)`
- `App\Models\OpenDataTranslation`
  - `$fillable`: open_data_id, language, title
  - `openData(): BelongsTo`
- Factories for both; `OpenDataSeeder` creating a handful of entries across two years/quarters with translations and a placeholder file path (no real file needed in the seeder — see Testing for how downloads are exercised).

## Public Side

### Routes (`routes/web.php`, public group)
- `GET /open-data` → `App\Livewire\OpenData\OpenDataIndex` (full-page Livewire), name `open-data.index`.
- `GET /open-data/{openData}/download` → `App\Http\Controllers\OpenDataDownloadController` (single `__invoke`), name `open-data.download`. Implicit binding resolves `OpenData`; aborts 404 if `! $openData->is_published`; increments `download_count` (atomic `increment()`); returns `Storage::disk('local')->download($openData->file_path, $openData->file_name)`.

### `OpenDataIndex` Livewire component
- Public properties: `?int $year = null`, `?int $quarter = null` (both `#[Url]` so filters are shareable/bookmarkable, matching the reference's query-driven feel).
- `render()`: query `OpenData::published()->with('translations')`, apply year/quarter when set, order by `year desc, quarter desc, id desc`, paginate (e.g. 10/page). Also pass `availableYears` = distinct published years (desc) for the year dropdown, and a static quarter list (1–4 → I–IV).
- View `resources/views/livewire/open-data/open-data-index.blade.php` on the `app` layout: page heading from `messages.open_data`; the two filter dropdowns (year, quarter) wired with `wire:model.live`; a card per entry showing the current-locale title, a format badge (`fileExtension()`) + human size, a download button linking to `open-data.download`, and the download count with an icon. Empty state when no datasets match. Pagination using the site's existing pager style.
- Styling reuses the public "echo" theme classes already used by Videos/News list pages; no new CSS framework.

### Navigation
- Add a top-level "Ochiq ma'lumotlar" item to both the desktop (`echo-desktop-menu`) and mobile menus in `resources/views/components/layouts/app.blade.php`, placed before Contacts, linking to `route('open-data.index')` with `wire:navigate`.
- New key `open_data` in `lang/{kr,uz,ru,en}/messages.php`: uz `Ochiq ma'lumotlar`, kr `Очиқ маълумотлар`, ru `Открытые данные`, en `Open data`.

## Admin Side + Accountant Role

### Role (no migration — `users.role` is free text)
- `User::isAccountant(): bool` → `role === 'accountant'`.
- `User::canManageOpenData(): bool` → `isAdmin() || isAccountant()`.
- Extend `User::canAccessAdmin()` to `in_array($role, ['admin','editor','writer','accountant'], true)` so accountants pass the outer admin gate.
- Accountant added as an option in the Users manager role `<select>` and its validation `in:` rule; role label `admin.users.role_accountant` (uz/kr/ru/en).

### Middleware
- `App\Http\Middleware\EnsureCanManageOpenData` — 403 unless `auth()->user()?->canManageOpenData()`. Register alias `manage-open-data` in `bootstrap/app.php` alongside the existing aliases.

### Routes (`routes/web.php`, admin group)
- `GET /admin/open-data` → `App\Livewire\Admin\OpenData\OpenDataIndex`, name `open-data.index`, middleware `manage-open-data`.

### `Admin\OpenData\OpenDataIndex` Livewire component
- Same shape as `Admin\Videos\VideoIndex`: `WithFileUploads`, an inline create/edit form toggled by `$showForm`, list of existing entries.
- Form fields: `year` (number), `quarter` (select: —/I/II/III/IV), four title inputs (`titles.kr|uz|ru|en`, primary `uz` required) rendered with the existing `lang-tabs` pattern, `is_published` checkbox, and `fileUpload` (required on create, optional on edit — keep existing file if none supplied).
- `rules()`: `year` required int between 2000 and 2100; `quarter` nullable int in 1..4; `titles.uz` required string; other titles nullable strings (max 255); `fileUpload` required (create) / nullable (edit), `file`, `mimes:pdf,xls,xlsx,csv,doc,docx`, `max:20480`.
- `save()`: when a file is supplied, `store('open-data', 'local')`, capture original name/size/mime, delete the previous file on replace; upsert the `OpenData` row (set `user_id` to the actor on create) and its translations (create/update per locale, skipping empty non-primary titles). Flash a saved message.
- `delete($id)`: confirm-modal first, then delete the stored file and the row (translations cascade).
- Page title via `->title(__('admin.open_data.title_section'))`. Reuses admin Blade components: `x-admin.page-header`, `x-admin.empty-state`, `x-admin.status-pill` (published/draft), confirm-modal.
- List columns: title (current locale), year + quarter, format badge, download count, status pill, edit/delete actions.

### Sidebar visibility (`components.layouts.admin`)
- Add an "Ochiq ma'lumotlar" link gated by `auth()->user()?->canManageOpenData()`.
- Wrap the existing Overview / Content / Administration sections so they are **hidden when** `auth()->user()->isAccountant()` — an accountant sees only the Open Data link.
- `App\Livewire\Admin\Dashboard`: if the actor `isAccountant()`, `mount()`/`render()` redirects to `route('admin.open-data.index')` (an accountant never lands on the dashboard).
- New admin lang keys under `admin.open_data` (title_section, subtitle, year, quarter, title, file, file_help, published, download_count, no_entries, confirm_delete, saved_flash, deleted_flash, quarter labels) and `admin.nav.open_data`, in all four locales.

## File Handling & Storage

- Disk: `local` (private), directory `open-data/`, stored filename = UUID + original extension; original name retained in `file_name`.
- Downloads always flow through `OpenDataDownloadController`, never a direct public URL, so the counter is authoritative and unpublished files can't be fetched.
- **Deployment caveat:** `storage/app/open-data` must be writable by the web-server user (`www-data`) — the same class of permission issue addressed for `storage/app/public`. Document it; do not rely on world-writable in production.

## Error Handling

- Download of a non-existent or unpublished entry → 404.
- Download when the file is missing on disk → `Storage::download` throws; let it 404 (the row exists but the file doesn't — log nothing special).
- Admin upload validation failures surface inline (Russian messages via the existing `lang/ru/validation.php` + `admin.open_data` attribute names).
- `title()` and `fileExtension()` tolerate missing translations / odd filenames without throwing.

## Testing (Pest)

**Public**
- `/open-data` returns 200 and lists only published entries.
- Year and quarter filters narrow the list correctly.
- Download route increments `download_count` and returns the file with the original name (use `Storage::fake('local')`).
- Download of an unpublished entry → 404.

**Role & access**
- `isAccountant`, `canManageOpenData`, `canAccessAdmin` include accountant.
- Accountant: 200 on `/admin/open-data`; 403 on news/categories/users/media/activity; dashboard redirects to open-data.
- Editor/writer: 403 on `/admin/open-data`. Admin: 200 on `/admin/open-data` and the rest.

**Admin CRUD**
- Create with a fake file + four titles persists the row, translations, uploader, and file metadata.
- Edit without a new file keeps the existing file; edit with a new file replaces and deletes the old one.
- Delete removes the row and the stored file.
- Validation: missing year, bad quarter, disallowed mime, oversized file, missing primary `uz` title each rejected.
- Activity log records create/update/delete.

**i18n/nav**
- Public nav renders the Open Data link; admin page title is translated.

## Success Criteria

- A visitor can browse the Open Data page, filter by year/quarter, and download a file; the download count rises.
- An accountant logs in, sees only the Open Data manager, and can upload/edit/delete datasets with 4-language titles; they cannot reach any other admin section.
- Admins can manage open data too; editors/writers and the public cannot.
- All listed tests pass; `vendor/bin/pint --dirty` clean.

## Out of Scope (YAGNI)

- The reference page's "Useful links" external-resource section.
- Multiple files per entry.
- View counts (download count only).
- A DB CHECK constraint on `users.role` (kept free-text, matching current state).
