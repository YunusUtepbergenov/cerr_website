# Admin Panel Improvements + Russian i18n Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Translate the entire admin panel to Russian and add the missing CRUD coverage (Users / Pages / Videos), news workflow improvements (auto-slug + bulk actions), and UX polish (styled confirm modal, drag-drop uploads, media library, activity log) defined in the design at `docs/superpowers/specs/2026-05-05-admin-improvements-i18n-design.md`.

**Architecture:** Four-phase incremental rollout. Each phase produces shippable, tested software and ends with `vendor/bin/pint --dirty --format agent` + `php artisan test --compact` passing. Phase 1 establishes the i18n pattern so Phases 2–4 use it from day one. Subsequent phases each add one cluster of features.

**Tech Stack:** Laravel 12, Livewire 4 + Volt 1, Pest 3, PostgreSQL, Bootstrap 5, Alpine.js 3, TinyMCE 7.

---

## File map

### Phase 1 — i18n foundation
- **Create** `app/Http/Middleware/SetAdminLocale.php`
- **Modify** `bootstrap/app.php` (chain `SetAdminLocale` into the `admin` middleware alias)
- **Create** `lang/ru/admin.php` (full key inventory for every admin string)
- **Create** `lang/en/admin.php`, `lang/uz/admin.php`, `lang/kr/admin.php` (empty-string stubs sharing the same key skeleton)
- **Modify** every admin view to use `__('admin.…')`:
  - `resources/views/components/layouts/admin.blade.php`
  - `resources/views/components/layouts/auth.blade.php`
  - `resources/views/livewire/auth/login.blade.php`
  - `resources/views/livewire/admin/dashboard.blade.php`
  - `resources/views/livewire/admin/news/index.blade.php`
  - `resources/views/livewire/admin/news/form.blade.php`
  - `resources/views/livewire/admin/categories/index.blade.php`
  - `resources/views/livewire/admin/tags/index.blade.php`
- **Modify** Livewire components that surface user-facing strings:
  - `app/Livewire/Admin/News/NewsIndex.php` (flash messages)
  - `app/Livewire/Admin/News/NewsForm.php` (validation messages, flash)
  - `app/Livewire/Admin/Categories/CategoryIndex.php` (flash)
  - `app/Livewire/Admin/Tags/TagIndex.php` (flash)
- **Create** `tests/Feature/Admin/AdminLocaleMiddlewareTest.php`

### Phase 2 — Quick wins
- **Create** `resources/views/components/admin/confirm-modal.blade.php` (single shared modal)
- **Modify** `resources/views/components/layouts/admin.blade.php` (mount modal, dispatch helpers)
- **Modify** every admin index view to replace `wire:confirm` with the new event-driven pattern:
  - `resources/views/livewire/admin/news/index.blade.php`
  - `resources/views/livewire/admin/categories/index.blade.php`
  - `resources/views/livewire/admin/tags/index.blade.php`
  - `resources/views/livewire/admin/news/form.blade.php`
- **Modify** `resources/views/livewire/admin/news/form.blade.php` to add:
  - Auto-slug Alpine logic on the create screen
  - Drag-drop wrapper around cover image inputs
- **Create** `tests/Feature/Admin/AutoSlugTest.php`
- **Create** `tests/Feature/Admin/ConfirmModalComponentTest.php`

### Phase 3 — Missing CRUD (Users, Pages, Videos)
- **Create migration** `database/migrations/{ts}_add_last_login_at_to_users_table.php`
- **Modify** `app/Models/User.php` (cast/fillable for `last_login_at`)
- **Modify** `resources/views/livewire/auth/login.blade.php` (write `last_login_at`)
- **Create** `app/Livewire/Concerns/HandlesImageUploads.php` (shared trait)
- **Modify** `app/Livewire/Admin/News/NewsForm.php` to use the trait
- **Create** Users CRUD:
  - `app/Livewire/Admin/Users/UserIndex.php`
  - `resources/views/livewire/admin/users/index.blade.php`
- **Create** Pages CRUD:
  - `app/Livewire/Admin/Pages/PageIndex.php`
  - `app/Livewire/Admin/Pages/PageForm.php`
  - `resources/views/livewire/admin/pages/index.blade.php`
  - `resources/views/livewire/admin/pages/form.blade.php`
- **Create** Videos CRUD:
  - `app/Livewire/Admin/Videos/VideoIndex.php`
  - `resources/views/livewire/admin/videos/index.blade.php`
- **Modify** `routes/web.php` (register new routes)
- **Modify** `resources/views/components/layouts/admin.blade.php` (sidebar links + new "Administration" section)
- **Extend** `lang/ru/admin.php` with `users`, `pages`, `videos` sections
- **Create** tests:
  - `tests/Feature/Admin/UserCrudTest.php`
  - `tests/Feature/Admin/PageCrudTest.php`
  - `tests/Feature/Admin/VideoCrudTest.php`
  - `tests/Feature/Admin/LastLoginUpdateTest.php`

### Phase 4 — Bulk + Media + Activity
- **Modify** `app/Livewire/Admin/News/NewsIndex.php` (`selected[]`, `bulkPublish/Unpublish/Delete`)
- **Modify** `resources/views/livewire/admin/news/index.blade.php` (checkbox column + action bar)
- **Create migration** `database/migrations/{ts}_create_activity_log_table.php`
- **Create** `app/Models/Activity.php`
- **Create** `app/Models/Concerns/LogsActivity.php` (trait)
- **Modify** existing Livewire CRUD components to call `logActivity()`
- **Create** `app/Livewire/Admin/Activity/ActivityIndex.php` + view
- **Create** `app/Livewire/Admin/Media/MediaIndex.php` + view (filesystem-backed)
- **Create** `app/Livewire/Admin/Media/MediaPicker.php` + view (modal)
- **Modify** `resources/views/livewire/admin/news/form.blade.php` (mount picker, "Choose existing" button, TinyMCE `file_picker_callback`)
- **Modify** `routes/web.php` (register new routes)
- **Modify** `resources/views/components/layouts/admin.blade.php` (sidebar)
- **Extend** `lang/ru/admin.php` with `activity`, `media`, `bulk` sections
- **Create** tests:
  - `tests/Feature/Admin/BulkActionsTest.php`
  - `tests/Feature/Admin/MediaLibraryTest.php`
  - `tests/Feature/Admin/ActivityLogTest.php`

---

# Phase 1 — i18n foundation

**Objective:** every admin screen renders in Russian regardless of session locale; every existing test still passes; no English strings visible in admin views or auth pages.

**Order rationale:** middleware first (so we can test it without changing strings), then translation file, then per-view extraction. After each view extraction, we run tests; nothing in admin is functionally changed.

---

## Task 1.1: Create the SetAdminLocale middleware

**Files:**
- Create: `app/Http/Middleware/SetAdminLocale.php`

- [ ] **Step 1: Create the middleware file**

Create `app/Http/Middleware/SetAdminLocale.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetAdminLocale
{
    /**
     * Force the application locale to Russian for any admin request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        app()->setLocale('ru');

        return $next($request);
    }
}
```

- [ ] **Step 2: Format with Pint**

Run: `vendor/bin/pint --dirty --format agent`
Expected: `{"tool":"pint","result":"passed"}` or `"fixed"` for the new file.

---

## Task 1.2: Register the middleware on the admin alias

**Files:**
- Modify: `bootstrap/app.php`

- [ ] **Step 1: Read the current admin middleware registration**

Run: `grep -n "EnsureAdmin\|admin" bootstrap/app.php`
Expected: `'admin' => EnsureAdmin::class,` line is present.

- [ ] **Step 2: Wrap the admin alias to chain SetAdminLocale before EnsureAdmin**

Change in `bootstrap/app.php`:

Replace:
```php
        $middleware->alias([
            'admin' => EnsureAdmin::class,
        ]);
```

With:
```php
        $middleware->alias([
            'admin' => [
                SetAdminLocale::class,
                EnsureAdmin::class,
            ],
        ]);
```

Also add the import near the top, beside the existing `EnsureAdmin` import:
```php
use App\Http\Middleware\SetAdminLocale;
```

- [ ] **Step 3: Format with Pint**

Run: `vendor/bin/pint --dirty --format agent`
Expected: `passed` or `fixed`.

---

## Task 1.3: Write the locale middleware test

**Files:**
- Create: `tests/Feature/Admin/AdminLocaleMiddlewareTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/AdminLocaleMiddlewareTest.php`:

```php
<?php

use App\Models\User;

describe('SetAdminLocale middleware', function () {
    it('forces Russian on admin routes regardless of session locale', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        session(['locale' => 'en']);

        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk();

        expect(app()->getLocale())->toBe('ru');
    })->group('feature', 'admin');

    it('does not change locale on public routes', function () {
        session(['locale' => 'en']);

        $this->get('/')->assertOk();

        expect(app()->getLocale())->toBe('en');
    })->group('feature', 'admin');
});
```

- [ ] **Step 2: Run the test, expect pass**

Run: `php artisan test tests/Feature/Admin/AdminLocaleMiddlewareTest.php`
Expected: 2 tests, all pass.

(If first run fails because the existing dashboard view contains English strings rendered through `__()` calls that don't yet exist, that's OK — the test asserts locale, not content. The page should still render.)

---

## Task 1.4: Create lang/ru/admin.php with the full key inventory

**Files:**
- Create: `lang/ru/admin.php`

- [ ] **Step 1: Write the file**

Create `lang/ru/admin.php`:

```php
<?php

return [
    'nav' => [
        'overview' => 'Обзор',
        'content' => 'Контент',
        'dashboard' => 'Панель',
        'news' => 'Новости',
        'categories' => 'Категории',
        'tags' => 'Теги',
        'view_site' => 'Открыть сайт',
        'sign_out' => 'Выйти',
    ],

    'common' => [
        'save' => 'Сохранить',
        'cancel' => 'Отмена',
        'edit' => 'Изменить',
        'delete' => 'Удалить',
        'create' => 'Создать',
        'back' => 'Назад',
        'view' => 'Просмотр',
        'all' => 'Все',
        'search' => 'Поиск',
        'status' => 'Статус',
        'languages' => 'Языки',
        'updated' => 'Обновлено',
        'updated_at' => 'Обновлено :time',
        'actions' => 'Действия',
        'name' => 'Имя',
        'slug' => 'Slug',
        'active' => 'Активно',
        'inactive' => 'Неактивно',
        'saved' => 'Сохранено.',
        'deleted' => 'Удалено.',
        'required' => '*',
        'none' => '— Нет —',
        'no_results' => 'Ничего не найдено.',
        'loading' => 'Загрузка…',
        'saving' => 'Сохранение…',
        'uploading' => 'Загрузка…',
        'showing_range' => 'Показано :from–:to из :total',
        'confirm_delete' => 'Удалить запись?',
        'remove' => 'Удалить',
    ],

    'auth' => [
        'sign_in' => 'Вход в админ-панель',
        'email' => 'Email',
        'password' => 'Пароль',
        'remember_me' => 'Запомнить меня',
        'sign_in_button' => 'Войти',
        'signing_in' => 'Вход…',
        'invalid_credentials' => 'Неверный email или пароль.',
        'too_many_attempts' => 'Слишком много попыток. Попробуйте через :seconds сек.',
    ],

    'dashboard' => [
        'title' => 'Панель',
        'welcome' => 'С возвращением, :name.',
        'total_news' => 'Всего новостей',
        'published' => 'Опубликовано',
        'drafts' => 'Черновики',
        'categories_tags' => 'Категории / Теги',
        'recent_news' => 'Последние новости',
        'all_news' => 'Все новости',
        'no_news' => 'Новостей пока нет.',
        'create_first' => 'Создать первую статью',
    ],

    'news' => [
        'title_section' => 'Новости',
        'subtitle' => 'Управление статьями, переводами и публикацией',
        'new_article' => 'Новая статья',
        'edit_article' => 'Редактирование статьи',
        'create_article' => 'Создание новой многоязычной статьи',
        'last_saved' => 'последнее сохранение :time',
        'back_to_list' => 'К списку',
        'search_placeholder' => 'Поиск по заголовку или slug…',
        'all_statuses' => 'Все статусы',
        'all_categories' => 'Все категории',
        'title' => 'Заголовок',
        'title_placeholder' => 'Заголовок на :locale',
        'short_description' => 'Краткое описание',
        'short_description_placeholder' => 'Одно-два предложения',
        'content' => 'Содержание',
        'seo' => 'SEO (:locale)',
        'seo_title' => 'SEO заголовок',
        'seo_title_placeholder' => 'По умолчанию — заголовок',
        'seo_description' => 'SEO описание',
        'seo_description_placeholder' => 'Не более 160 символов',
        'cover_image' => 'Обложка (:locale)',
        'cover_specs' => 'JPG, PNG, WebP — до 5 МБ',
        'remove_cover' => 'Удалить обложку',
        'confirm_remove_cover' => 'Удалить текущую обложку?',
        'publishing' => 'Публикация',
        'slug_help' => 'Используется в URL статьи.',
        'status_draft' => 'Черновик — виден только админам',
        'status_published' => 'Опубликовано — на сайте',
        'status_auto_publish' => 'Автопубликация — по расписанию',
        'status_disabled' => 'Отключено — скрыто с сайта',
        'scheduled_at' => 'Время публикации',
        'show_on_homepage' => 'Показать на главной',
        'save_article' => 'Сохранить статью',
        'view_on_site' => 'Открыть на сайте',
        'category' => 'Категория',
        'tags' => 'Теги',
        'tags_selected' => ':count выбрано',
        'no_tags_yet' => 'Тегов пока нет.',
        'create_one' => 'Создать тег',
        'category_label' => 'Категория',
        'views' => 'Просмотры',
        'no_news_match' => 'Статьи по фильтру не найдены.',
        'try_clear_filters' => 'Очистите фильтры или',
        'create_new_article' => 'создайте новую статью',
        'confirm_delete' => 'Удалить эту статью со всеми переводами?',
        'deleted_flash' => 'Статья удалена.',
        'saved_flash' => 'Статья сохранена.',
    ],

    'categories' => [
        'title_section' => 'Категории',
        'subtitle' => 'Группировка статей. Каждая категория поддерживает 4 языка.',
        'new_category' => 'Новая категория',
        'edit_category' => 'Редактирование категории',
        'create_category' => 'Создание категории',
        'slug_help' => 'Используется в URL категории.',
        'name_label' => 'Название',
        'no_categories' => 'Категорий пока нет.',
        'no_categories_help' => 'Создайте первую, чтобы группировать статьи.',
        'confirm_delete' => 'Удалить категорию? Связанные новости станут без категории.',
        'saved_flash' => 'Категория сохранена.',
        'deleted_flash' => 'Категория удалена.',
    ],

    'tags' => [
        'title_section' => 'Теги',
        'subtitle' => 'Свободные метки для статей.',
        'new_tag' => 'Новый тег',
        'edit_tag' => 'Редактирование тега',
        'create_tag' => 'Создание тега',
        'name_label' => 'Название',
        'articles_count' => 'Статей',
        'no_tags' => 'Тегов пока нет.',
        'no_tags_help' => 'Теги помогают читателям находить связанный контент.',
        'confirm_delete' => 'Удалить этот тег?',
        'saved_flash' => 'Тег сохранён.',
        'deleted_flash' => 'Тег удалён.',
    ],

    'validation' => [
        'required' => 'Поле обязательно.',
        'string' => 'Должна быть строка.',
        'max' => 'Не более :max символов.',
        'unique' => 'Такое значение уже существует.',
        'image' => 'Файл должен быть изображением.',
        'mimes' => 'Допустимые форматы: :values.',
        'date' => 'Должна быть корректная дата.',
        'in' => 'Недопустимое значение.',
        'exists' => 'Не найдено.',
    ],
];
```

- [ ] **Step 2: Format with Pint**

Run: `vendor/bin/pint --dirty --format agent`
Expected: `passed` or `fixed`.

---

## Task 1.5: Create empty stubs for the other locales

**Files:**
- Create: `lang/en/admin.php`
- Create: `lang/uz/admin.php`
- Create: `lang/kr/admin.php`

- [ ] **Step 1: Generate stubs by copying ru/admin.php with empty values**

Run a one-liner that loads the RU file, recursively replaces every leaf value with empty string, and writes the result to each stub:

```bash
php -r '
$src = require "lang/ru/admin.php";
$blank = function ($a) use (&$blank) {
    foreach ($a as $k => $v) { $a[$k] = is_array($v) ? $blank($v) : ""; }
    return $a;
};
$out = "<?php\n\nreturn " . var_export($blank($src), true) . ";\n";
foreach (["en","uz","kr"] as $loc) { file_put_contents("lang/$loc/admin.php", $out); }
echo "ok\n";
'
```

Expected output: `ok`.

- [ ] **Step 2: Format with Pint**

Run: `vendor/bin/pint --dirty --format agent`
Expected: `passed` or `fixed`.

- [ ] **Step 3: Verify the structure matches**

Run: `php -r 'foreach (["en","uz","kr"] as $l) { echo $l.": "; print_r(array_keys(require "lang/$l/admin.php")); }'`
Expected: each prints the same top-level keys (nav, common, auth, dashboard, news, categories, tags, validation).

---

## Task 1.6: Translate `admin.blade.php` (layout)

**Files:**
- Modify: `resources/views/components/layouts/admin.blade.php`

- [ ] **Step 1: Replace each English string with `__('admin.…')`**

Apply these exact replacements (everything else in the file stays the same):

| Old | New |
|---|---|
| `<title>{{ $title ?? 'Admin' }} — CERR Admin</title>` | `<title>{{ $title ?? __('admin.dashboard.title') }} — CERR Admin</title>` |
| `<span>CERR Admin</span>` | unchanged (brand stays in English) |
| `<div class="nav-label">Overview</div>` | `<div class="nav-label">{{ __('admin.nav.overview') }}</div>` |
| `<i class="fa-solid fa-gauge-high"></i> Dashboard` | `<i class="fa-solid fa-gauge-high"></i> {{ __('admin.nav.dashboard') }}` |
| `<div class="nav-label">Content</div>` | `<div class="nav-label">{{ __('admin.nav.content') }}</div>` |
| `<i class="fa-solid fa-newspaper"></i> News` | `<i class="fa-solid fa-newspaper"></i> {{ __('admin.nav.news') }}` |
| `<i class="fa-solid fa-folder-open"></i> Categories` | `<i class="fa-solid fa-folder-open"></i> {{ __('admin.nav.categories') }}` |
| `<i class="fa-solid fa-tags"></i> Tags` | `<i class="fa-solid fa-tags"></i> {{ __('admin.nav.tags') }}` |
| `aria-label="Toggle navigation"` | unchanged (a11y English is fine) |
| `title="View site"` | `title="{{ __('admin.nav.view_site') }}"` |
| `title="Sign out"` | `title="{{ __('admin.nav.sign_out') }}"` |

Use the `Edit` tool with `replace_all=false` for each pair. The replacements are unique enough not to collide.

- [ ] **Step 2: Verify rendering**

Run: `php artisan view:clear`
Run: `php artisan test tests/Feature/Admin/AdminLocaleMiddlewareTest.php`
Expected: 2 tests pass.

---

## Task 1.7: Translate `auth.blade.php` (auth layout) and `auth/login.blade.php`

**Files:**
- Modify: `resources/views/components/layouts/auth.blade.php`
- Modify: `resources/views/livewire/auth/login.blade.php`

- [ ] **Step 1: Update auth layout title default**

In `resources/views/components/layouts/auth.blade.php`:

Replace `<title>{{ $title ?? 'Sign in' }} — CERR</title>`
with `<title>{{ $title ?? __('admin.auth.sign_in') }} — CERR</title>`.

The `<div class="auth-brand">CERR Admin</div>` stays as-is (brand text).

- [ ] **Step 2: Update login.blade.php**

In `resources/views/livewire/auth/login.blade.php`:

Replace these strings:

| Old | New |
|---|---|
| `#[Title('Sign in')]` | `#[Title('Вход')]` |
| `'These credentials do not match our records.'` | `__('admin.auth.invalid_credentials')` |
| `"Too many attempts. Try again in {$seconds} seconds."` | `__('admin.auth.too_many_attempts', ['seconds' => $seconds])` |
| `<h1>Sign in</h1>` | `<h1>{{ __('admin.auth.sign_in') }}</h1>` |
| `<label class="form-label">Email</label>` | `<label class="form-label">{{ __('admin.auth.email') }}</label>` |
| `<label class="form-label">Password</label>` | `<label class="form-label">{{ __('admin.auth.password') }}</label>` |
| `<label for="remember" class="form-check-label">Remember me</label>` | `<label for="remember" class="form-check-label">{{ __('admin.auth.remember_me') }}</label>` |
| `<span wire:loading.remove wire:target="login">Sign in</span>` | `<span wire:loading.remove wire:target="login">{{ __('admin.auth.sign_in_button') }}</span>` |
| `<span wire:loading wire:target="login">Signing in…</span>` | `<span wire:loading wire:target="login">{{ __('admin.auth.signing_in') }}</span>` |

- [ ] **Step 3: Run login tests**

Run: `php artisan test tests/Feature/Admin/LoginFlowTest.php`
Expected: 4 tests pass. The "Sign in" text assertion in the render test still passes because we kept the H1 → `__('admin.auth.sign_in')` which resolves to "Вход в админ-панель"; the existing test asserts `Sign in` literal → **this test will break**.

- [ ] **Step 4: Update the login page render test**

In `tests/Feature/Admin/LoginFlowTest.php`:

Replace `->assertSee('Sign in')` with `->assertSee(__('admin.auth.sign_in'))`.

- [ ] **Step 5: Re-run login tests**

Run: `php artisan test tests/Feature/Admin/LoginFlowTest.php`
Expected: 4 tests pass.

---

## Task 1.8: Translate `dashboard.blade.php`

**Files:**
- Modify: `resources/views/livewire/admin/dashboard.blade.php`

- [ ] **Step 1: Replace strings**

Apply these replacements:

| Old | New |
|---|---|
| `<h1>Dashboard</h1>` | `<h1>{{ __('admin.dashboard.title') }}</h1>` |
| `<div class="subtitle">Welcome back, {{ explode(' ', auth()->user()->name)[0] }}.</div>` | `<div class="subtitle">{{ __('admin.dashboard.welcome', ['name' => explode(' ', auth()->user()->name)[0]]) }}</div>` |
| `<i class="fa-solid fa-newspaper me-1"></i> Total news` | `<i class="fa-solid fa-newspaper me-1"></i> {{ __('admin.dashboard.total_news') }}` |
| `<i class="fa-solid fa-circle-check me-1"></i> Published` | `<i class="fa-solid fa-circle-check me-1"></i> {{ __('admin.dashboard.published') }}` |
| `<i class="fa-regular fa-pen-to-square me-1"></i> Drafts` | `<i class="fa-regular fa-pen-to-square me-1"></i> {{ __('admin.dashboard.drafts') }}` |
| `<i class="fa-solid fa-folder-open me-1"></i> Categories / Tags` | `<i class="fa-solid fa-folder-open me-1"></i> {{ __('admin.dashboard.categories_tags') }}` |
| `<span>Recent news</span>` | `<span>{{ __('admin.dashboard.recent_news') }}</span>` |
| `All news <i class="fa-solid fa-arrow-right ms-1"></i>` | `{{ __('admin.dashboard.all_news') }} <i class="fa-solid fa-arrow-right ms-1"></i>` |
| `<th>ID</th>` | `<th>ID</th>` (unchanged — "ID" is a universal abbreviation) |
| `<th>Title</th>` | `<th>{{ __('admin.news.title') }}</th>` |
| `<th style="width: 130px;">Status</th>` | `<th style="width: 130px;">{{ __('admin.common.status') }}</th>` |
| `<th style="width: 160px;">Languages</th>` | `<th style="width: 160px;">{{ __('admin.common.languages') }}</th>` |
| `<th style="width: 130px;">Updated</th>` | `<th style="width: 130px;">{{ __('admin.common.updated') }}</th>` |
| `<a href="{{ route('admin.news.edit', $item) }}" class="btn btn-sm btn-outline-primary">Edit</a>` | `<a href="{{ route('admin.news.edit', $item) }}" class="btn btn-sm btn-outline-primary">{{ __('admin.common.edit') }}</a>` |
| `<div class="fw-semibold">No news yet.</div>` | `<div class="fw-semibold">{{ __('admin.dashboard.no_news') }}</div>` |
| `<a href="{{ route('admin.news.create') }}">Create your first article</a>.` | `<a href="{{ route('admin.news.create') }}">{{ __('admin.dashboard.create_first') }}</a>.` |

- [ ] **Step 2: Clear cache and run tests**

Run: `php artisan view:clear && php artisan test tests/Feature/Admin/AdminAccessTest.php`
Expected: 3 tests pass.

---

## Task 1.9: Translate `news/index.blade.php`

**Files:**
- Modify: `resources/views/livewire/admin/news/index.blade.php`

- [ ] **Step 1: Replace strings**

| Old | New |
|---|---|
| `<h1>News</h1>` | `<h1>{{ __('admin.news.title_section') }}</h1>` |
| `<div class="subtitle">Manage articles, translations, and publishing status</div>` | `<div class="subtitle">{{ __('admin.news.subtitle') }}</div>` |
| `<i class="fa-solid fa-plus me-1"></i> New article` | `<i class="fa-solid fa-plus me-1"></i> {{ __('admin.news.new_article') }}` |
| `placeholder="Search by title or slug…"` | `placeholder="{{ __('admin.news.search_placeholder') }}"` |
| `<option value="">All statuses</option>` | `<option value="">{{ __('admin.news.all_statuses') }}</option>` |
| `<option value="draft">Draft</option>` | `<option value="draft">{{ __('admin.news.status_draft') }}</option>` (use just `Черновик` here — the long form is for the form select; add `'status_short_draft' => 'Черновик'` keys if length matters; for now reuse) |
| `<option value="published">Published</option>` | `<option value="published">Опубликовано</option>` (inline RU acceptable, OR add a short key — see Step 2 below) |
| `<option value="auto_publish">Auto publish</option>` | `<option value="auto_publish">Автопубликация</option>` |
| `<option value="disabled">Disabled</option>` | `<option value="disabled">Отключено</option>` |
| `<option value="">All categories</option>` | `<option value="">{{ __('admin.news.all_categories') }}</option>` |
| `<th>Title</th>` | `<th>{{ __('admin.news.title') }}</th>` |
| `<th style="width: 130px;">Status</th>` | `<th style="width: 130px;">{{ __('admin.common.status') }}</th>` |
| `<th style="width: 160px;">Languages</th>` | `<th style="width: 160px;">{{ __('admin.common.languages') }}</th>` |
| `<th style="width: 160px;">Category</th>` | `<th style="width: 160px;">{{ __('admin.news.category_label') }}</th>` |
| `<th style="width: 90px;">Views</th>` | `<th style="width: 90px;">{{ __('admin.news.views') }}</th>` |
| `<th style="width: 130px;">Updated</th>` | `<th style="width: 130px;">{{ __('admin.common.updated') }}</th>` |
| `title="View"` | `title="{{ __('admin.common.view') }}"` |
| `title="Edit"` | `title="{{ __('admin.common.edit') }}"` |
| `title="Delete"` | `title="{{ __('admin.common.delete') }}"` |
| `wire:confirm="Delete this news item and all its translations?"` | `wire:confirm="{{ __('admin.news.confirm_delete') }}"` |
| `<div class="fw-semibold">No news matches the current filters.</div>` | `<div class="fw-semibold">{{ __('admin.news.no_news_match') }}</div>` |
| `<div class="small mt-1">Try clearing search and filter criteria, or <a href="{{ route('admin.news.create') }}">create a new article</a>.</div>` | `<div class="small mt-1">{{ __('admin.news.try_clear_filters') }} <a href="{{ route('admin.news.create') }}">{{ __('admin.news.create_new_article') }}</a>.</div>` |
| `Showing {{ $newsList->firstItem() }}–{{ $newsList->lastItem() }} of {{ $newsList->total() }}` | `{{ __('admin.common.showing_range', ['from' => $newsList->firstItem(), 'to' => $newsList->lastItem(), 'total' => $newsList->total()]) }}` |

- [ ] **Step 2: Add the four short-status keys to `lang/ru/admin.php` under `news`**

Open `lang/ru/admin.php` and add inside the `news` section:

```php
'status_short_draft' => 'Черновик',
'status_short_published' => 'Опубликовано',
'status_short_auto_publish' => 'Автопубликация',
'status_short_disabled' => 'Отключено',
```

Then in the index view, replace the four hardcoded RU strings introduced above with `{{ __('admin.news.status_short_draft') }}` etc.

- [ ] **Step 3: Update stubs**

Re-run the stub-generator from Task 1.5 to keep `lang/{en,uz,kr}/admin.php` in sync:

```bash
php -r '
$src = require "lang/ru/admin.php";
$blank = function ($a) use (&$blank) {
    foreach ($a as $k => $v) { $a[$k] = is_array($v) ? $blank($v) : ""; }
    return $a;
};
$out = "<?php\n\nreturn " . var_export($blank($src), true) . ";\n";
foreach (["en","uz","kr"] as $loc) { file_put_contents("lang/$loc/admin.php", $out); }
echo "ok\n";
'
```

- [ ] **Step 4: Run tests**

Run: `php artisan view:clear && php artisan test tests/Feature/Admin/NewsCrudTest.php`
Expected: 7 tests pass.

---

## Task 1.10: Translate `news/form.blade.php`

**Files:**
- Modify: `resources/views/livewire/admin/news/form.blade.php`

- [ ] **Step 1: Replace strings**

This file is the largest — apply these replacements:

| Old | New |
|---|---|
| `{{ $news?->exists ? 'Edit article' : 'New article' }}` | `{{ $news?->exists ? __('admin.news.edit_article') : __('admin.news.new_article') }}` |
| `<div class="subtitle">` block with `#{{ $news->id }} · last saved …` | replace with `__('admin.news.last_saved', ['time' => $news->updated_at?->diffForHumans() ?? '—'])` |
| `Create a new multilingual news article` | `{{ __('admin.news.create_article') }}` |
| `<i class="fa-solid fa-arrow-left me-1"></i> Back to list` | `<i class="fa-solid fa-arrow-left me-1"></i> {{ __('admin.news.back_to_list') }}` |
| `<label class="form-label">Title @if (...)` | `<label class="form-label">{{ __('admin.news.title') }} @if (...)` |
| `placeholder="Article headline in {{ strtoupper($locale) }}"` | `placeholder="{{ __('admin.news.title_placeholder', ['locale' => strtoupper($locale)]) }}"` |
| `<label class="form-label">Short description @if (...)` | `<label class="form-label">{{ __('admin.news.short_description') }} @if (...)` |
| `placeholder="One- or two-sentence summary"` | `placeholder="{{ __('admin.news.short_description_placeholder') }}"` |
| `<label class="form-label">Content @if (...)` | `<label class="form-label">{{ __('admin.news.content') }} @if (...)` |
| `<i class="fa-solid fa-magnifying-glass me-1"></i> SEO ({{ strtoupper($locale) }})` | `<i class="fa-solid fa-magnifying-glass me-1"></i> {{ __('admin.news.seo', ['locale' => strtoupper($locale)]) }}` |
| `<label class="form-label">SEO title</label>` | `<label class="form-label">{{ __('admin.news.seo_title') }}</label>` |
| `placeholder="Falls back to title"` | `placeholder="{{ __('admin.news.seo_title_placeholder') }}"` |
| `<label class="form-label">SEO description</label>` | `<label class="form-label">{{ __('admin.news.seo_description') }}</label>` |
| `placeholder="160 characters max"` | `placeholder="{{ __('admin.news.seo_description_placeholder') }}"` |
| `<span>Cover image ({{ strtoupper($locale) }})</span>` | `<span>{{ __('admin.news.cover_image', ['locale' => strtoupper($locale)]) }}</span>` |
| `<span class="text-muted small fw-normal">JPG, PNG, WebP — up to 5 MB</span>` | `<span class="text-muted small fw-normal">{{ __('admin.news.cover_specs') }}</span>` |
| `<i class="fa-solid fa-trash me-1"></i> Remove` | `<i class="fa-solid fa-trash me-1"></i> {{ __('admin.common.remove') }}` |
| `wire:confirm="Remove the current cover image?"` | `wire:confirm="{{ __('admin.news.confirm_remove_cover') }}"` |
| `<i class="fa-solid fa-spinner fa-spin me-1"></i> Uploading…` | `<i class="fa-solid fa-spinner fa-spin me-1"></i> {{ __('admin.common.uploading') }}` |
| `<div class="card-header">Publishing</div>` | `<div class="card-header">{{ __('admin.news.publishing') }}</div>` |
| `<label class="form-label">Slug <span class="text-danger">*</span></label>` | `<label class="form-label">Slug <span class="text-danger">*</span></label>` (slug stays as a technical word) |
| `placeholder="my-article-url"` | `placeholder="my-article-url"` (technical) |
| `<div class="form-text small">Used in the article URL.</div>` | `<div class="form-text small">{{ __('admin.news.slug_help') }}</div>` |
| `<label class="form-label">Status</label>` | `<label class="form-label">{{ __('admin.common.status') }}</label>` |
| `<option value="draft">Draft — only visible to admins</option>` | `<option value="draft">{{ __('admin.news.status_draft') }}</option>` |
| `<option value="published">Published — live now</option>` | `<option value="published">{{ __('admin.news.status_published') }}</option>` |
| `<option value="auto_publish">Auto publish — at scheduled time</option>` | `<option value="auto_publish">{{ __('admin.news.status_auto_publish') }}</option>` |
| `<option value="disabled">Disabled — hidden from site</option>` | `<option value="disabled">{{ __('admin.news.status_disabled') }}</option>` |
| `<label class="form-label">Scheduled at</label>` | `<label class="form-label">{{ __('admin.news.scheduled_at') }}</label>` |
| `<label for="is_main" class="form-check-label">Show on homepage hero</label>` | `<label for="is_main" class="form-check-label">{{ __('admin.news.show_on_homepage') }}</label>` |
| `<i class="fa-solid fa-floppy-disk me-1"></i> Save article` | `<i class="fa-solid fa-floppy-disk me-1"></i> {{ __('admin.news.save_article') }}` |
| `<i class="fa-solid fa-spinner fa-spin me-1"></i> Saving…` | `<i class="fa-solid fa-spinner fa-spin me-1"></i> {{ __('admin.common.saving') }}` |
| `<i class="fa-solid fa-arrow-up-right-from-square me-1"></i> View on site` | `<i class="fa-solid fa-arrow-up-right-from-square me-1"></i> {{ __('admin.news.view_on_site') }}` |
| `<div class="card-header">Category</div>` | `<div class="card-header">{{ __('admin.news.category') }}</div>` |
| `<option value="">— None —</option>` | `<option value="">{{ __('admin.common.none') }}</option>` |
| `<span>Tags</span>` | `<span>{{ __('admin.news.tags') }}</span>` |
| `<span class="text-muted small">{{ count($tag_ids) }} selected</span>` | `<span class="text-muted small">{{ __('admin.news.tags_selected', ['count' => count($tag_ids)]) }}</span>` |
| `<div class="small">No tags yet. <a href="{{ route('admin.tags.index') }}">Create one</a>.</div>` | `<div class="small">{{ __('admin.news.no_tags_yet') }} <a href="{{ route('admin.tags.index') }}">{{ __('admin.news.create_one') }}</a>.</div>` |

- [ ] **Step 2: Run tests**

Run: `php artisan view:clear && php artisan test tests/Feature/Admin/NewsCrudTest.php`
Expected: 7 tests pass.

---

## Task 1.11: Translate `categories/index.blade.php`

**Files:**
- Modify: `resources/views/livewire/admin/categories/index.blade.php`

- [ ] **Step 1: Replace strings**

| Old | New |
|---|---|
| `<h1>Categories</h1>` | `<h1>{{ __('admin.categories.title_section') }}</h1>` |
| `<div class="subtitle">Group news articles. Each category supports 4 language names.</div>` | `<div class="subtitle">{{ __('admin.categories.subtitle') }}</div>` |
| `<i class="fa-solid fa-plus me-1"></i> New category` | `<i class="fa-solid fa-plus me-1"></i> {{ __('admin.categories.new_category') }}` |
| `<div class="card-header">{{ $editingId ? 'Edit category' : 'Create category' }}</div>` | `<div class="card-header">{{ $editingId ? __('admin.categories.edit_category') : __('admin.categories.create_category') }}</div>` |
| `<label class="form-label">Slug <span class="text-danger">*</span></label>` | unchanged |
| `<div class="form-text small">Used in the category URL.</div>` | `<div class="form-text small">{{ __('admin.categories.slug_help') }}</div>` |
| `<label for="cat-status" class="form-check-label">Active</label>` | `<label for="cat-status" class="form-check-label">{{ __('admin.common.active') }}</label>` |
| `<span class="lang-chip">{{ $locale }}</span> Name` | `<span class="lang-chip">{{ $locale }}</span> {{ __('admin.categories.name_label') }}` |
| `<button type="button" class="btn btn-outline-secondary" wire:click="cancel">Cancel</button>` | `<button type="button" class="btn btn-outline-secondary" wire:click="cancel">{{ __('admin.common.cancel') }}</button>` |
| `<i class="fa-solid fa-floppy-disk me-1"></i> Save` | `<i class="fa-solid fa-floppy-disk me-1"></i> {{ __('admin.common.save') }}` |
| `<th style="width: 100px;">Status</th>` | `<th style="width: 100px;">{{ __('admin.common.status') }}</th>` |
| `<span class="pill status-published">Active</span>` | `<span class="pill status-published">{{ __('admin.common.active') }}</span>` |
| `<span class="pill status-draft">Inactive</span>` | `<span class="pill status-draft">{{ __('admin.common.inactive') }}</span>` |
| `wire:confirm="Delete this category? News in it will be uncategorized."` | `wire:confirm="{{ __('admin.categories.confirm_delete') }}"` |
| `<div class="fw-semibold">No categories yet.</div>` | `<div class="fw-semibold">{{ __('admin.categories.no_categories') }}</div>` |
| `<div class="small mt-1">Create one to start grouping articles.</div>` | `<div class="small mt-1">{{ __('admin.categories.no_categories_help') }}</div>` |

- [ ] **Step 2: Run tests**

Run: `php artisan view:clear && php artisan test tests/Feature/Admin/CategoryCrudTest.php`
Expected: 3 tests pass.

---

## Task 1.12: Translate `tags/index.blade.php`

**Files:**
- Modify: `resources/views/livewire/admin/tags/index.blade.php`

- [ ] **Step 1: Replace strings**

| Old | New |
|---|---|
| `<h1>Tags</h1>` | `<h1>{{ __('admin.tags.title_section') }}</h1>` |
| `<div class="subtitle">Free-form labels you can attach to news articles.</div>` | `<div class="subtitle">{{ __('admin.tags.subtitle') }}</div>` |
| `<i class="fa-solid fa-plus me-1"></i> New tag` | `<i class="fa-solid fa-plus me-1"></i> {{ __('admin.tags.new_tag') }}` |
| `<div class="card-header">{{ $editingId ? 'Edit tag' : 'Create tag' }}</div>` | `<div class="card-header">{{ $editingId ? __('admin.tags.edit_tag') : __('admin.tags.create_tag') }}</div>` |
| `<label class="form-label">Name <span class="text-danger">*</span></label>` | `<label class="form-label">{{ __('admin.tags.name_label') }} <span class="text-danger">*</span></label>` |
| `<button type="button" class="btn btn-outline-secondary" wire:click="cancel">Cancel</button>` | `<button type="button" class="btn btn-outline-secondary" wire:click="cancel">{{ __('admin.common.cancel') }}</button>` |
| `<i class="fa-solid fa-floppy-disk me-1"></i> Save` | `<i class="fa-solid fa-floppy-disk me-1"></i> {{ __('admin.common.save') }}` |
| `<th>Name</th>` | `<th>{{ __('admin.tags.name_label') }}</th>` |
| `<th style="width: 120px;">Articles</th>` | `<th style="width: 120px;">{{ __('admin.tags.articles_count') }}</th>` |
| `wire:confirm="Delete this tag?"` | `wire:confirm="{{ __('admin.tags.confirm_delete') }}"` |
| `<div class="fw-semibold">No tags yet.</div>` | `<div class="fw-semibold">{{ __('admin.tags.no_tags') }}</div>` |
| `<div class="small mt-1">Tags help readers discover related articles.</div>` | `<div class="small mt-1">{{ __('admin.tags.no_tags_help') }}</div>` |

- [ ] **Step 2: Run tests**

Run: `php artisan view:clear && php artisan test tests/Feature/Admin/TagCrudTest.php`
Expected: 3 tests pass.

---

## Task 1.13: Translate Livewire component flash messages

**Files:**
- Modify: `app/Livewire/Admin/News/NewsIndex.php`
- Modify: `app/Livewire/Admin/News/NewsForm.php`
- Modify: `app/Livewire/Admin/Categories/CategoryIndex.php`
- Modify: `app/Livewire/Admin/Tags/TagIndex.php`

- [ ] **Step 1: Replace flash strings**

| File | Old | New |
|---|---|---|
| NewsIndex.php | `session()->flash('status', 'News deleted.');` | `session()->flash('status', __('admin.news.deleted_flash'));` |
| NewsForm.php | `session()->flash('status', 'News saved.');` | `session()->flash('status', __('admin.news.saved_flash'));` |
| CategoryIndex.php | `session()->flash('status', 'Category saved.');` | `session()->flash('status', __('admin.categories.saved_flash'));` |
| CategoryIndex.php | `session()->flash('status', 'Category deleted.');` | `session()->flash('status', __('admin.categories.deleted_flash'));` |
| TagIndex.php | `session()->flash('status', 'Tag saved.');` | `session()->flash('status', __('admin.tags.saved_flash'));` |
| TagIndex.php | `session()->flash('status', 'Tag deleted.');` | `session()->flash('status', __('admin.tags.deleted_flash'));` |

- [ ] **Step 2: Run all admin tests**

Run: `php artisan test tests/Feature/Admin/`
Expected: all admin tests pass (around 20 tests).

---

## Task 1.14: Final grep audit + Pint + full test suite

- [ ] **Step 1: Grep for likely-English strings in admin views**

Run:
```bash
grep -rn -E '">\s*[A-Z][a-z]+( [A-Z]?[a-z]+)+\s*<' resources/views/livewire/admin resources/views/components/layouts/admin.blade.php resources/views/livewire/auth resources/views/components/layouts/auth.blade.php
```

Manually inspect any hits. Expected hits to LEAVE in English: brand text "CERR Admin", URL placeholders, technical labels like "Slug" or "URL". Replace anything user-facing that you missed.

- [ ] **Step 2: Format and run full suite**

Run: `vendor/bin/pint --dirty --format agent`
Expected: `passed`.

Run: `php artisan test --compact`
Expected: 213+ tests pass (211 existing + 2 new locale middleware).

- [ ] **Step 3: Commit Phase 1**

```bash
git add app/Http/Middleware/SetAdminLocale.php bootstrap/app.php lang/ resources/views/components/layouts/admin.blade.php resources/views/components/layouts/auth.blade.php resources/views/livewire/auth/login.blade.php resources/views/livewire/admin/ app/Livewire/Admin/ tests/Feature/Admin/AdminLocaleMiddlewareTest.php tests/Feature/Admin/LoginFlowTest.php

git commit -m "$(cat <<'EOF'
feat(admin): translate admin panel to Russian (Phase 1)

- Add SetAdminLocale middleware forcing app locale to ru on /admin/*
- Add lang/ru/admin.php with full key inventory; empty stubs for en/uz/kr
- Replace hardcoded strings across all admin views, auth layout, login
- Translate flash messages in Livewire components

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

# Phase 2 — Quick wins

**Objective:** auto-slug from KR title on the create form, styled confirmation modal replacing browser `confirm()`, drag-and-drop on cover image inputs.

---

## Task 2.1: Create the confirm modal component

**Files:**
- Create: `resources/views/components/admin/confirm-modal.blade.php`

- [ ] **Step 1: Write the component**

Create `resources/views/components/admin/confirm-modal.blade.php`:

```blade
<div
    x-data="{
        open: false,
        message: '',
        confirmLabel: '{{ __('admin.common.delete') }}',
        cancelLabel: '{{ __('admin.common.cancel') }}',
        callback: null,
        show(detail) {
            this.message = detail.message ?? '{{ __('admin.common.confirm_delete') }}';
            this.confirmLabel = detail.confirmLabel ?? '{{ __('admin.common.delete') }}';
            this.cancelLabel = detail.cancelLabel ?? '{{ __('admin.common.cancel') }}';
            this.callback = detail.onConfirm ?? null;
            this.open = true;
        },
        confirm() {
            if (typeof this.callback === 'function') { this.callback(); }
            this.open = false; this.callback = null;
        },
    }"
    @open-confirm.window="show($event.detail)"
    @keydown.escape.window="open = false"
    x-show="open"
    x-cloak
    style="position: fixed; inset: 0; z-index: 1080; background: rgba(15, 23, 42, .5); display: flex; align-items: center; justify-content: center;"
>
    <div @click.outside="open = false" style="background: #fff; border-radius: 12px; padding: 1.5rem; max-width: 420px; width: calc(100% - 2rem); box-shadow: 0 20px 40px rgba(15,23,42,.2);">
        <div class="d-flex align-items-start gap-3 mb-3">
            <div style="width: 40px; height: 40px; flex-shrink: 0; border-radius: 50%; background: #fee2e2; display: flex; align-items: center; justify-content: center; color: #b91c1c;">
                <i class="fa-solid fa-triangle-exclamation"></i>
            </div>
            <div class="flex-grow-1">
                <h5 class="mb-1" style="font-size: 1.05rem;">{{ __('admin.common.confirm_delete') }}</h5>
                <p class="text-muted small mb-0" x-text="message"></p>
            </div>
        </div>
        <div class="d-flex justify-content-end gap-2">
            <button type="button" class="btn btn-outline-secondary" @click="open = false" x-text="cancelLabel"></button>
            <button type="button" class="btn btn-danger" @click="confirm" x-text="confirmLabel"></button>
        </div>
    </div>
</div>
```

---

## Task 2.2: Mount the confirm modal in the admin layout

**Files:**
- Modify: `resources/views/components/layouts/admin.blade.php`

- [ ] **Step 1: Add the component reference**

Find the line ` </main>` near the end of the admin layout (closing the main content area).

After ` </main>` and before `</div>` (closing `.admin-main`), add:

```blade
            </main>
        </div>
    </div>

    <x-admin.confirm-modal />
```

(The `<x-admin.confirm-modal />` line goes after the closing `</div>` of `.admin-shell`, before the script tags.)

- [ ] **Step 2: View clear**

Run: `php artisan view:clear`

---

## Task 2.3: Replace `wire:confirm` with the new event-driven pattern

**Files:**
- Modify: `resources/views/livewire/admin/news/index.blade.php`
- Modify: `resources/views/livewire/admin/news/form.blade.php`
- Modify: `resources/views/livewire/admin/categories/index.blade.php`
- Modify: `resources/views/livewire/admin/tags/index.blade.php`

- [ ] **Step 1: Update each delete button**

Pattern transformation for every `<button>` with `wire:click="...delete..." wire:confirm="...">`:

Replace:
```blade
<button ... wire:click="delete({{ $item->id }})" wire:confirm="{{ __('admin.news.confirm_delete') }}">
```

With:
```blade
<button ... type="button" x-data
        @click="$dispatch('open-confirm', { message: @js(__('admin.news.confirm_delete')), onConfirm: () => $wire.delete({{ $item->id }}) })">
```

Apply this transformation to:
- News index delete button (Task 1.9 file): use `admin.news.confirm_delete`
- News form "Remove cover" button: use `admin.news.confirm_remove_cover`, callback `() => $wire.clearCover('{{ $locale }}')`
- Categories index delete: `admin.categories.confirm_delete`, callback `() => $wire.delete({{ $cat->id }})`
- Tags index delete: `admin.tags.confirm_delete`, callback `() => $wire.delete({{ $tag->id }})`

Remove the `wire:confirm="..."` attribute on each.

- [ ] **Step 2: Run admin tests**

Run: `php artisan test tests/Feature/Admin/`
Expected: all pass.

---

## Task 2.4: Test the confirm modal renders and dispatches

**Files:**
- Create: `tests/Feature/Admin/ConfirmModalComponentTest.php`

- [ ] **Step 1: Write the test**

```php
<?php

use App\Models\User;

describe('Admin confirm modal', function () {
    it('mounts on every admin page', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('@open-confirm.window', false);
    })->group('feature', 'admin');

    it('replaces wire:confirm on news delete buttons', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        \App\Models\News::factory()->create()->translations()->create([
            'lang' => 'kr',
            'title' => 't',
            'short_description' => 's',
            'content' => '<p>c</p>',
            'image_url' => '',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.news.index'));

        $response->assertOk()
            ->assertSee('open-confirm', false)
            ->assertDontSee('wire:confirm', false);
    })->group('feature', 'admin');
});
```

- [ ] **Step 2: Run the test**

Run: `php artisan test tests/Feature/Admin/ConfirmModalComponentTest.php`
Expected: 2 tests pass.

---

## Task 2.5: Add auto-slug to NewsForm

**Files:**
- Modify: `resources/views/livewire/admin/news/form.blade.php`

- [ ] **Step 1: Wrap the slug input with Alpine state**

Find the slug input block in the Publishing card:

```blade
<div class="mb-3">
    <label class="form-label">Slug <span class="text-danger">*</span></label>
    <input type="text" wire:model="slug" class="form-control @error('slug') is-invalid @enderror" placeholder="my-article-url">
    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
    <div class="form-text small">{{ __('admin.news.slug_help') }}</div>
</div>
```

Replace with:

```blade
<div class="mb-3"
     x-data="{ manuallyEdited: @js((bool) ($news?->exists)) }"
     x-init="
        $watch(() => $wire.translations.kr.title, (title) => {
            if (manuallyEdited) return;
            if (typeof title !== 'string') return;
            const slug = title.toLowerCase()
                .replace(/[^\wЀ-ӿ\s-]/g, '')
                .trim()
                .replace(/[\s_]+/g, '-')
                .replace(/^-+|-+$/g, '');
            $wire.set('slug', slug, false);
        });
     ">
    <label class="form-label">Slug <span class="text-danger">*</span></label>
    <input type="text" wire:model="slug" @input="manuallyEdited = true"
           class="form-control @error('slug') is-invalid @enderror" placeholder="my-article-url">
    @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
    <div class="form-text small">{{ __('admin.news.slug_help') }}</div>
</div>
```

Notes:
- `manuallyEdited` starts `true` on edit (so we never overwrite an existing slug), `false` on create.
- Once the user types in the slug field, `manuallyEdited` flips to `true` and stays.
- The slugify regex preserves Cyrillic ranges (Russian-language slugs would need `Str::slug` server-side for proper transliteration, but since the slug is on the KR/uzbek-cyrillic title, basic ASCII-fallback is fine for now). For better quality we could add a server-side `slugify` Livewire method later.

---

## Task 2.6: Test auto-slug behavior

**Files:**
- Create: `tests/Feature/Admin/AutoSlugTest.php`

- [ ] **Step 1: Write the test**

The auto-slug logic is client-side; we test it by asserting the markup contains the right Alpine state hooks (full E2E would need a browser test).

```php
<?php

use App\Models\News;
use App\Models\User;

describe('Auto-slug behavior', function () {
    it('creates slug attribute is wired with auto-fill on the create form', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.news.create'));

        $response->assertOk()
            ->assertSee('manuallyEdited: false', false)
            ->assertSee('$watch(() => $wire.translations.kr.title', false);
    })->group('feature', 'admin');

    it('disables auto-fill on the edit form', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $news = News::factory()->create(['slug' => 'existing-slug']);

        $response = $this->actingAs($admin)->get(route('admin.news.edit', $news));

        $response->assertOk()
            ->assertSee('manuallyEdited: true', false);
    })->group('feature', 'admin');
});
```

- [ ] **Step 2: Run the test**

Run: `php artisan test tests/Feature/Admin/AutoSlugTest.php`
Expected: 2 tests pass.

---

## Task 2.7: Add drag-and-drop wrapper for cover image inputs

**Files:**
- Modify: `resources/views/livewire/admin/news/form.blade.php`

- [ ] **Step 1: Wrap the file input**

Find the existing cover image input:

```blade
<input type="file" wire:model="cover_uploads.{{ $locale }}" accept="image/*" class="form-control @error('cover_uploads.'.$locale) is-invalid @enderror">
```

Replace with:

```blade
<div x-data="{ dragging: false }"
     class="position-relative"
     :class="{ 'is-dragging': dragging }"
     @dragover.prevent="dragging = true"
     @dragleave.prevent="dragging = false"
     @drop.prevent="
        dragging = false;
        if ($event.dataTransfer.files.length) {
            const input = $el.querySelector('input[type=file]');
            input.files = $event.dataTransfer.files;
            input.dispatchEvent(new Event('change'));
        }
     "
     style="border: 2px dashed transparent; border-radius: 8px; padding: 4px; transition: all .15s;">
    <div x-show="dragging" x-cloak style="position: absolute; inset: 0; background: rgba(37,99,235,.08); border: 2px dashed #2563eb; border-radius: 8px; display: flex; align-items: center; justify-content: center; pointer-events: none; font-weight: 600; color: #2563eb;">
        <i class="fa-solid fa-cloud-arrow-up me-2"></i> Drop image here
    </div>
    <input type="file" wire:model="cover_uploads.{{ $locale }}" accept="image/*"
           class="form-control @error('cover_uploads.'.$locale) is-invalid @enderror">
</div>
```

Add the "Drop image here" string to `lang/ru/admin.php` under `news`:

```php
'drop_here' => 'Перетащите изображение сюда',
```

Then in the markup replace `Drop image here` with `{{ __('admin.news.drop_here') }}`.

Re-run the stub generator from Task 1.5 to keep stubs aligned.

---

## Task 2.8: Phase 2 final test pass + commit

- [ ] **Step 1: Pint + tests**

Run: `vendor/bin/pint --dirty --format agent`
Run: `php artisan test --compact`
Expected: all tests pass (213+ from Phase 1, plus 4 new tests = 217+).

- [ ] **Step 2: Commit**

```bash
git add resources/views/components/admin/confirm-modal.blade.php resources/views/components/layouts/admin.blade.php resources/views/livewire/admin/ tests/Feature/Admin/ConfirmModalComponentTest.php tests/Feature/Admin/AutoSlugTest.php lang/

git commit -m "$(cat <<'EOF'
feat(admin): styled confirm modal, auto-slug, drag-drop uploads (Phase 2)

- Replace browser confirm() with shared <x-admin.confirm-modal/>
- Auto-slug fills from KR title on create form (Alpine-driven)
- Drag-and-drop wrapper around cover image inputs

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

# Phase 3 — Missing CRUD (Users, Pages, Videos)

**Objective:** Admin can fully manage users (with self-protection), pages (multi-language), and videos. `last_login_at` tracked on users.

---

## Task 3.1: Migration — add `last_login_at` to users

**Files:**
- Create: `database/migrations/{timestamp}_add_last_login_at_to_users_table.php`

- [ ] **Step 1: Generate migration**

Run: `php artisan make:migration add_last_login_at_to_users_table --table=users --no-interaction`
Output: prints the new file path.

- [ ] **Step 2: Fill in the migration**

Replace the file's `up`/`down` with:

```php
public function up(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->timestamp('last_login_at')->nullable()->after('remember_token');
    });
}

public function down(): void
{
    Schema::table('users', function (Blueprint $table) {
        $table->dropColumn('last_login_at');
    });
}
```

- [ ] **Step 3: Run migration**

Run: `php artisan migrate`
Expected: migration runs successfully.

- [ ] **Step 4: Update User model**

In `app/Models/User.php`:

Add `'last_login_at'` to the `casts()` return array as `'datetime'`:

```php
protected function casts(): array
{
    return [
        'email_verified_at' => 'datetime',
        'last_login_at' => 'datetime',
        'password' => 'hashed',
    ];
}
```

Add `'last_login_at'` to `$fillable`.

---

## Task 3.2: Update login to record `last_login_at`

**Files:**
- Modify: `resources/views/livewire/auth/login.blade.php`

- [ ] **Step 1: Set last_login_at on successful auth**

In the `login()` method, after `RateLimiter::clear($throttleKey);`, add:

```php
$user = Auth::user();
$user->forceFill(['last_login_at' => now()])->save();
```

Update the existing `$user = Auth::user();` line later in the method to reuse the variable (don't fetch twice).

- [ ] **Step 2: Write test**

Create `tests/Feature/Admin/LastLoginUpdateTest.php`:

```php
<?php

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Volt\Volt;

describe('last_login_at tracking', function () {
    it('updates last_login_at on successful login', function () {
        User::factory()->create([
            'email' => 'lastlogin@test.com',
            'password' => Hash::make('secret-pass'),
            'last_login_at' => null,
        ]);

        $this->get('/login');

        Volt::test('auth.login')
            ->set('email', 'lastlogin@test.com')
            ->set('password', 'secret-pass')
            ->call('login');

        $user = User::where('email', 'lastlogin@test.com')->first();
        expect($user->last_login_at)->not->toBeNull();
    })->group('feature', 'admin');
});
```

- [ ] **Step 3: Run the test**

Run: `php artisan test tests/Feature/Admin/LastLoginUpdateTest.php`
Expected: 1 test passes.

---

## Task 3.3: Extract `HandlesImageUploads` trait

**Files:**
- Create: `app/Livewire/Concerns/HandlesImageUploads.php`
- Modify: `app/Livewire/Admin/News/NewsForm.php`

- [ ] **Step 1: Create the trait**

```php
<?php

namespace App\Livewire\Concerns;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesImageUploads
{
    /**
     * Store an uploaded image on the public disk in the given folder.
     * Returns the storage path (e.g. "news/covers/{uuid}.jpg").
     */
    protected function storeUploadedImage(UploadedFile $file, string $folder): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $filename = Str::uuid()->toString().'.'.$extension;

        return $file->storeAs($folder, $filename, 'public');
    }

    /**
     * Delete a previously stored image from the public disk if it is a
     * managed path under one of our storage subfolders.
     */
    protected function deleteStoredImage(?string $path): void
    {
        if (! $path) {
            return;
        }

        $managedPrefixes = ['news/', 'pages/', 'videos/'];
        foreach ($managedPrefixes as $prefix) {
            if (str_starts_with($path, $prefix)) {
                Storage::disk('public')->delete($path);

                return;
            }
        }
    }
}
```

- [ ] **Step 2: Refactor NewsForm to use the trait**

In `app/Livewire/Admin/News/NewsForm.php`:

Add `use App\Livewire\Concerns\HandlesImageUploads;` and `use HandlesImageUploads;` next to `use WithFileUploads;`.

Replace the existing inline upload + delete logic in `save()`:

```php
if ($upload) {
    $extension = strtolower($upload->getClientOriginalExtension() ?: $upload->extension());
    $filename = Str::uuid()->toString().'.'.$extension;
    $imagePath = $upload->storeAs('news/covers', $filename, 'public');

    if ($existing && $existing->image_url && str_starts_with($existing->image_url, 'news/') && $existing->image_url !== $imagePath) {
        Storage::disk('public')->delete($existing->image_url);
    }
}
```

With:

```php
if ($upload) {
    $imagePath = $this->storeUploadedImage($upload, 'news/covers');
    if ($existing && $existing->image_url !== $imagePath) {
        $this->deleteStoredImage($existing->image_url);
    }
}
```

Similarly, in `clearCover()`, replace:

```php
if ($existing && $existing->image_url && str_starts_with($existing->image_url, 'news/')) {
    Storage::disk('public')->delete($existing->image_url);
}
```

With:

```php
$this->deleteStoredImage($existing?->image_url);
```

- [ ] **Step 3: Run news tests**

Run: `php artisan test tests/Feature/Admin/NewsCrudTest.php`
Expected: 7 tests pass.

---

## Task 3.4: Build User CRUD

**Files:**
- Create: `app/Livewire/Admin/Users/UserIndex.php`
- Create: `resources/views/livewire/admin/users/index.blade.php`

- [ ] **Step 1: Add `users` keys to `lang/ru/admin.php`**

Add this section inside the `lang/ru/admin.php` array:

```php
'users' => [
    'title_section' => 'Пользователи',
    'subtitle' => 'Управление администраторами, редакторами и читателями.',
    'new_user' => 'Новый пользователь',
    'edit_user' => 'Редактирование пользователя',
    'create_user' => 'Создание пользователя',
    'name' => 'Имя',
    'email' => 'Email',
    'role' => 'Роль',
    'role_admin' => 'Администратор',
    'role_writer' => 'Автор',
    'role_editor' => 'Редактор',
    'role_viewer' => 'Читатель',
    'password' => 'Пароль',
    'password_help' => 'Оставьте пустым, чтобы не менять.',
    'last_login' => 'Последний вход',
    'never_logged_in' => 'Никогда',
    'reset_password' => 'Сбросить пароль',
    'reset_password_done' => 'Новый пароль: :password',
    'no_users' => 'Пользователей пока нет.',
    'confirm_delete' => 'Удалить этого пользователя? Действие нельзя отменить.',
    'cannot_delete_self' => 'Нельзя удалить свою учётную запись.',
    'cannot_demote_self' => 'Нельзя понизить свою учётную запись.',
    'saved_flash' => 'Пользователь сохранён.',
    'deleted_flash' => 'Пользователь удалён.',
],
```

Re-run stub generator (Task 1.5 step 1).

- [ ] **Step 2: Write the component**

Create `app/Livewire/Admin/Users/UserIndex.php`:

```php
<?php

namespace App\Livewire\Admin\Users;

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Пользователи')]
class UserIndex extends Component
{
    public ?int $editingId = null;

    public string $name = '';

    public string $email = '';

    public string $role = 'viewer';

    public string $password = '';

    public bool $showForm = false;

    public ?string $generatedPassword = null;

    public string $roleFilter = '';

    protected function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($this->editingId)],
            'role' => ['required', Rule::in(['admin', 'writer', 'editor', 'viewer'])],
            'password' => [$this->editingId ? 'nullable' : 'required', 'string', 'min:8'],
        ];
    }

    public function startCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $user = User::findOrFail($id);
        $this->editingId = $user->id;
        $this->name = $user->name;
        $this->email = $user->email;
        $this->role = $user->role;
        $this->password = '';
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingId === auth()->id() && $this->role !== 'admin') {
            $this->addError('role', __('admin.users.cannot_demote_self'));

            return;
        }

        $user = $this->editingId ? User::findOrFail($this->editingId) : new User;
        $user->name = $this->name;
        $user->email = $this->email;
        $user->role = $this->role;
        if ($this->password !== '') {
            $user->password = Hash::make($this->password);
        }
        $user->save();

        session()->flash('status', __('admin.users.saved_flash'));
        $this->resetForm();
        $this->showForm = false;
    }

    public function resetPassword(int $id): void
    {
        $user = User::findOrFail($id);
        $plain = Str::password(16);
        $user->password = Hash::make($plain);
        $user->save();

        $this->generatedPassword = $plain;
    }

    public function delete(int $id): void
    {
        if ($id === auth()->id()) {
            session()->flash('status', __('admin.users.cannot_delete_self'));

            return;
        }

        User::findOrFail($id)->delete();
        session()->flash('status', __('admin.users.deleted_flash'));
    }

    public function cancel(): void
    {
        $this->resetForm();
        $this->showForm = false;
    }

    private function resetForm(): void
    {
        $this->editingId = null;
        $this->name = '';
        $this->email = '';
        $this->role = 'viewer';
        $this->password = '';
        $this->resetValidation();
    }

    public function render()
    {
        $query = User::query()->orderBy('id');
        if ($this->roleFilter !== '') {
            $query->where('role', $this->roleFilter);
        }

        return view('livewire.admin.users.index', [
            'users' => $query->get(),
        ]);
    }
}
```

- [ ] **Step 3: Write the view**

Create `resources/views/livewire/admin/users/index.blade.php`:

```blade
<div>
    <div class="page-header">
        <div>
            <h1>{{ __('admin.users.title_section') }}</h1>
            <div class="subtitle">{{ __('admin.users.subtitle') }}</div>
        </div>
        @if (! $showForm)
            <button type="button" class="btn btn-primary" wire:click="startCreate">
                <i class="fa-solid fa-plus me-1"></i> {{ __('admin.users.new_user') }}
            </button>
        @endif
    </div>

    @if ($generatedPassword)
        <div class="alert alert-success d-flex justify-content-between align-items-center">
            <span><i class="fa-solid fa-key me-1"></i> {{ __('admin.users.reset_password_done', ['password' => $generatedPassword]) }}</span>
            <button class="btn btn-sm btn-light" onclick="navigator.clipboard.writeText('{{ $generatedPassword }}')">
                <i class="fa-regular fa-copy"></i>
            </button>
        </div>
    @endif

    @if ($showForm)
        <div class="card mb-3">
            <div class="card-header">{{ $editingId ? __('admin.users.edit_user') : __('admin.users.create_user') }}</div>
            <div class="card-body">
                <form wire:submit.prevent="save">
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('admin.users.name') }} <span class="text-danger">*</span></label>
                            <input type="text" wire:model="name" class="form-control @error('name') is-invalid @enderror">
                            @error('name') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('admin.users.email') }} <span class="text-danger">*</span></label>
                            <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror">
                            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('admin.users.role') }}</label>
                            <select wire:model="role" class="form-select @error('role') is-invalid @enderror">
                                <option value="admin">{{ __('admin.users.role_admin') }}</option>
                                <option value="writer">{{ __('admin.users.role_writer') }}</option>
                                <option value="editor">{{ __('admin.users.role_editor') }}</option>
                                <option value="viewer">{{ __('admin.users.role_viewer') }}</option>
                            </select>
                            @error('role') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('admin.users.password') }} @if (! $editingId) <span class="text-danger">*</span> @endif</label>
                            <input type="password" wire:model="password" class="form-control @error('password') is-invalid @enderror">
                            @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            @if ($editingId)
                                <div class="form-text small">{{ __('admin.users.password_help') }}</div>
                            @endif
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
                        <th style="width: 60px;">ID</th>
                        <th>{{ __('admin.users.name') }}</th>
                        <th>{{ __('admin.users.email') }}</th>
                        <th style="width: 130px;">{{ __('admin.users.role') }}</th>
                        <th style="width: 160px;">{{ __('admin.users.last_login') }}</th>
                        <th style="width: 200px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $u)
                        <tr wire:key="user-{{ $u->id }}">
                            <td class="text-muted small">#{{ $u->id }}</td>
                            <td>{{ $u->name }}</td>
                            <td>{{ $u->email }}</td>
                            <td><span class="lang-chip">{{ __('admin.users.role_'.$u->role) }}</span></td>
                            <td><span class="text-muted small">{{ $u->last_login_at?->diffForHumans() ?? __('admin.users.never_logged_in') }}</span></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" wire:click="edit({{ $u->id }})" title="{{ __('admin.common.edit') }}"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn btn-outline-secondary" type="button" x-data
                                            @click="$dispatch('open-confirm', { message: @js(__('admin.users.confirm_delete')), onConfirm: () => $wire.resetPassword({{ $u->id }}) })"
                                            title="{{ __('admin.users.reset_password') }}"><i class="fa-solid fa-key"></i></button>
                                    <button class="btn btn-outline-danger" type="button" x-data
                                            @click="$dispatch('open-confirm', { message: @js(__('admin.users.confirm_delete')), onConfirm: () => $wire.delete({{ $u->id }}) })"
                                            title="{{ __('admin.common.delete') }}" @disabled($u->id === auth()->id())><i class="fa-solid fa-trash"></i></button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="6"><div class="empty-state"><i class="fa-regular fa-user d-block"></i><div class="fw-semibold">{{ __('admin.users.no_users') }}</div></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
```

- [ ] **Step 4: Register route**

In `routes/web.php`, add inside the existing admin group:

```php
Route::get('/users', \App\Livewire\Admin\Users\UserIndex::class)->name('users.index');
```

Add `use App\Livewire\Admin\Users\UserIndex;` to the imports.

- [ ] **Step 5: Update sidebar**

In `resources/views/components/layouts/admin.blade.php`, after the closing `</a>` of the Tags link, add a new section:

```blade
<div class="nav-label">{{ __('admin.nav.administration') }}</div>
<a href="{{ route('admin.users.index') }}" class="{{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
    <i class="fa-solid fa-users"></i> {{ __('admin.nav.users') }}
</a>
```

Add to `lang/ru/admin.php` under `nav`:

```php
'administration' => 'Администрирование',
'users' => 'Пользователи',
```

Re-run stub generator.

---

## Task 3.5: Test User CRUD

**Files:**
- Create: `tests/Feature/Admin/UserCrudTest.php`

- [ ] **Step 1: Write tests**

```php
<?php

use App\Livewire\Admin\Users\UserIndex;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->admin);
});

describe('User CRUD', function () {
    it('creates a user', function () {
        Livewire::test(UserIndex::class)
            ->call('startCreate')
            ->set('name', 'New Editor')
            ->set('email', 'editor@x.test')
            ->set('role', 'editor')
            ->set('password', 'secret-pass')
            ->call('save')
            ->assertHasNoErrors();

        $u = User::where('email', 'editor@x.test')->first();
        expect($u)->not->toBeNull()->and($u->role)->toBe('editor');
        expect(Hash::check('secret-pass', $u->password))->toBeTrue();
    })->group('feature', 'admin');

    it('rejects duplicate email', function () {
        User::factory()->create(['email' => 'taken@x.test']);

        Livewire::test(UserIndex::class)
            ->call('startCreate')
            ->set('name', 'X')
            ->set('email', 'taken@x.test')
            ->set('password', 'secret-pass')
            ->call('save')
            ->assertHasErrors(['email']);
    })->group('feature', 'admin');

    it('blocks self-demotion', function () {
        Livewire::test(UserIndex::class)
            ->call('edit', $this->admin->id)
            ->set('role', 'viewer')
            ->call('save')
            ->assertHasErrors(['role']);

        expect($this->admin->fresh()->role)->toBe('admin');
    })->group('feature', 'admin');

    it('blocks self-deletion', function () {
        Livewire::test(UserIndex::class)
            ->call('delete', $this->admin->id);

        expect(User::find($this->admin->id))->not->toBeNull();
    })->group('feature', 'admin');

    it('resets password with a new random value', function () {
        $other = User::factory()->create();
        $oldHash = $other->password;

        Livewire::test(UserIndex::class)
            ->call('resetPassword', $other->id);

        expect($other->fresh()->password)->not->toBe($oldHash);
    })->group('feature', 'admin');
});
```

- [ ] **Step 2: Run**

Run: `php artisan test tests/Feature/Admin/UserCrudTest.php`
Expected: 5 tests pass.

---

## Task 3.6: Build Pages CRUD

**Files:**
- Create: `app/Livewire/Admin/Pages/PageIndex.php`
- Create: `app/Livewire/Admin/Pages/PageForm.php`
- Create: `resources/views/livewire/admin/pages/index.blade.php`
- Create: `resources/views/livewire/admin/pages/form.blade.php`

- [ ] **Step 1: Add `pages` keys to `lang/ru/admin.php`**

```php
'pages' => [
    'title_section' => 'Страницы',
    'subtitle' => 'Статические страницы (О нас, История, и т.д.).',
    'new_page' => 'Новая страница',
    'edit_page' => 'Редактирование страницы',
    'create_page' => 'Создание страницы',
    'slug' => 'Slug',
    'slug_help' => 'Внимание: изменение slug может сломать публичный маршрут.',
    'image' => 'Изображение',
    'title' => 'Заголовок',
    'content' => 'Содержание',
    'no_pages' => 'Страниц пока нет.',
    'confirm_delete' => 'Удалить эту страницу со всеми переводами?',
    'saved_flash' => 'Страница сохранена.',
    'deleted_flash' => 'Страница удалена.',
],
```

Re-run stub generator.

- [ ] **Step 2: Write `PageIndex.php`**

```php
<?php

namespace App\Livewire\Admin\Pages;

use App\Models\Page;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Страницы')]
class PageIndex extends Component
{
    public function delete(int $id): void
    {
        $page = Page::with('translations')->findOrFail($id);

        if ($page->image) {
            Storage::disk('public')->delete($page->image);
        }

        $page->delete();

        session()->flash('status', __('admin.pages.deleted_flash'));
    }

    public function render()
    {
        return view('livewire.admin.pages.index', [
            'pages' => Page::with('translations')->orderBy('id')->get(),
        ]);
    }
}
```

- [ ] **Step 3: Write `PageForm.php`**

```php
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
                    'seo_title' => $t->seo_title ?? null,
                    'seo_description' => $t->seo_description ?? null,
                ];
            }
        } else {
            foreach (self::LOCALES as $locale) {
                $this->translations[$locale] = [
                    'title' => '',
                    'content' => '',
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
                    'seo_title' => $data['seo_title'] ?? null,
                    'seo_description' => $data['seo_description'] ?? null,
                ]
            );
        }

        $this->page = $page->fresh('translations');
        $this->image = $page->image;
        $this->imageUpload = null;

        session()->flash('status', __('admin.pages.saved_flash'));

        $this->redirectRoute('admin.pages.edit', ['page' => $page->id], navigate: false);
    }

    public function render()
    {
        return view('livewire.admin.pages.form');
    }
}
```

Note: `App\Models\Page` and `PageTranslation` should already exist with `slug`, `image`, and translation columns matching `language/title/content/seo_title/seo_description`. If `image` is not on the `pages` table per the migration, we need a migration too. Verify with: `\Schema::getColumnListing('pages')`. If the column is missing, add a migration:

```bash
php artisan make:migration add_image_to_pages_table --table=pages --no-interaction
```

Then in the migration:
```php
public function up(): void { Schema::table('pages', fn ($t) => $t->string('image')->nullable()->after('slug')); }
public function down(): void { Schema::table('pages', fn ($t) => $t->dropColumn('image')); }
```

Run: `php artisan migrate`.

- [ ] **Step 4: Write Pages index view**

Create `resources/views/livewire/admin/pages/index.blade.php`:

```blade
<div>
    <div class="page-header">
        <div>
            <h1>{{ __('admin.pages.title_section') }}</h1>
            <div class="subtitle">{{ __('admin.pages.subtitle') }}</div>
        </div>
        <a href="{{ route('admin.pages.create') }}" class="btn btn-primary"><i class="fa-solid fa-plus me-1"></i> {{ __('admin.pages.new_page') }}</a>
    </div>
    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead><tr><th>ID</th><th>Slug</th><th>{{ __('admin.common.languages') }}</th><th></th></tr></thead>
                <tbody>
                    @forelse ($pages as $p)
                        @php $available = $p->translations->pluck('language')->all(); @endphp
                        <tr wire:key="page-{{ $p->id }}">
                            <td class="text-muted small">#{{ $p->id }}</td>
                            <td><code>{{ $p->slug }}</code></td>
                            <td>@foreach (['kr','uz','ru','en'] as $loc)<span class="lang-chip {{ in_array($loc, $available, true) ? '' : 'missing' }}">{{ $loc }}</span>@endforeach</td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <a href="{{ route('admin.pages.edit', $p) }}" class="btn btn-outline-primary"><i class="fa-solid fa-pen"></i></a>
                                    <button class="btn btn-outline-danger" type="button" x-data
                                            @click="$dispatch('open-confirm', { message: @js(__('admin.pages.confirm_delete')), onConfirm: () => $wire.delete({{ $p->id }}) })">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="4"><div class="empty-state"><i class="fa-regular fa-file d-block"></i><div class="fw-semibold">{{ __('admin.pages.no_pages') }}</div></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
```

- [ ] **Step 4b: Write Pages form view**

Create `resources/views/livewire/admin/pages/form.blade.php`:

```blade
<div>
    <div class="page-header">
        <div>
            <h1>{{ $page?->exists ? __('admin.pages.edit_page') : __('admin.pages.create_page') }}</h1>
            <div class="subtitle">{{ $page?->exists ? '#'.$page->id : __('admin.pages.create_page') }}</div>
        </div>
        <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> {{ __('admin.common.back') }}
        </a>
    </div>

    <form wire:submit.prevent="save">
        <div class="row g-3">
            <div class="col-lg-8">
                <div class="card mb-3">
                    <div class="card-body">
                        <ul class="nav nav-tabs lang-tabs">
                            @foreach (\App\Livewire\Admin\Pages\PageForm::LOCALES as $locale)
                                <li class="nav-item">
                                    <button type="button" class="nav-link {{ $activeLocale === $locale ? 'active' : '' }}" wire:click.prevent="setLocale('{{ $locale }}')">
                                        {{ strtoupper($locale) }}
                                    </button>
                                </li>
                            @endforeach
                        </ul>

                        @foreach (\App\Livewire\Admin\Pages\PageForm::LOCALES as $locale)
                            <div @class(['d-none' => $activeLocale !== $locale])>
                                <div class="mb-3">
                                    <label class="form-label">{{ __('admin.pages.title') }} @if ($locale === 'kr') <span class="text-danger">*</span> @endif</label>
                                    <input type="text" wire:model="translations.{{ $locale }}.title" class="form-control @error('translations.'.$locale.'.title') is-invalid @enderror">
                                    @error("translations.$locale.title") <div class="invalid-feedback">{{ $message }}</div> @enderror
                                </div>

                                <div class="mb-3">
                                    <label class="form-label">{{ __('admin.pages.content') }} @if ($locale === 'kr') <span class="text-danger">*</span> @endif</label>
                                    <div wire:ignore>
                                        <textarea
                                            id="page-editor-{{ $locale }}"
                                            class="tinymce-editor"
                                            data-locale="{{ $locale }}">{!! $translations[$locale]['content'] ?? '' !!}</textarea>
                                    </div>
                                    @error("translations.$locale.content") <div class="text-danger small mt-1">{{ $message }}</div> @enderror
                                </div>

                                <details class="mb-3">
                                    <summary class="text-muted small mb-2" style="cursor: pointer;">SEO ({{ strtoupper($locale) }})</summary>
                                    <div class="row g-2 mt-1">
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('admin.news.seo_title') }}</label>
                                            <input type="text" wire:model="translations.{{ $locale }}.seo_title" class="form-control">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label">{{ __('admin.news.seo_description') }}</label>
                                            <input type="text" wire:model="translations.{{ $locale }}.seo_description" class="form-control">
                                        </div>
                                    </div>
                                </details>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="col-lg-4">
                <div class="card mb-3" style="position: sticky; top: 80px;">
                    <div class="card-header">{{ __('admin.news.publishing') }}</div>
                    <div class="card-body">
                        <div class="mb-3">
                            <label class="form-label">{{ __('admin.pages.slug') }} <span class="text-danger">*</span></label>
                            <input type="text" wire:model="slug" class="form-control @error('slug') is-invalid @enderror">
                            @error('slug') <div class="invalid-feedback">{{ $message }}</div> @enderror
                            <div class="form-text small">{{ __('admin.pages.slug_help') }}</div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">{{ __('admin.pages.image') }}</label>
                            @if ($image)
                                <div class="mb-2"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image) }}" alt="" style="max-width: 100%; border-radius: 6px; border: 1px solid var(--admin-border-soft);"></div>
                            @endif
                            <input type="file" wire:model="imageUpload" accept="image/*" class="form-control @error('imageUpload') is-invalid @enderror">
                            @error('imageUpload') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            <span wire:loading.remove wire:target="save"><i class="fa-solid fa-floppy-disk me-1"></i> {{ __('admin.common.save') }}</span>
                            <span wire:loading wire:target="save"><i class="fa-solid fa-spinner fa-spin me-1"></i> {{ __('admin.common.saving') }}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

    @push('scripts')
        <script src="https://cdn.tiny.cloud/1/no-api-key/tinymce/7/tinymce.min.js" referrerpolicy="origin"></script>
        <script>
            (function () {
                const initEditor = (textarea) => {
                    if (!window.tinymce || textarea.dataset.tmceInitialized === '1') return;
                    textarea.dataset.tmceInitialized = '1';
                    const locale = textarea.dataset.locale;
                    window.tinymce.init({
                        target: textarea,
                        height: 480,
                        menubar: false,
                        promotion: false,
                        branding: false,
                        plugins: 'lists link image table code paste autolink',
                        toolbar: 'undo redo | styles | bold italic | bullist numlist | link image table | code',
                        setup: (editor) => {
                            editor.on('change input keyup', () => {
                                window.Livewire.find('{{ $this->getId() }}').set('translations.' + locale + '.content', editor.getContent(), false);
                            });
                        },
                    });
                };
                const initAll = () => document.querySelectorAll('textarea.tinymce-editor').forEach(initEditor);
                if (document.readyState !== 'loading') initAll(); else document.addEventListener('DOMContentLoaded', initAll);
                document.addEventListener('livewire:navigated', initAll);
            })();
        </script>
    @endpush
</div>
```

- [ ] **Step 5: Register Pages routes**

In `routes/web.php`, inside the admin group:

```php
Route::get('/pages', \App\Livewire\Admin\Pages\PageIndex::class)->name('pages.index');
Route::get('/pages/create', \App\Livewire\Admin\Pages\PageForm::class)->name('pages.create');
Route::get('/pages/{page}/edit', \App\Livewire\Admin\Pages\PageForm::class)->name('pages.edit');
```

- [ ] **Step 6: Update sidebar**

In the layout, add under "Content" section:

```blade
<a href="{{ route('admin.pages.index') }}" class="{{ request()->routeIs('admin.pages.*') ? 'active' : '' }}">
    <i class="fa-solid fa-file-lines"></i> {{ __('admin.nav.pages') }}
</a>
```

Add `'pages' => 'Страницы'` under `nav` in `lang/ru/admin.php`.

---

## Task 3.7: Test Pages CRUD

**Files:**
- Create: `tests/Feature/Admin/PageCrudTest.php`

- [ ] **Step 1: Write tests**

```php
<?php

use App\Livewire\Admin\Pages\PageForm;
use App\Models\Page;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

describe('Page CRUD', function () {
    it('creates a page with all 4 translations', function () {
        Storage::fake('public');

        Livewire::test(PageForm::class)
            ->set('slug', 'about-test')
            ->set('translations.kr.title', 'KR')
            ->set('translations.kr.content', '<p>kr</p>')
            ->set('translations.uz.title', 'UZ')
            ->set('translations.uz.content', '<p>uz</p>')
            ->set('translations.ru.title', 'RU')
            ->set('translations.ru.content', '<p>ru</p>')
            ->set('translations.en.title', 'EN')
            ->set('translations.en.content', '<p>en</p>')
            ->set('imageUpload', UploadedFile::fake()->image('p.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        $p = Page::where('slug', 'about-test')->first();
        expect($p)->not->toBeNull()->and($p->translations()->count())->toBe(4);
        expect($p->image)->toStartWith('pages/');
    })->group('feature', 'admin');

    it('rejects duplicate slug', function () {
        Page::factory()->create(['slug' => 'taken']);

        Livewire::test(PageForm::class)
            ->set('slug', 'taken')
            ->set('translations.kr.title', 't')
            ->set('translations.kr.content', '<p>c</p>')
            ->call('save')
            ->assertHasErrors(['slug']);
    })->group('feature', 'admin');
});
```

- [ ] **Step 2: Run**

Run: `php artisan test tests/Feature/Admin/PageCrudTest.php`
Expected: 2 tests pass.

---

## Task 3.8: Build Videos CRUD

**Files:**
- Create: `app/Livewire/Admin/Videos/VideoIndex.php`
- Create: `resources/views/livewire/admin/videos/index.blade.php`

- [ ] **Step 1: Add `videos` keys to `lang/ru/admin.php`**

```php
'videos' => [
    'title_section' => 'Видео',
    'subtitle' => 'Видеоматериалы.',
    'new_video' => 'Новое видео',
    'edit_video' => 'Редактирование',
    'create_video' => 'Создание видео',
    'title' => 'Название',
    'image' => 'Превью',
    'url' => 'URL',
    'no_videos' => 'Видео пока нет.',
    'confirm_delete' => 'Удалить это видео?',
    'saved_flash' => 'Видео сохранено.',
    'deleted_flash' => 'Видео удалено.',
],
```

Re-run stub generator.

- [ ] **Step 2: Write component**

```php
<?php

namespace App\Livewire\Admin\Videos;

use App\Livewire\Concerns\HandlesImageUploads;
use App\Models\Video;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('components.layouts.admin')]
#[Title('Видео')]
class VideoIndex extends Component
{
    use HandlesImageUploads, WithFileUploads;

    public ?int $editingId = null;

    public string $title = '';

    public string $url = '';

    public ?string $image = null;

    public $imageUpload = null;

    public bool $showForm = false;

    protected function rules(): array
    {
        return [
            'title' => ['required', 'string', 'max:255'],
            'url' => ['required', 'url', 'max:500'],
            'imageUpload' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp,gif', 'max:5120'],
        ];
    }

    public function startCreate(): void
    {
        $this->resetForm();
        $this->showForm = true;
    }

    public function edit(int $id): void
    {
        $v = Video::findOrFail($id);
        $this->editingId = $v->id;
        $this->title = $v->title;
        $this->url = $v->url;
        $this->image = $v->image;
        $this->showForm = true;
    }

    public function save(): void
    {
        $this->validate();

        $v = $this->editingId ? Video::findOrFail($this->editingId) : new Video;
        $v->title = $this->title;
        $v->url = $this->url;
        if ($this->imageUpload) {
            $newImage = $this->storeUploadedImage($this->imageUpload, 'videos');
            $this->deleteStoredImage($v->image);
            $v->image = $newImage;
        }
        $v->save();

        session()->flash('status', __('admin.videos.saved_flash'));
        $this->resetForm();
        $this->showForm = false;
    }

    public function delete(int $id): void
    {
        $v = Video::findOrFail($id);
        $this->deleteStoredImage($v->image);
        $v->delete();
        session()->flash('status', __('admin.videos.deleted_flash'));
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
        $this->url = '';
        $this->image = null;
        $this->imageUpload = null;
        $this->resetValidation();
    }

    public function render()
    {
        return view('livewire.admin.videos.index', [
            'videos' => Video::orderBy('id', 'desc')->get(),
        ]);
    }
}
```

- [ ] **Step 3: Write view**

Create `resources/views/livewire/admin/videos/index.blade.php`:

```blade
<div>
    <div class="page-header">
        <div>
            <h1>{{ __('admin.videos.title_section') }}</h1>
            <div class="subtitle">{{ __('admin.videos.subtitle') }}</div>
        </div>
        @if (! $showForm)
            <button type="button" class="btn btn-primary" wire:click="startCreate">
                <i class="fa-solid fa-plus me-1"></i> {{ __('admin.videos.new_video') }}
            </button>
        @endif
    </div>

    @if ($showForm)
        <div class="card mb-3">
            <div class="card-header">{{ $editingId ? __('admin.videos.edit_video') : __('admin.videos.create_video') }}</div>
            <div class="card-body">
                <form wire:submit.prevent="save">
                    <div class="row g-2 mb-3">
                        <div class="col-md-6">
                            <label class="form-label">{{ __('admin.videos.title') }} <span class="text-danger">*</span></label>
                            <input type="text" wire:model="title" class="form-control @error('title') is-invalid @enderror">
                            @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">{{ __('admin.videos.url') }} <span class="text-danger">*</span></label>
                            <input type="url" wire:model="url" class="form-control @error('url') is-invalid @enderror" placeholder="https://...">
                            @error('url') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-12">
                            <label class="form-label">{{ __('admin.videos.image') }}</label>
                            @if ($image)
                                <div class="mb-2"><img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($image) }}" alt="" style="max-width: 220px; border-radius: 6px; border: 1px solid var(--admin-border-soft);"></div>
                            @endif
                            <input type="file" wire:model="imageUpload" accept="image/*" class="form-control @error('imageUpload') is-invalid @enderror">
                            @error('imageUpload') <div class="invalid-feedback">{{ $message }}</div> @enderror
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
                        <th style="width: 60px;">ID</th>
                        <th style="width: 90px;">{{ __('admin.videos.image') }}</th>
                        <th>{{ __('admin.videos.title') }}</th>
                        <th>{{ __('admin.videos.url') }}</th>
                        <th style="width: 120px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($videos as $v)
                        <tr wire:key="video-{{ $v->id }}">
                            <td class="text-muted small">#{{ $v->id }}</td>
                            <td>@if ($v->image)<img src="{{ \Illuminate\Support\Facades\Storage::disk('public')->url($v->image) }}" alt="" style="width: 60px; height: 40px; object-fit: cover; border-radius: 4px;">@endif</td>
                            <td class="fw-semibold">{{ $v->title }}</td>
                            <td><a href="{{ $v->url }}" target="_blank" class="text-truncate d-inline-block" style="max-width: 240px;">{{ $v->url }}</a></td>
                            <td class="text-end">
                                <div class="btn-group btn-group-sm">
                                    <button class="btn btn-outline-primary" wire:click="edit({{ $v->id }})"><i class="fa-solid fa-pen"></i></button>
                                    <button class="btn btn-outline-danger" type="button" x-data
                                            @click="$dispatch('open-confirm', { message: @js(__('admin.videos.confirm_delete')), onConfirm: () => $wire.delete({{ $v->id }}) })">
                                        <i class="fa-solid fa-trash"></i>
                                    </button>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-video d-block"></i><div class="fw-semibold">{{ __('admin.videos.no_videos') }}</div></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
```

- [ ] **Step 4: Register route**

```php
Route::get('/videos', \App\Livewire\Admin\Videos\VideoIndex::class)->name('videos.index');
```

Note: this conflicts with the existing public `/videos` because the admin group is prefixed `/admin`. The route name is `admin.videos.index` so no collision.

- [ ] **Step 5: Update sidebar**

Add `'videos' => 'Видео'` under `nav` in `lang/ru/admin.php`. Add link in layout under "Content".

---

## Task 3.9: Test Videos CRUD

**Files:**
- Create: `tests/Feature/Admin/VideoCrudTest.php`

- [ ] **Step 1: Write tests**

```php
<?php

use App\Livewire\Admin\Videos\VideoIndex;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

describe('Video CRUD', function () {
    it('creates a video', function () {
        Storage::fake('public');

        Livewire::test(VideoIndex::class)
            ->call('startCreate')
            ->set('title', 'My video')
            ->set('url', 'https://youtube.com/watch?v=xyz')
            ->set('imageUpload', UploadedFile::fake()->image('thumb.jpg'))
            ->call('save')
            ->assertHasNoErrors();

        expect(Video::count())->toBe(1);
        expect(Video::first()->image)->toStartWith('videos/');
    })->group('feature', 'admin');

    it('requires a valid URL', function () {
        Livewire::test(VideoIndex::class)
            ->call('startCreate')
            ->set('title', 'X')
            ->set('url', 'not-a-url')
            ->call('save')
            ->assertHasErrors(['url']);
    })->group('feature', 'admin');

    it('deletes a video and its image file', function () {
        Storage::fake('public');
        Storage::disk('public')->put('videos/v.jpg', 'fake');
        $v = Video::factory()->create(['image' => 'videos/v.jpg']);

        Livewire::test(VideoIndex::class)->call('delete', $v->id);

        expect(Video::find($v->id))->toBeNull();
        Storage::disk('public')->assertMissing('videos/v.jpg');
    })->group('feature', 'admin');
});
```

- [ ] **Step 2: Run**

Run: `php artisan test tests/Feature/Admin/VideoCrudTest.php`
Expected: 3 tests pass.

---

## Task 3.10: Phase 3 final test pass + commit

- [ ] **Step 1: Pint + tests**

Run: `vendor/bin/pint --dirty --format agent`
Run: `php artisan test --compact`
Expected: all tests pass (217+ from Phase 2 + 11 new = 228+).

- [ ] **Step 2: Commit**

```bash
git add app/Livewire/ app/Models/User.php app/Http/ database/migrations/ resources/views/ routes/web.php lang/ tests/Feature/Admin/

git commit -m "$(cat <<'EOF'
feat(admin): Users, Pages, Videos CRUD + last_login_at (Phase 3)

- New User management with role + reset-password + self-protection
- New Pages CRUD with 4-language tabs and image upload
- New Videos CRUD (title + image + URL)
- Add last_login_at to users; recorded on login
- Extract HandlesImageUploads trait

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

# Phase 4 — Bulk + Media library + Activity log

**Objective:** select-and-bulk-act on news, browse a filesystem-backed media library and pick existing images, log every CRUD action with diffs.

---

## Task 4.1: Add bulk-select to News index

**Files:**
- Modify: `app/Livewire/Admin/News/NewsIndex.php`
- Modify: `resources/views/livewire/admin/news/index.blade.php`

- [ ] **Step 1: Add component properties + methods**

Add to the class:

```php
/** @var array<int> */
public array $selected = [];

public bool $selectAll = false;

public function updatedSelectAll(bool $value): void
{
    $this->selected = $value ? $this->currentPageIds() : [];
}

private function currentPageIds(): array
{
    return $this->renderedQuery()->paginate(15)->pluck('id')->all();
}

private function renderedQuery()
{
    $query = \App\Models\News::query()->latest();
    if ($this->status !== '') $query->where('status', $this->status);
    if ($this->category !== '') $query->where('category_id', $this->category);
    if ($this->search !== '') {
        $query->where(function ($q) {
            $q->where('slug', 'like', '%'.$this->search.'%')
              ->orWhereHas('translations', fn ($t) => $t->where('title', 'like', '%'.$this->search.'%'));
        });
    }
    return $query;
}

public function bulkPublish(): void
{
    if (! $this->selected) return;
    \App\Models\News::whereIn('id', $this->selected)->update(['status' => 'published']);
    session()->flash('status', __('admin.bulk.published_count', ['count' => count($this->selected)]));
    $this->selected = [];
    $this->selectAll = false;
}

public function bulkUnpublish(): void
{
    if (! $this->selected) return;
    \App\Models\News::whereIn('id', $this->selected)->update(['status' => 'draft']);
    session()->flash('status', __('admin.bulk.unpublished_count', ['count' => count($this->selected)]));
    $this->selected = [];
    $this->selectAll = false;
}

public function bulkDelete(): void
{
    if (! $this->selected) return;
    foreach ($this->selected as $id) {
        $this->delete($id);
    }
    $this->selected = [];
    $this->selectAll = false;
}
```

Update `render()` to use `$this->renderedQuery()->with(['translations', 'category.translations'])->paginate(15)` instead of building inline.

- [ ] **Step 2: Add bulk keys to `lang/ru/admin.php`**

```php
'bulk' => [
    'selected' => 'Выбрано: :count',
    'publish' => 'Опубликовать',
    'unpublish' => 'Снять с публикации',
    'delete' => 'Удалить',
    'published_count' => 'Опубликовано: :count',
    'unpublished_count' => 'Снято с публикации: :count',
    'confirm_delete' => 'Удалить :count выбранных статей?',
],
```

Re-run stub generator.

- [ ] **Step 3: Update view**

Add a new column header before the existing first `<th>`:

```blade
<th style="width: 36px;"><input type="checkbox" wire:model.live="selectAll"></th>
```

Inside the row, add the matching cell as the first `<td>`:

```blade
<td><input type="checkbox" wire:model.live="selected" value="{{ $item->id }}"></td>
```

Update existing `colspan="8"` empty-state to `colspan="9"`.

Above the table card, add an action bar that shows when items are selected:

```blade
<div x-data x-show="$wire.selected.length > 0" x-cloak class="card mb-3 p-3 d-flex flex-row gap-2 align-items-center" style="background: #fff7ed; border-color: #fed7aa;">
    <span class="fw-semibold flex-grow-1">{{ __('admin.bulk.selected', ['count' => count($selected)]) }}</span>
    <button class="btn btn-sm btn-success" wire:click="bulkPublish"><i class="fa-solid fa-circle-check me-1"></i> {{ __('admin.bulk.publish') }}</button>
    <button class="btn btn-sm btn-secondary" wire:click="bulkUnpublish"><i class="fa-solid fa-eye-slash me-1"></i> {{ __('admin.bulk.unpublish') }}</button>
    <button class="btn btn-sm btn-danger" type="button" x-data
            @click="$dispatch('open-confirm', { message: @js(__('admin.bulk.confirm_delete', ['count' => count($selected)])), onConfirm: () => $wire.bulkDelete() })">
        <i class="fa-solid fa-trash me-1"></i> {{ __('admin.bulk.delete') }}
    </button>
</div>
```

---

## Task 4.2: Test bulk actions

**Files:**
- Create: `tests/Feature/Admin/BulkActionsTest.php`

- [ ] **Step 1: Write tests**

```php
<?php

use App\Livewire\Admin\News\NewsIndex;
use App\Models\News;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

describe('Bulk news actions', function () {
    it('publishes selected drafts in bulk', function () {
        $a = News::factory()->create(['status' => 'draft']);
        $b = News::factory()->create(['status' => 'draft']);
        $c = News::factory()->create(['status' => 'draft']);

        Livewire::test(NewsIndex::class)
            ->set('selected', [$a->id, $b->id])
            ->call('bulkPublish');

        expect($a->fresh()->status)->toBe('published');
        expect($b->fresh()->status)->toBe('published');
        expect($c->fresh()->status)->toBe('draft');
    })->group('feature', 'admin');

    it('unpublishes selected items', function () {
        $a = News::factory()->create(['status' => 'published']);

        Livewire::test(NewsIndex::class)
            ->set('selected', [$a->id])
            ->call('bulkUnpublish');

        expect($a->fresh()->status)->toBe('draft');
    })->group('feature', 'admin');

    it('deletes selected items and removes managed cover files', function () {
        Storage::fake('public');
        Storage::disk('public')->put('news/covers/x.jpg', 'fake');
        $a = News::factory()->create();
        $a->translations()->create([
            'lang' => 'kr', 'title' => 't', 'short_description' => 's',
            'content' => '<p>c</p>', 'image_url' => 'news/covers/x.jpg',
        ]);

        Livewire::test(NewsIndex::class)
            ->set('selected', [$a->id])
            ->call('bulkDelete');

        expect(News::find($a->id))->toBeNull();
        Storage::disk('public')->assertMissing('news/covers/x.jpg');
    })->group('feature', 'admin');
});
```

- [ ] **Step 2: Run**

Run: `php artisan test tests/Feature/Admin/BulkActionsTest.php`
Expected: 3 tests pass.

---

## Task 4.3: Build Media library

**Files:**
- Create: `app/Livewire/Admin/Media/MediaIndex.php`
- Create: `resources/views/livewire/admin/media/index.blade.php`

- [ ] **Step 1: Add `media` keys to `lang/ru/admin.php`**

```php
'media' => [
    'title_section' => 'Медиатека',
    'subtitle' => 'Загруженные изображения. Источник истины — диск, не БД.',
    'all_folders' => 'Все папки',
    'covers' => 'Обложки',
    'inline' => 'Внутри статей',
    'pages' => 'Страницы',
    'videos' => 'Видео',
    'search' => 'Поиск по имени файла…',
    'no_files' => 'Файлов пока нет.',
    'confirm_delete' => 'Удалить файл? Статьи, использующие его, покажут битое изображение.',
    'choose_existing' => 'Выбрать из библиотеки',
],
```

Re-run stub generator.

- [ ] **Step 2: Write `MediaIndex.php`**

```php
<?php

namespace App\Livewire\Admin\Media;

use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;

#[Layout('components.layouts.admin')]
#[Title('Медиатека')]
class MediaIndex extends Component
{
    #[Url(except: '')]
    public string $search = '';

    #[Url(except: '')]
    public string $folder = '';

    public function delete(string $path): void
    {
        if (! $this->isManagedPath($path)) {
            return;
        }
        Storage::disk('public')->delete($path);
    }

    private function isManagedPath(string $path): bool
    {
        foreach (['news/covers/', 'news/inline/', 'pages/', 'videos/'] as $prefix) {
            if (str_starts_with($path, $prefix)) {
                return true;
            }
        }

        return false;
    }

    public function render()
    {
        $folders = $this->folder !== ''
            ? [$this->folder]
            : ['news/covers', 'news/inline', 'pages', 'videos'];

        $files = collect();
        foreach ($folders as $folder) {
            foreach (Storage::disk('public')->files($folder) as $f) {
                $files->push([
                    'path' => $f,
                    'name' => basename($f),
                    'url' => Storage::disk('public')->url($f),
                    'size' => Storage::disk('public')->size($f),
                    'modified' => Storage::disk('public')->lastModified($f),
                    'folder' => $folder,
                ]);
            }
        }

        if ($this->search !== '') {
            $files = $files->filter(fn ($f) => str_contains(strtolower($f['name']), strtolower($this->search)));
        }

        $files = $files->sortByDesc('modified')->values();

        return view('livewire.admin.media.index', [
            'files' => $files,
        ]);
    }
}
```

- [ ] **Step 3: Write the view**

Create `resources/views/livewire/admin/media/index.blade.php`:

```blade
<div>
    <div class="page-header">
        <div>
            <h1>{{ __('admin.media.title_section') }}</h1>
            <div class="subtitle">{{ __('admin.media.subtitle') }}</div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-6">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="{{ __('admin.media.search') }}">
                </div>
                <div class="col-md-6">
                    <select wire:model.live="folder" class="form-select">
                        <option value="">{{ __('admin.media.all_folders') }}</option>
                        <option value="news/covers">{{ __('admin.media.covers') }}</option>
                        <option value="news/inline">{{ __('admin.media.inline') }}</option>
                        <option value="pages">{{ __('admin.media.pages') }}</option>
                        <option value="videos">{{ __('admin.media.videos') }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    @if ($files->isEmpty())
        <div class="card"><div class="empty-state"><i class="fa-regular fa-image d-block"></i><div class="fw-semibold">{{ __('admin.media.no_files') }}</div></div></div>
    @else
        <div class="row g-3">
            @foreach ($files as $f)
                <div class="col-md-4 col-xl-3" wire:key="media-{{ $f['path'] }}">
                    <div class="card h-100">
                        <img src="{{ $f['url'] }}" alt="" style="width: 100%; height: 160px; object-fit: cover; border-radius: 10px 10px 0 0;">
                        <div class="card-body">
                            <div class="text-truncate fw-semibold small" title="{{ $f['name'] }}">{{ $f['name'] }}</div>
                            <div class="text-muted small mt-1">
                                {{ number_format($f['size'] / 1024, 1) }} KB · {{ \Carbon\Carbon::createFromTimestamp($f['modified'])->diffForHumans() }}
                            </div>
                        </div>
                        <div class="card-footer p-2 d-flex justify-content-between align-items-center">
                            <span class="lang-chip">{{ $f['folder'] }}</span>
                            <button class="btn btn-sm btn-outline-danger" type="button" x-data
                                    @click="$dispatch('open-confirm', { message: @js(__('admin.media.confirm_delete')), onConfirm: () => $wire.delete('{{ $f['path'] }}') })">
                                <i class="fa-solid fa-trash"></i>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>
```

- [ ] **Step 4: Register route + sidebar**

```php
Route::get('/media', \App\Livewire\Admin\Media\MediaIndex::class)->name('media.index');
```

Sidebar link under "Content":

```blade
<a href="{{ route('admin.media.index') }}" class="{{ request()->routeIs('admin.media.*') ? 'active' : '' }}">
    <i class="fa-solid fa-photo-film"></i> {{ __('admin.nav.media') }}
</a>
```

Add `'media' => 'Медиатека'` under `nav` in admin.php.

---

## Task 4.4: Build Media picker (modal)

**Files:**
- Create: `app/Livewire/Admin/Media/MediaPicker.php`
- Create: `resources/views/livewire/admin/media/picker.blade.php`

- [ ] **Step 1: Component**

```php
<?php

namespace App\Livewire\Admin\Media;

use Illuminate\Support\Facades\Storage;
use Livewire\Component;

class MediaPicker extends Component
{
    public bool $open = false;

    public string $search = '';

    public string $folder = 'news/covers';

    public string $contextEvent = 'media-picked';

    public function show(string $folder = 'news/covers', string $contextEvent = 'media-picked'): void
    {
        $this->folder = $folder;
        $this->contextEvent = $contextEvent;
        $this->open = true;
    }

    public function pick(string $path): void
    {
        $url = Storage::disk('public')->url($path);
        $this->dispatch($this->contextEvent, path: $path, url: $url);
        $this->open = false;
    }

    public function render()
    {
        $files = collect(Storage::disk('public')->files($this->folder))
            ->map(fn ($p) => [
                'path' => $p,
                'name' => basename($p),
                'url' => Storage::disk('public')->url($p),
                'modified' => Storage::disk('public')->lastModified($p),
            ])
            ->when($this->search !== '', fn ($c) => $c->filter(fn ($f) => str_contains(strtolower($f['name']), strtolower($this->search))))
            ->sortByDesc('modified')
            ->values();

        return view('livewire.admin.media.picker', ['files' => $files]);
    }
}
```

- [ ] **Step 2: View**

Create `resources/views/livewire/admin/media/picker.blade.php`:

```blade
<div>
    @if ($open)
        <div style="position: fixed; inset: 0; z-index: 1080; background: rgba(15,23,42,.55); display: flex; align-items: center; justify-content: center;"
             wire:click.self="$set('open', false)">
            <div style="background: #fff; border-radius: 12px; padding: 1.25rem; max-width: 920px; width: calc(100% - 2rem); max-height: 80vh; overflow: auto;">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="mb-0">{{ __('admin.media.title_section') }}</h5>
                    <button class="btn btn-sm btn-outline-secondary" wire:click="$set('open', false)">×</button>
                </div>
                <div class="mb-3">
                    <input type="text" wire:model.live.debounce.300ms="search" class="form-control" placeholder="{{ __('admin.media.search') }}">
                </div>
                @if ($files->isEmpty())
                    <div class="empty-state"><i class="fa-regular fa-image d-block"></i><div>{{ __('admin.media.no_files') }}</div></div>
                @else
                    <div class="row g-2">
                        @foreach ($files as $f)
                            <div class="col-md-3" wire:key="picker-{{ $f['path'] }}">
                                <button type="button" wire:click="pick('{{ $f['path'] }}')" class="btn p-0 w-100" style="border: 1px solid var(--admin-border); border-radius: 8px; overflow: hidden; background: #fff;">
                                    <img src="{{ $f['url'] }}" alt="" style="width: 100%; height: 110px; object-fit: cover;">
                                    <div class="p-2 text-truncate small">{{ $f['name'] }}</div>
                                </button>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    @endif
</div>
```

- [ ] **Step 3: Wire into NewsForm**

In `news/form.blade.php` add an event listener:

```blade
<div x-data x-on:media-picked.window="(e) => $wire.set('translations.{{ $activeLocale }}.image_url', e.detail.path)">
    <livewire:admin.media.media-picker />
    {{-- existing cover image markup --}}
    <button type="button" class="btn btn-sm btn-outline-secondary mb-2" @click="$dispatch('show-picker', { folder: 'news/covers' })">
        <i class="fa-regular fa-images me-1"></i> {{ __('admin.media.choose_existing') }}
    </button>
</div>
```

And in the picker component listen for `show-picker`:

```php
protected $listeners = ['show-picker' => 'showFromEvent'];

public function showFromEvent(array $detail = []): void
{
    $this->show($detail['folder'] ?? 'news/covers');
}
```

(Note: the cover URL persists `image_url` to a `news/covers/...` path; existing `coverUrl()` accessor handles rendering.)

- [ ] **Step 4: TinyMCE file_picker_callback**

In the existing TinyMCE init script in `news/form.blade.php`, add:

```js
file_picker_types: 'image',
file_picker_callback: function (callback, value, meta) {
    window.dispatchEvent(new CustomEvent('show-picker', { detail: { folder: 'news/inline' } }));
    window.addEventListener('media-picked', function handler(e) {
        callback(e.detail.url, { alt: '' });
        window.removeEventListener('media-picked', handler);
    }, { once: true });
},
```

---

## Task 4.5: Test media library

**Files:**
- Create: `tests/Feature/Admin/MediaLibraryTest.php`

```php
<?php

use App\Livewire\Admin\Media\MediaIndex;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

describe('Media library', function () {
    it('lists files from managed folders', function () {
        Storage::disk('public')->put('news/covers/a.jpg', 'fake');
        Storage::disk('public')->put('news/inline/b.png', 'fake');

        Livewire::test(MediaIndex::class)
            ->assertSee('a.jpg')
            ->assertSee('b.png');
    })->group('feature', 'admin');

    it('deletes a file from disk', function () {
        Storage::disk('public')->put('news/covers/del.jpg', 'fake');

        Livewire::test(MediaIndex::class)->call('delete', 'news/covers/del.jpg');

        Storage::disk('public')->assertMissing('news/covers/del.jpg');
    })->group('feature', 'admin');

    it('does not delete paths outside managed folders', function () {
        Storage::disk('public')->put('something/else.jpg', 'fake');

        Livewire::test(MediaIndex::class)->call('delete', 'something/else.jpg');

        Storage::disk('public')->assertExists('something/else.jpg');
    })->group('feature', 'admin');
});
```

Run: `php artisan test tests/Feature/Admin/MediaLibraryTest.php`
Expected: 3 tests pass.

---

## Task 4.6: Activity log — migration + model

**Files:**
- Create: `database/migrations/{timestamp}_create_activity_log_table.php`
- Create: `app/Models/Activity.php`
- Create: `app/Models/Concerns/LogsActivity.php`

- [ ] **Step 1: Migration**

Run: `php artisan make:migration create_activity_log_table --create=activity_log --no-interaction`

Replace generated migration with:

```php
public function up(): void
{
    Schema::create('activity_log', function (Blueprint $table) {
        $table->id();
        $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
        $table->string('subject_type');
        $table->unsignedBigInteger('subject_id')->nullable();
        $table->string('action', 64);
        $table->jsonb('changes')->nullable();
        $table->timestamp('created_at')->useCurrent();
        $table->index(['subject_type', 'subject_id']);
        $table->index('created_at');
    });
}

public function down(): void
{
    Schema::dropIfExists('activity_log');
}
```

Run: `php artisan migrate`.

- [ ] **Step 2: Model**

Create `app/Models/Activity.php`:

```php
<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Activity extends Model
{
    protected $table = 'activity_log';

    public $timestamps = false;

    protected $fillable = ['user_id', 'subject_type', 'subject_id', 'action', 'changes', 'created_at'];

    protected function casts(): array
    {
        return ['changes' => 'array', 'created_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }
}
```

- [ ] **Step 3: Trait**

Create `app/Models/Concerns/LogsActivity.php`:

```php
<?php

namespace App\Models\Concerns;

use App\Models\Activity;

trait LogsActivity
{
    public function logActivity(string $action, array $changes = []): void
    {
        try {
            Activity::create([
                'user_id' => auth()->id(),
                'subject_type' => static::class,
                'subject_id' => $this->getKey(),
                'action' => $action,
                'changes' => $changes ?: null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            \Log::warning('Activity log failed: '.$e->getMessage());
        }
    }
}
```

Add `use \App\Models\Concerns\LogsActivity;` to the relevant models: `News`, `Page`, `Video`, `User`.

---

## Task 4.7: Wire activity logging into CRUD

**Files:**
- Modify: `app/Livewire/Admin/News/NewsForm.php`
- Modify: `app/Livewire/Admin/News/NewsIndex.php`
- Modify: `app/Livewire/Admin/Pages/PageForm.php`
- Modify: `app/Livewire/Admin/Videos/VideoIndex.php`
- Modify: `app/Livewire/Admin/Users/UserIndex.php`
- Modify: `app/Livewire/Admin/Categories/CategoryIndex.php`
- Modify: `app/Livewire/Admin/Tags/TagIndex.php`

- [ ] **Step 1: NewsForm**

In `save()`, after `$news->save()` (the parent save before translations):

```php
$isNew = ! $news->wasRecentlyCreated ? false : true;
// ... existing translation/tag sync ...

// At end of save(), before redirect:
$news->logActivity($isNew ? 'created' : 'updated', $this->summarizeChanges($news));
```

Add:

```php
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
```

Note: getChanges returns dirty-after-save attributes. For "content changed" detection per translation, we'd need to compare more carefully — for this round, flagging `translations` => `'changed'` is sufficient.

- [ ] **Step 2: NewsIndex delete + bulk methods**

Before each individual deletion:

```php
$news->logActivity('deleted', ['slug' => $news->slug]);
```

For `bulkPublish` / `bulkUnpublish`, after the update query:

```php
foreach (\App\Models\News::whereIn('id', $this->selected)->get() as $n) {
    $n->logActivity($action, ['status' => $n->status]);
}
```

(where `$action` is `'published'` or `'unpublished'`).

- [ ] **Step 3: Apply same pattern to Pages, Videos, Users, Categories, Tags**

Each `save()` calls `logActivity` with `created`/`updated`; each `delete()` calls `logActivity('deleted', ['name' => ...])` before the delete (since after delete the model id is gone).

For `User::resetPassword`, log a `'reset_password'` activity row with empty `changes` (no payload — never include the new password or hash).

---

## Task 4.8: Activity index page

**Files:**
- Create: `app/Livewire/Admin/Activity/ActivityIndex.php`
- Create: `resources/views/livewire/admin/activity/index.blade.php`

- [ ] **Step 1: Add activity keys**

```php
'activity' => [
    'title_section' => 'Журнал действий',
    'subtitle' => 'История изменений в админ-панели.',
    'when' => 'Время',
    'who' => 'Кто',
    'what' => 'Действие',
    'subject' => 'Объект',
    'changes' => 'Изменения',
    'no_activity' => 'Записей пока нет.',
    'action_created' => 'создал',
    'action_updated' => 'изменил',
    'action_deleted' => 'удалил',
    'action_published' => 'опубликовал',
    'action_unpublished' => 'снял с публикации',
    'action_reset_password' => 'сбросил пароль',
    'system' => 'Система',
],
```

Re-run stub generator.

- [ ] **Step 2: Component + view**

```php
<?php

namespace App\Livewire\Admin\Activity;

use App\Models\Activity;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('components.layouts.admin')]
#[Title('Журнал действий')]
class ActivityIndex extends Component
{
    use WithPagination;

    #[Url(except: '')]
    public string $action = '';

    #[Url(except: '')]
    public string $subjectType = '';

    public function render()
    {
        $query = Activity::with('user')->latest('created_at');

        if ($this->action !== '') {
            $query->where('action', $this->action);
        }
        if ($this->subjectType !== '') {
            $query->where('subject_type', $this->subjectType);
        }

        return view('livewire.admin.activity.index', [
            'activities' => $query->paginate(30),
        ]);
    }
}
```

Create `resources/views/livewire/admin/activity/index.blade.php`:

```blade
<div>
    <div class="page-header">
        <div>
            <h1>{{ __('admin.activity.title_section') }}</h1>
            <div class="subtitle">{{ __('admin.activity.subtitle') }}</div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <div class="row g-2">
                <div class="col-md-6">
                    <select wire:model.live="action" class="form-select">
                        <option value="">{{ __('admin.common.all') }}</option>
                        <option value="created">{{ __('admin.activity.action_created') }}</option>
                        <option value="updated">{{ __('admin.activity.action_updated') }}</option>
                        <option value="deleted">{{ __('admin.activity.action_deleted') }}</option>
                        <option value="published">{{ __('admin.activity.action_published') }}</option>
                        <option value="unpublished">{{ __('admin.activity.action_unpublished') }}</option>
                    </select>
                </div>
                <div class="col-md-6">
                    <select wire:model.live="subjectType" class="form-select">
                        <option value="">{{ __('admin.common.all') }}</option>
                        <option value="App\Models\News">{{ __('admin.nav.news') }}</option>
                        <option value="App\Models\Page">{{ __('admin.nav.pages') }}</option>
                        <option value="App\Models\Video">{{ __('admin.nav.videos') }}</option>
                        <option value="App\Models\User">{{ __('admin.nav.users') }}</option>
                    </select>
                </div>
            </div>
        </div>
    </div>

    <div class="card">
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th>{{ __('admin.activity.when') }}</th>
                        <th>{{ __('admin.activity.who') }}</th>
                        <th>{{ __('admin.activity.what') }}</th>
                        <th>{{ __('admin.activity.subject') }}</th>
                        <th>{{ __('admin.activity.changes') }}</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($activities as $a)
                        <tr wire:key="activity-{{ $a->id }}">
                            <td><span class="text-muted small">{{ $a->created_at->diffForHumans() }}</span></td>
                            <td>{{ $a->user->name ?? __('admin.activity.system') }}</td>
                            <td>{{ __('admin.activity.action_'.$a->action) }}</td>
                            <td><code class="small">{{ class_basename($a->subject_type) }} #{{ $a->subject_id }}</code></td>
                            <td><code class="small">{{ json_encode($a->changes ?? [], JSON_UNESCAPED_UNICODE) }}</code></td>
                        </tr>
                    @empty
                        <tr><td colspan="5"><div class="empty-state"><i class="fa-solid fa-clock-rotate-left d-block"></i><div class="fw-semibold">{{ __('admin.activity.no_activity') }}</div></div></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($activities->hasPages())
            <div class="card-footer">{{ $activities->onEachSide(1)->links() }}</div>
        @endif
    </div>
</div>
```

Register route:
```php
Route::get('/activity', \App\Livewire\Admin\Activity\ActivityIndex::class)->name('activity.index');
```

Add sidebar link under "Administration" section:
```blade
<a href="{{ route('admin.activity.index') }}" class="{{ request()->routeIs('admin.activity.*') ? 'active' : '' }}">
    <i class="fa-solid fa-clock-rotate-left"></i> {{ __('admin.nav.activity') }}
</a>
```

Add `'activity' => 'Журнал действий'` under `nav`.

---

## Task 4.9: Activity log inline panel on news edit

**Files:**
- Modify: `resources/views/livewire/admin/news/form.blade.php`

- [ ] **Step 1: Add a sidebar card on the edit screen**

Inside the right column, after the existing "Tags" card and only when `$news?->exists`:

```blade
@if ($news?->exists)
    <div class="card mt-3">
        <div class="card-header">{{ __('admin.activity.title_section') }}</div>
        <div class="card-body" style="max-height: 300px; overflow-y: auto;">
            @forelse ($news->activity()->with('user')->latest()->limit(10)->get() as $a)
                <div class="d-flex gap-2 align-items-start mb-2 pb-2 border-bottom">
                    <i class="fa-solid fa-clock-rotate-left text-muted small mt-1"></i>
                    <div class="flex-grow-1">
                        <div class="small">
                            <strong>{{ $a->user->name ?? __('admin.activity.system') }}</strong>
                            {{ __('admin.activity.action_'.$a->action) }}
                        </div>
                        <div class="text-muted small">{{ $a->created_at->diffForHumans() }}</div>
                    </div>
                </div>
            @empty
                <div class="text-muted small">{{ __('admin.activity.no_activity') }}</div>
            @endforelse
        </div>
    </div>
@endif
```

Add to `App\Models\News`:

```php
public function activity()
{
    return $this->morphMany(\App\Models\Activity::class, 'subject', 'subject_type', 'subject_id');
}
```

---

## Task 4.10: Test activity log

**Files:**
- Create: `tests/Feature/Admin/ActivityLogTest.php`

```php
<?php

use App\Livewire\Admin\News\NewsForm;
use App\Livewire\Admin\News\NewsIndex;
use App\Models\Activity;
use App\Models\News;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

describe('Activity log', function () {
    it('records a row when news is created', function () {
        Livewire::test(NewsForm::class)
            ->set('slug', 'logged')
            ->set('translations.kr.title', 't')
            ->set('translations.kr.short_description', 's')
            ->set('translations.kr.content', '<p>c</p>')
            ->call('save');

        $log = Activity::where('subject_type', News::class)->latest()->first();
        expect($log)->not->toBeNull()->and($log->action)->toBe('created');
        expect($log->user_id)->toBe(auth()->id());
    })->group('feature', 'admin');

    it('records a row when news is deleted', function () {
        $n = News::factory()->create();
        Livewire::test(NewsIndex::class)->call('delete', $n->id);

        expect(Activity::where('action', 'deleted')->where('subject_id', $n->id)->exists())->toBeTrue();
    })->group('feature', 'admin');

    it('records published action on bulk publish', function () {
        $a = News::factory()->create(['status' => 'draft']);
        $b = News::factory()->create(['status' => 'draft']);

        Livewire::test(NewsIndex::class)
            ->set('selected', [$a->id, $b->id])
            ->call('bulkPublish');

        expect(Activity::where('action', 'published')->count())->toBe(2);
    })->group('feature', 'admin');

    it('does not store password fields in changes', function () {
        $other = User::factory()->create();
        \App\Livewire\Admin\Users\UserIndex::class; // ensure class loaded

        Livewire::test(\App\Livewire\Admin\Users\UserIndex::class)
            ->call('resetPassword', $other->id);

        $log = Activity::where('action', 'reset_password')->where('subject_id', $other->id)->first();
        expect($log)->not->toBeNull();
        $changes = $log->changes ?? [];
        expect($changes)->not->toHaveKey('password');
    })->group('feature', 'admin');
});
```

Run: `php artisan test tests/Feature/Admin/ActivityLogTest.php`
Expected: 4 tests pass.

---

## Task 4.11: Phase 4 final test pass + commit

- [ ] **Step 1: Pint + tests**

Run: `vendor/bin/pint --dirty --format agent`
Run: `php artisan test --compact`
Expected: all tests pass (228+ from Phase 3 + 14 new = 242+).

- [ ] **Step 2: Commit**

```bash
git add app/ database/migrations/ resources/views/ routes/web.php lang/ tests/Feature/Admin/

git commit -m "$(cat <<'EOF'
feat(admin): bulk actions, media library, activity log (Phase 4)

- Bulk publish/unpublish/delete on news with checkbox selection
- Filesystem-backed media library + image picker integrated with TinyMCE
- Activity log model + trait wired into all admin CRUD operations
- Activity index page + per-article activity panel on news edit

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

---

## Final acceptance

After all four phases:
- 242+ Pest tests passing
- Admin panel fully translated to Russian (with empty stubs for en/uz/kr ready for translators)
- Users / Pages / Videos / Activity / Media — full CRUD
- Bulk actions on news
- Auto-slug, drag-drop, media picker, styled confirm modal
- Activity log captures who-did-what
