# Admin Panel Improvements + Russian i18n — Design

**Date:** 2026-05-05
**Status:** Approved (5 sections, brainstormed and confirmed)
**Owner:** Implementation by Claude, review by user

## Summary

Extend the existing admin panel with three classes of improvements — missing CRUD coverage, news workflow features, and UX polish — and translate the entire admin UI to Russian. Implementation is split into four phases that ship independently.

## Goals

1. Eliminate features where data exists in the DB but cannot be edited via the admin (Pages, Videos, Users).
2. Reduce friction in news editing (auto-slug, bulk actions).
3. Replace browser-native confirmations and other rough edges with styled, consistent components.
4. Translate the entire admin UI to Russian; the admin always renders in Russian regardless of public-site locale.

## Non-goals

- Profile/password self-service page.
- 2FA, SSO, or any auth beyond the existing rate-limited password login.
- Admin locale switcher — admin is fixed RU.
- TinyMCE editor UI translation (we translate our own UI; TinyMCE toolbar stays English).
- Translating the admin to languages other than Russian. Stubs are seeded for `en/uz/kr` so future translation is mechanical.
- DB-backed media library with usage tracking. Filesystem-only.
- News duplication, copy-translation-from-KR helper, profile page (deferred per scoping).
- Per-user fine-grained permissions. The single `admin` role gate stays.

## Architecture

### Locale resolution

The public site uses session-based locale via `App\Http\Middleware\SetLocale`. Admin needs to be unaffected and always render in Russian.

- New middleware `App\Http\Middleware\SetAdminLocale` calls `app()->setLocale('ru')`.
- Registered as part of the existing `admin` middleware alias group in `bootstrap/app.php` so it runs only on `/admin/*` routes.
- Public site behavior is untouched.

### Translation file layout

All admin strings live in `lang/ru/admin.php`, sectioned by feature:

```php
return [
    'nav' => [...],
    'common' => ['save' => 'Сохранить', ...],
    'news' => [...],
    'categories' => [...],
    'tags' => [...],
    'users' => [...],
    'pages' => [...],
    'videos' => [...],
    'media' => [...],
    'activity' => [...],
    'validation' => [...],
];
```

Used in views via `__('admin.section.key')` and in components via `trans('admin.section.key')`.

Empty stubs at `lang/{en,uz,kr}/admin.php` are created so a translator can fill them later without code changes. They are not used today (admin is forced to `ru` and Laravel will read keys from `lang/ru/admin.php`). Each stub is committed with the same key skeleton and empty-string values to make a future find-and-translate pass mechanical.

### String extraction pattern

Phase 1 replaces every hardcoded English string in admin views, Livewire components, and the auth login page with `__('admin.…')`. This happens *before* new screens are built so subsequent phases follow the i18n pattern from day one.

### Authorization

All new admin routes use the existing `auth + admin` middleware stack. No new roles or policies in this round.

## Phases

### Phase 1: Foundation + i18n

**Scope:** translation infrastructure only. No feature work, no schema changes.

- Add `SetAdminLocale` middleware; register on the `admin` middleware alias.
- Create `lang/ru/admin.php` with full key inventory.
- Create empty stubs at `lang/{en,uz,kr}/admin.php`.
- Replace hardcoded strings across:
  - `resources/views/components/layouts/admin.blade.php`
  - `resources/views/components/layouts/auth.blade.php`
  - `resources/views/livewire/admin/**/*.blade.php`
  - `resources/views/livewire/auth/login.blade.php`
  - Validation messages in `App\Livewire\Admin\**` components (override via `messages()` method on each component).
- Tests: `AdminLocaleMiddlewareTest` asserts `app()->getLocale() === 'ru'` on any `/admin/*` route regardless of session locale; one render assertion per admin page that the rendered HTML contains a known Russian string.

**Acceptance:** every existing admin screen renders in Russian. No English strings visible. All existing tests still pass.

### Phase 2: Quick wins

#### Auto-slug from title

- `App\Livewire\Admin\News\NewsForm`: when on the create screen and the slug field has not been manually edited, the slug fills from `Str::slug($translations.kr.title)` reactively.
- "Manually edited" is tracked client-side via Alpine local state on the slug field (`@input.once="manual = true"`). Survives Livewire re-renders because it lives on the DOM element, not the component.
- On the edit screen (`$news->exists`), auto-slug is disabled at mount; slug is treated as user-locked.

#### Styled confirmation modals

- New Blade component `resources/views/components/admin/confirm-modal.blade.php`. Single instance rendered at the bottom of `admin.blade.php`.
- Listens for an Alpine event `open-confirm` with `{ message, onConfirm: () => ... }`.
- Replaces every `wire:confirm="..."` site usage in admin views with `@click="$dispatch('open-confirm', { message: '...', onConfirm: () => $wire.delete({{ id }}) })"`.
- All confirmation strings from `lang/ru/admin.php`.

#### Drag-and-drop on cover image input

- `NewsForm` cover image field gets a styled drop-zone wrapper.
- Alpine handlers on `dragover`/`dragleave`/`drop` show a hover state and forward the dropped file to the underlying `<input type="file" wire:model="cover_uploads.{locale}">` by setting `files` and dispatching `change`.
- The native input remains for keyboard/screen-reader users.

**Tests:**
- `NewsCrudTest`: auto-slug fills from KR title on create; remains in sync until user types in the slug field; not applied on edit.
- New `ConfirmModalComponentTest`: the modal markup is rendered once in the layout; dispatched event triggers the bound action.
- Drag-drop is JS-only and explicitly not covered by Pest tests in this round.

### Phase 3: Missing CRUD

Three new admin sections following the existing `News`/`Categories`/`Tags` patterns.

#### User management — `/admin/users`

- `App\Livewire\Admin\Users\UserIndex`. Columns: name, email, role pill, last login, actions.
- A small migration in Phase 3 adds `last_login_at` (nullable timestamp) to `users`; the existing login Volt component (`resources/views/livewire/auth/login.blade.php`) is updated to set `last_login_at = now()` on successful authentication. This is the only schema change in Phase 3.
- Filter by role.
- Inline create/edit form: name, email, role select (`admin/writer/editor/viewer`), optional new password (blank = unchanged).
- Actions: edit, reset password, delete.
- Reset password generates a 16-character random password using `Str::password(16)`, sets it on the user (hashed), and surfaces the plain-text value once via `session()->flash('reset_password_plain', ...)`. The next render shows it in a copyable banner with a "Copy" button. It is never stored anywhere except the encrypted session for that one render.
- Self-protection rules:
  - An admin cannot delete themselves.
  - An admin cannot demote themselves to a non-admin role.
- Sidebar gets a new "Administration" section label with "Users" link.

#### Pages CRUD — `/admin/pages`

- `App\Livewire\Admin\Pages\PageIndex` — list with slug + per-language indicators.
- `App\Livewire\Admin\Pages\PageForm` — multi-language tabs (`kr/uz/ru/en`), TinyMCE for content. Single image upload per page (matches existing `pages.image` schema; not per-translation).
- Slug editable but warned: changing it can break the matching public route.

#### Videos CRUD — `/admin/videos`

- `App\Livewire\Admin\Videos\VideoIndex` — list with thumbnail, title, URL.
- Inline create/edit form: title, image upload, URL (validated as URL).
- Not multi-language (matches `videos` schema).

#### Shared upload helper

- Extract the per-locale upload logic in `NewsForm` into a trait `App\Livewire\Concerns\HandlesImageUploads` with:
  - `storeUploadedImage(UploadedFile $file, string $folder): string`
  - `deleteStoredImage(?string $path): void` — only deletes when path is on the public disk under `news/`, `pages/`, `videos/`.
- `NewsForm`, `PageForm`, `VideoIndex` consume the trait. (Users have no avatar in this round.)

**Tests:** `UserCrudTest`, `PageCrudTest`, `VideoCrudTest` follow the shape of the existing `NewsCrudTest`. Each covers create / validation errors / edit / delete + the resource-specific rules (self-protection on users, slug change warning on pages). One additional test verifies `users.last_login_at` is updated on successful login.

### Phase 4: Bulk actions + Media library + Activity log

#### Bulk actions on news

- `NewsIndex` adds a checkbox column. Property `selected: array<int>`.
- Header checkbox selects all visible (current page only — explicitly NOT cross-page).
- Action bar appears above the table when `count(selected) > 0` with: Publish, Unpublish, Delete + count.
- Bulk publish/unpublish: single update query for selected ids.
- Bulk delete: loops the existing per-item `delete()` so file cleanup is reused.
- All bulk actions go through the styled confirmation modal from Phase 2.

#### Media library — `/admin/media` + inline picker

- New page listing every file in `storage/app/public/news/{covers,inline}/`.
- Source of truth is the filesystem. No DB table. Trade-off: cannot detect "orphan" images (ones no article references). Acceptable for current scale.
- For each file: thumbnail, filename, dimensions, size, modification date, delete button.
- Search by filename + filter by folder (`covers` / `inline`).
- Pagination via array slicing (filesystem listing is small enough that we don't need a database).
- Inline picker: `App\Livewire\Admin\Media\MediaPicker` rendered as a modal, opened from `NewsForm` via "Choose existing" button next to the cover upload. On select, dispatches `cover-selected` with the file path; `NewsForm` listens and writes to `translations.{locale}.image_url`.
- TinyMCE integration: configure `file_picker_callback` to open the same picker for inline images.
- Delete confirmation explicitly warns: "Articles referencing this image will show a broken image."

#### Activity log

- New migration creates `activity_log` table:

```
id              bigserial primary key
user_id         bigint nullable references users(id) on delete set null
subject_type   varchar(255) not null      // model class
subject_id     bigint nullable             // null when subject is deleted before log row
action          varchar(64) not null       // created|updated|deleted|published|unpublished
changes         jsonb nullable             // diff payload
created_at      timestamp not null
```

- `App\Models\Activity` with relations to `user` (belongsTo) and `subject` (morphTo).
- Trait `App\Models\Concerns\LogsActivity` adds `logActivity(string $action, array $changes = [])` writing one row.
- Wired into `NewsForm::save()`, `NewsIndex::delete()`, bulk actions, `UserIndex` / `PageForm` / `VideoIndex` mutations.
- Diff strategy:
  - Capture the original loaded model attributes at mount.
  - On save, compute changed keys; for `content` field specifically, store `['content' => 'changed']` (no HTML diff to keep rows small).
  - Skip noisy fields: `updated_at`, `view_count`. Skip sensitive fields: `password`, `remember_token`.
- `/admin/activity` index: paginated table with user, action, subject (linked when subject still exists), summary of changes, time. Filter by user / action / subject type.
- Inline panel on `NewsForm`: collapsible "Activity" card on the sidebar showing this article's last 10 events.
- No auto-prune. Editorial team is small; table will stay tiny.

**Tests:**
- `BulkActionsTest`: select 3 news, bulk-publish → all three become `published`; bulk-delete removes records and managed cover files.
- `MediaLibraryTest`: list endpoint returns expected files; delete removes the file from disk; non-admin gets 403.
- `ActivityLogTest`: creating/updating/deleting news writes one row each with correct `user_id`, `action`, and `changes` payload (asserting `content` is flagged but no HTML stored; password fields excluded).

## Data flow

- News editing flow: user opens form → Livewire mounts with `$news` and translations → user edits via TinyMCE/inputs → save runs validation → `HandlesImageUploads::storeUploadedImage` for any new files → `News::updateOrCreate` translations → `tags()->sync($tag_ids)` → `LogsActivity::logActivity('updated', $diff)` → flash session message → redirect to edit screen.
- Bulk action flow: user checks rows → action button click → confirmation modal opens → on confirm, Livewire method runs DB update + per-item file cleanup + activity log writes → flash + re-render with cleared `selected`.
- Media picker flow: NewsForm "Choose existing" → opens `MediaPicker` modal → user picks file → dispatches `cover-selected` event → NewsForm listener sets `translations.{locale}.image_url` → modal closes.

## Error handling

- Validation errors surface inline next to fields (existing pattern, kept).
- Failed file uploads: Livewire's standard `WithFileUploads` error handling; surface validation message under the input.
- Activity log writes are best-effort: wrap each in try/catch logging to Laravel log; never block a save because of activity-logging failure.
- Self-protection on users: if an admin attempts to delete or demote themselves, the action is rejected with a visible error toast.

## Testing strategy

- Per-phase test gate: all existing tests + all new tests pass before that phase ships.
- Pest feature tests live next to existing ones in `tests/Feature/Admin/`.
- Browser-side behaviors (drag-drop, Alpine modal interactions) are not covered by Pest in this round; explicitly noted as a gap.
- After each phase: `vendor/bin/pint --dirty --format agent` + `php artisan test --compact`.

## Risks

| # | Risk | Mitigation |
|---|---|---|
| 1 | Hardcoded English strings missed during extraction | Phase 1 ends with grep audit for English-shaped tokens in admin views; manual review. |
| 2 | TinyMCE UI in English | Out of scope; documented in non-goals. |
| 3 | Auto-slug "manually edited" flag drifts on Livewire re-render | Use Alpine DOM state, not Livewire property. |
| 4 | Activity log size if HTML diffs stored | Diff content as a flag only; no before/after HTML. |
| 5 | Bulk delete loops per-item | Acceptable for small editorial workload. |
| 6 | Media library deletion breaks articles using the image | Confirmation modal warns explicitly; no auto-scan. |
| 7 | New `activity_log` migration must work on the recently repaired prod schema | Migration uses standard `$table->id()` (same pattern that produced correct `videos` table). Phase 4 test plan validates against a copy of repaired prod schema before merging. |

## Spec sections checklist

- [x] Architecture (locale resolution, translation layout, extraction pattern, authorization)
- [x] Phase 1 — i18n foundation
- [x] Phase 2 — auto-slug, modals, drag-drop
- [x] Phase 3 — Users / Pages / Videos CRUD + shared trait
- [x] Phase 4 — bulk actions, media library, activity log
- [x] Data flow
- [x] Error handling
- [x] Testing strategy
- [x] Risks + non-goals
