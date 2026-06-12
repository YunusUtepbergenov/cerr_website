# Admin Panel Redesign Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Refactor the admin panel's inline CSS/JS into Vite-compiled assets and Blade components, add a light/dark theme, polish the dashboard/news/forms visuals, and fix six observed defects.

**Architecture:** The admin layout (`resources/views/components/layouts/admin.blade.php`) currently inlines ~230 lines of CSS and a sidebar script. We move them to `resources/css/admin.css` + `resources/js/admin.js` (new Vite inputs), introduce theme tokens overridden by a `[data-admin-theme="dark"]` block, and extract five Blade components (`page-header`, `stat-card`, `status-pill`, `lang-chips`, `empty-state`) used across the 9 admin Livewire views.

**Tech Stack:** Laravel 12, Livewire (class components), Bootstrap 5 (static file in `public/css/vendor/`), Vite (laravel-vite-plugin), Pest 3.

**Spec:** `docs/superpowers/specs/2026-06-12-admin-redesign-design.md`

**Conventions that apply to every task:**
- Run tests with `php artisan test --compact --filter=<name>`.
- After modifying any PHP file, run `vendor/bin/pint --dirty --format agent` before committing.
- Pest tests in `tests/Feature` automatically use `RefreshDatabase` (see `tests/Pest.php`).
- The admin UI is Russian-only in practice: `lang/ru/admin.php` has real values; `lang/en|uz|kr/admin.php` mirror the same keys with `''` values. New keys must be added to all four files (real text in `ru`, `''` elsewhere).
- Commit only the files each task names — the working tree has unrelated uncommitted changes (`composer.lock`, `package.json`, a migration) that must NOT be staged.

---

### Task 1: Extract admin CSS/JS into Vite assets

**Files:**
- Create: `resources/css/admin.css`
- Create: `resources/js/admin.js`
- Modify: `vite.config.js`
- Modify: `resources/views/components/layouts/admin.blade.php`
- Test: `tests/Feature/Admin/AdminLayoutTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/AdminLayoutTest.php`:

```php
<?php

use App\Models\User;

describe('Admin layout assets', function () {
    it('serves admin styles via vite instead of an inline style block', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        // The shell markup is still there…
        $response->assertSee('admin-shell', false);
        // …but the big inline stylesheet is gone (this selector only existed inline).
        $response->assertDontSee('.admin-sidebar {', false);
        // And the sidebar toggle script moved out too.
        $response->assertDontSee("localStorage.getItem(STORAGE_KEY)", false);
    })->group('feature', 'admin');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=AdminLayoutTest`
Expected: FAIL on `assertDontSee('.admin-sidebar {')` (inline CSS still present).

- [ ] **Step 3: Create `resources/css/admin.css`**

Move the entire contents of the `<style>` block in `resources/views/components/layouts/admin.blade.php` (everything between `<style>` on line 13 and `</style>` on line 241 — from `:root {` through the closing `}` of the `@media (max-width: 991px)` block) **verbatim** into `resources/css/admin.css`. No edits yet; later tasks modify this file.

- [ ] **Step 4: Create `resources/js/admin.js`**

Move the sidebar IIFE from the layout's bottom `<script>` block (lines 348–397, the `(function () { const STORAGE_KEY = 'cerr-admin-sidebar-collapsed'; … })();` block) verbatim into `resources/js/admin.js`.

- [ ] **Step 5: Register the new Vite inputs**

In `vite.config.js`, change the `input` array to:

```js
input: [
    'resources/css/app.css',
    'resources/css/admin.css',
    'resources/js/app.js',
    'resources/js/admin.js',
],
```

- [ ] **Step 6: Update the admin layout**

In `resources/views/components/layouts/admin.blade.php`:

1. Replace the entire `<style>…</style>` block with:

```blade
    @vite(['resources/css/admin.css'])
```

(keep the `@stack('styles')` line that follows).

2. Replace the bottom inline `<script>(function () { const STORAGE_KEY = ……})();</script>` block with:

```blade
    @vite(['resources/js/admin.js'])
```

(keep `@livewireScripts`, the jQuery/Bootstrap script tags, and `@stack('scripts')` in their current order; `@vite` for the JS goes where the inline script was, i.e. after `@livewireScripts`).

- [ ] **Step 7: Build and run the test**

Run: `npm run build`
Expected: build succeeds, manifest includes `resources/css/admin.css` and `resources/js/admin.js`.

Run: `php artisan test --compact --filter=AdminLayoutTest`
Expected: PASS

Run: `php artisan test --compact --filter=AdminAccessTest`
Expected: PASS (regression check)

- [ ] **Step 8: Commit**

```bash
git add resources/css/admin.css resources/js/admin.js vite.config.js resources/views/components/layouts/admin.blade.php tests/Feature/Admin/AdminLayoutTest.php
git commit -m "refactor(admin): move inline layout CSS/JS into Vite-compiled assets"
```

---

### Task 2: Theme tokens + dark mode

**Files:**
- Modify: `resources/css/admin.css`
- Modify: `resources/js/admin.js`
- Modify: `resources/views/components/layouts/admin.blade.php`
- Modify: `lang/ru/admin.php`, `lang/en/admin.php`, `lang/uz/admin.php`, `lang/kr/admin.php`
- Test: `tests/Feature/Admin/AdminLayoutTest.php`

- [ ] **Step 1: Write the failing test**

Append to the `describe` block in `tests/Feature/Admin/AdminLayoutTest.php`:

```php
    it('renders the theme toggle and pre-paint theme snippet', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertSee('id="theme-toggle"', false);
        $response->assertSee('cerr-admin-theme', false);
        $response->assertSee('data-admin-theme', false);
    })->group('feature', 'admin');
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=AdminLayoutTest`
Expected: FAIL (`id="theme-toggle"` not found).

- [ ] **Step 3: Add new tokens and replace hard-coded colors in `resources/css/admin.css`**

Add these four lines inside the existing `:root { … }` block, after `--radius-lg: 10px;`:

```css
    --admin-hover: rgba(0, 0, 0, .04);
    --admin-chip-bg: #f5f5f5;
    --admin-topbar-bg: rgba(255, 255, 255, .8);
    --admin-shadow: 0 1px 2px rgba(16, 24, 40, .04);
```

Then make these exact replacements throughout the file:

| Selector | Old | New |
|---|---|---|
| `.admin-sidebar a:hover` | `background: rgba(0, 0, 0, .04);` | `background: var(--admin-hover);` |
| `.admin-topbar` | `background: rgba(255, 255, 255, .8);` | `background: var(--admin-topbar-bg);` |
| `.admin-topbar .icon-btn:hover` | `background: rgba(0, 0, 0, .04);` | `background: var(--admin-hover);` |
| `.card` | `box-shadow: none;` | `box-shadow: var(--admin-shadow);` |
| `.btn-outline-secondary:hover` | `background: rgba(0, 0, 0, .04);` | `background: var(--admin-hover);` |
| `.btn-light` | `background: rgba(255,255,255,.9);` | `background: var(--admin-surface);` |
| `.pill.status-draft` | `background: #f5f5f5;` | `background: var(--admin-chip-bg);` |
| `.lang-chip` | `background: #f5f5f5;` | `background: var(--admin-chip-bg);` |
| `.pagination .page-link:hover` | `background: rgba(0, 0, 0, .04);` | `background: var(--admin-hover);` |
| `.pagination .page-item.active .page-link` | `color: #fff;` | `color: var(--admin-bg);` |
| `.sticky-action-bar` | `background: rgba(255,255,255,.92);` | `background: var(--admin-topbar-bg);` |

- [ ] **Step 4: Append the dark theme + toggle styles to `resources/css/admin.css`**

```css
/* ---------- Dark theme ---------- */
[data-admin-theme="dark"] {
    --admin-bg: #111113;
    --admin-surface: #18181b;
    --admin-surface-soft: #141416;
    --admin-border: #27272a;
    --admin-border-soft: #1f1f23;
    --admin-text: #f4f4f5;
    --admin-text-muted: #a1a1aa;
    --admin-text-faint: #71717a;
    --admin-primary: #7c89e8;
    --admin-primary-hover: #8e9af0;
    --admin-primary-soft: rgba(124, 137, 232, .14);
    --admin-success: #4ade80;
    --admin-success-soft: rgba(74, 222, 128, .12);
    --admin-danger: #f87171;
    --admin-danger-soft: rgba(248, 113, 113, .12);
    --admin-warning: #fbbf24;
    --admin-warning-soft: rgba(251, 191, 36, .12);
    --admin-hover: rgba(255, 255, 255, .06);
    --admin-chip-bg: #27272a;
    --admin-topbar-bg: rgba(24, 24, 27, .8);
    --admin-shadow: 0 1px 2px rgba(0, 0, 0, .4);
}
[data-admin-theme="dark"] .btn-primary { color: #fff; }
[data-admin-theme="dark"] .toast-item { box-shadow: 0 4px 24px rgba(0, 0, 0, .5); }
[data-admin-theme="dark"] input[type="datetime-local"]::-webkit-calendar-picker-indicator { filter: invert(1); }
[data-admin-theme="dark"] .admin-sidebar { box-shadow: none; }

#theme-toggle .theme-icon-light { display: none; }
[data-admin-theme="dark"] #theme-toggle .theme-icon-dark { display: none; }
[data-admin-theme="dark"] #theme-toggle .theme-icon-light { display: inline-block; }
```

- [ ] **Step 5: Add the pre-paint snippet and toggle button to the layout**

In `resources/views/components/layouts/admin.blade.php`:

1. The opening tag currently reads `<html lang="{{ app()->getLocale() }}" data-bs-theme="light">` — leave it, and add this snippet immediately after `<meta name="viewport" …>` in `<head>` (before any stylesheet links, so the theme applies pre-paint):

```blade
    <script>
        (function () {
            try {
                var t = localStorage.getItem('cerr-admin-theme')
                    || (window.matchMedia('(prefers-color-scheme: dark)').matches ? 'dark' : 'light');
                document.documentElement.setAttribute('data-admin-theme', t);
                document.documentElement.setAttribute('data-bs-theme', t);
            } catch (e) {}
        })();
    </script>
```

2. In the topbar, immediately before the "view site" link (`<a href="{{ route('home') }}" target="_blank" class="icon-btn" …>`), add:

```blade
                    <button type="button" class="icon-btn" id="theme-toggle" title="{{ __('admin.nav.toggle_theme') }}">
                        <i class="fa-solid fa-moon theme-icon-dark"></i>
                        <i class="fa-solid fa-sun theme-icon-light"></i>
                    </button>
```

- [ ] **Step 6: Add the toggle handler to `resources/js/admin.js`**

Append inside the file (as a separate IIFE after the sidebar one):

```js
(function () {
    const setup = () => {
        const btn = document.getElementById('theme-toggle');
        if (!btn || btn.dataset.bound === '1') return;
        btn.dataset.bound = '1';
        btn.addEventListener('click', () => {
            const next = document.documentElement.getAttribute('data-admin-theme') === 'dark' ? 'light' : 'dark';
            document.documentElement.setAttribute('data-admin-theme', next);
            document.documentElement.setAttribute('data-bs-theme', next);
            try { localStorage.setItem('cerr-admin-theme', next); } catch (e) {}
        });
    };
    if (document.readyState !== 'loading') setup();
    else document.addEventListener('DOMContentLoaded', setup);
    document.addEventListener('livewire:navigated', setup);
})();
```

- [ ] **Step 7: Add the lang key**

In `lang/ru/admin.php` `'nav'` array, after `'sign_out' => 'Выйти',`:

```php
        'toggle_theme' => 'Переключить тему',
```

In `lang/en/admin.php`, `lang/uz/admin.php`, `lang/kr/admin.php` `'nav'` arrays, same position:

```php
        'toggle_theme' => '',
```

- [ ] **Step 8: Build and run tests**

Run: `npm run build` — expected: succeeds.
Run: `php artisan test --compact --filter=AdminLayoutTest` — expected: PASS.
Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 9: Commit**

```bash
git add resources/css/admin.css resources/js/admin.js resources/views/components/layouts/admin.blade.php lang/ru/admin.php lang/en/admin.php lang/uz/admin.php lang/kr/admin.php tests/Feature/Admin/AdminLayoutTest.php
git commit -m "feat(admin): add dark mode with persisted theme toggle"
```

---

### Task 3: Blade components (status-pill, lang-chips, stat-card, page-header, empty-state)

**Files:**
- Create: `resources/views/components/admin/status-pill.blade.php`
- Create: `resources/views/components/admin/lang-chips.blade.php`
- Create: `resources/views/components/admin/stat-card.blade.php`
- Create: `resources/views/components/admin/page-header.blade.php`
- Create: `resources/views/components/admin/empty-state.blade.php`
- Modify: `resources/css/admin.css`
- Test: `tests/Feature/Admin/AdminComponentsTest.php`

- [ ] **Step 1: Write the failing tests**

Create `tests/Feature/Admin/AdminComponentsTest.php`:

```php
<?php

describe('Admin Blade components', function () {
    beforeEach(function () {
        app()->setLocale('ru');
    });

    it('renders a translated status pill', function () {
        $html = $this->blade('<x-admin.status-pill status="published" />');

        $html->assertSee('Опубликовано');
        $html->assertSee('status-published', false);
    });

    it('falls back to the raw value for unknown statuses', function () {
        $html = $this->blade('<x-admin.status-pill status="bogus" />');

        $html->assertSee('bogus');
        $html->assertSee('status-bogus', false);
    });

    it('renders lang chips marking missing locales', function () {
        $html = $this->blade('<x-admin.lang-chips :available="[\'ru\', \'uz\']" />');

        $html->assertSeeInOrder(['kr', 'uz', 'ru', 'en']);
        $html->assertSee('lang-chip missing', false);
    });

    it('renders a stat card with accent', function () {
        $html = $this->blade('<x-admin.stat-card label="Черновики" value="3" icon="fa-regular fa-pen-to-square" accent="warning" />');

        $html->assertSee('Черновики');
        $html->assertSee('3');
        $html->assertSee('accent-warning', false);
    });

    it('renders a page header with actions slot', function () {
        $html = $this->blade('<x-admin.page-header title="Новости" subtitle="Управление"><button>Создать</button></x-admin.page-header>');

        $html->assertSee('Новости');
        $html->assertSee('Управление');
        $html->assertSee('Создать');
    });

    it('renders an empty state', function () {
        $html = $this->blade('<x-admin.empty-state icon="fa-regular fa-newspaper" title="Пусто" />');

        $html->assertSee('Пусто');
        $html->assertSee('fa-regular fa-newspaper', false);
    });
});
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --compact --filter=AdminComponentsTest`
Expected: FAIL — `Unable to locate a class or view for component [admin.status-pill]`.

- [ ] **Step 3: Create the components**

`resources/views/components/admin/status-pill.blade.php`:

```blade
@props(['status'])

@php
    $labelKey = 'admin.news.status_short_'.$status;
    $label = \Illuminate\Support\Facades\Lang::has($labelKey) ? __($labelKey) : $status;
@endphp

<span {{ $attributes->merge(['class' => 'pill status-'.$status]) }}>{{ $label }}</span>
```

`resources/views/components/admin/lang-chips.blade.php`:

```blade
@props(['available' => []])

<span {{ $attributes->merge(['class' => 'lang-chips']) }}>
    @foreach (['kr', 'uz', 'ru', 'en'] as $locale)
        <span class="lang-chip{{ in_array($locale, $available, true) ? '' : ' missing' }}">{{ $locale }}</span>
    @endforeach
</span>
```

`resources/views/components/admin/stat-card.blade.php`:

```blade
@props(['label', 'value', 'icon' => null, 'accent' => null])

<div {{ $attributes->merge(['class' => 'card stat-card'.($accent ? ' accent-'.$accent : '')]) }}>
    <div class="label">
        @if ($icon)<i class="{{ $icon }} me-1"></i>@endif
        {{ $label }}
    </div>
    <div class="value">{{ $value }}</div>
</div>
```

`resources/views/components/admin/page-header.blade.php`:

```blade
@props(['title', 'subtitle' => null])

<div {{ $attributes->merge(['class' => 'page-header']) }}>
    <div>
        <h1>{{ $title }}</h1>
        @if ($subtitle)
            <div class="subtitle">{{ $subtitle }}</div>
        @endif
    </div>
    @if (trim($slot) !== '')
        <div class="d-flex align-items-center gap-2 flex-wrap">{{ $slot }}</div>
    @endif
</div>
```

`resources/views/components/admin/empty-state.blade.php`:

```blade
@props(['icon' => 'fa-regular fa-folder-open', 'title'])

<div {{ $attributes->merge(['class' => 'empty-state']) }}>
    <i class="{{ $icon }} d-block"></i>
    <div class="fw-semibold">{{ $title }}</div>
    @if (trim($slot) !== '')
        <div class="small mt-1">{{ $slot }}</div>
    @endif
</div>
```

- [ ] **Step 4: Add supporting CSS to `resources/css/admin.css`**

Append:

```css
/* ---------- Components ---------- */
.lang-chips { display: inline-flex; gap: .15rem; white-space: nowrap; }
.lang-chips .lang-chip { margin-right: 0; }

.stat-card .label i { color: var(--admin-text-faint); }
.stat-card.accent-success .value { color: var(--admin-success); }
.stat-card.accent-warning .value { color: var(--admin-warning); }
.stat-card.accent-primary .value { color: var(--admin-primary); }

.cat-badge {
    display: inline-flex; padding: .2rem .55rem; font-size: .78rem; font-weight: 500;
    border-radius: var(--radius-sm); background: var(--admin-primary-soft); color: var(--admin-primary);
    max-width: 150px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
}
```

Also shrink the existing `.lang-chip` so four chips fit on one line — in the existing `.lang-chip` rule, change `padding: .12rem .42rem;` to `padding: .1rem .3rem;` and `font-size: .72rem;` to `font-size: .68rem;`.

- [ ] **Step 5: Run tests**

Run: `php artisan test --compact --filter=AdminComponentsTest`
Expected: PASS (all 6).

Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 6: Commit**

```bash
git add resources/views/components/admin/ resources/css/admin.css tests/Feature/Admin/AdminComponentsTest.php
git commit -m "feat(admin): add reusable Blade components for admin UI"
```

---

### Task 4: Dashboard upgrade

**Files:**
- Modify: `app/Livewire/Admin/Dashboard.php`
- Modify: `resources/views/livewire/admin/dashboard.blade.php`
- Modify: `lang/ru/admin.php`, `lang/en/admin.php`, `lang/uz/admin.php`, `lang/kr/admin.php`
- Test: `tests/Feature/Admin/AdminDashboardTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/AdminDashboardTest.php`:

```php
<?php

use App\Models\Activity;
use App\Models\User;

describe('Admin dashboard', function () {
    it('shows translated status pills and split stat cards', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        createNewsWithTranslation(['status' => 'published']);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        // Translated pill, not the raw enum value.
        $response->assertSee('status-published', false);
        $response->assertDontSee('>published<', false);
        // Categories and tags are separate cards now.
        $response->assertDontSee(__('admin.dashboard.categories_tags'));
        $response->assertSee(__('admin.dashboard.categories'));
        $response->assertSee(__('admin.dashboard.tags'));
        // Quick actions present.
        $response->assertSee(route('admin.news.create'));
    })->group('feature', 'admin');

    it('shows recent activity to admins', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $news = createNewsWithTranslation();
        Activity::create([
            'user_id' => $admin->id,
            'subject_type' => \App\Models\News::class,
            'subject_id' => $news->id,
            'action' => 'created',
            'created_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertSee(__('admin.dashboard.recent_activity'));
        $response->assertSee(__('admin.activity.action_created'));
    })->group('feature', 'admin');

    it('falls back to created_at when updated_at is missing', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $news = createNewsWithTranslation();
        $news->timestamps = false;
        $news->forceFill(['updated_at' => null, 'created_at' => now()->subHours(3)])->saveQuietly();

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        $response->assertSee(now()->subHours(3)->diffForHumans());
    })->group('feature', 'admin');
});
```

Note: if `createNewsWithTranslation` creates news whose factory leaves `updated_at` set, the third test's `saveQuietly` with `timestamps = false` clears it. If the `news` table has a NOT NULL constraint on `updated_at`, change the test to assert the em-dash fallback instead — check the actual schema before adjusting.

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=AdminDashboardTest`
Expected: FAIL (categories_tags combined card still rendered; recent_activity key missing).

- [ ] **Step 3: Add lang keys**

In `lang/ru/admin.php` `'dashboard'` array, replace `'categories_tags' => 'Категории / Теги',` with:

```php
        'categories' => 'Категории',
        'tags' => 'Теги',
        'quick_actions' => 'Быстрые действия',
        'upload_media' => 'Загрузить изображения',
        'recent_activity' => 'Последние действия',
        'all_activity' => 'Весь журнал',
```

(Keep `'categories_tags'` removed — the test asserts it is gone from the page; deleting the key entirely is correct since this was its only usage. Apply the same key changes with `''` values in `lang/en/admin.php`, `lang/uz/admin.php`, `lang/kr/admin.php`.)

- [ ] **Step 4: Update `app/Livewire/Admin/Dashboard.php`**

Replace the class with:

```php
<?php

namespace App\Livewire\Admin;

use App\Models\Activity;
use App\Models\Category;
use App\Models\News;
use App\Models\Tag;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.admin')]
class Dashboard extends Component
{
    public function render()
    {
        return view('livewire.admin.dashboard', [
            'newsCount' => News::count(),
            'publishedCount' => News::where('status', 'published')->count(),
            'draftCount' => News::where('status', 'draft')->count(),
            'categoryCount' => Category::count(),
            'tagCount' => Tag::count(),
            'recentNews' => News::with('translations')->latest()->limit(8)->get(),
            'recentActivity' => auth()->user()->canViewActivity()
                ? Activity::with('user')->latest('created_at')->limit(8)->get()
                : collect(),
        ])->title(__('admin.nav.dashboard'));
    }
}
```

(Note the `#[Title('Dashboard')]` attribute and its import are removed; the translated title is set on the view.)

- [ ] **Step 5: Rewrite `resources/views/livewire/admin/dashboard.blade.php`**

```blade
<div>
    <x-admin.page-header :title="__('admin.dashboard.title')" :subtitle="__('admin.dashboard.welcome', ['name' => explode(' ', auth()->user()->name)[0]])">
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i> {{ __('admin.news.new_article') }}
        </a>
        <a href="{{ route('admin.media.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-photo-film me-1"></i> {{ __('admin.dashboard.upload_media') }}
        </a>
    </x-admin.page-header>

    <div class="row g-3 mb-4 row-cols-2 row-cols-md-3 row-cols-xl-5">
        <div class="col">
            <x-admin.stat-card :label="__('admin.dashboard.total_news')" :value="number_format($newsCount)" icon="fa-solid fa-newspaper" />
        </div>
        <div class="col">
            <x-admin.stat-card :label="__('admin.dashboard.published')" :value="number_format($publishedCount)" icon="fa-solid fa-circle-check" accent="success" />
        </div>
        <div class="col">
            <x-admin.stat-card :label="__('admin.dashboard.drafts')" :value="number_format($draftCount)" icon="fa-regular fa-pen-to-square" accent="warning" />
        </div>
        <div class="col">
            <x-admin.stat-card :label="__('admin.dashboard.categories')" :value="$categoryCount" icon="fa-solid fa-folder-open" />
        </div>
        <div class="col">
            <x-admin.stat-card :label="__('admin.dashboard.tags')" :value="$tagCount" icon="fa-solid fa-tags" />
        </div>
    </div>

    <div class="card">
        <div class="card-header d-flex justify-content-between align-items-center">
            <span>{{ __('admin.dashboard.recent_news') }}</span>
            <a href="{{ route('admin.news.index') }}" class="btn btn-sm btn-outline-primary">
                {{ __('admin.dashboard.all_news') }} <i class="fa-solid fa-arrow-right ms-1"></i>
            </a>
        </div>
        <div class="table-responsive">
            <table class="table mb-0">
                <thead>
                    <tr>
                        <th style="width: 60px;">ID</th>
                        <th>{{ __('admin.news.title') }}</th>
                        <th style="width: 140px;">{{ __('admin.common.status') }}</th>
                        <th style="width: 150px;">{{ __('admin.common.languages') }}</th>
                        <th style="width: 140px;">{{ __('admin.common.updated') }}</th>
                        <th style="width: 80px;"></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($recentNews as $item)
                        <tr>
                            <td class="text-muted small">#{{ $item->id }}</td>
                            <td><div class="fw-semibold">{{ optional($item->translations->first())->title ?? '—' }}</div></td>
                            <td><x-admin.status-pill :status="$item->status" /></td>
                            <td><x-admin.lang-chips :available="$item->translations->pluck('lang')->all()" /></td>
                            <td><span class="text-muted small">{{ ($item->updated_at ?? $item->created_at)?->diffForHumans() ?? '—' }}</span></td>
                            <td class="text-end">
                                <a href="{{ route('admin.news.edit', $item) }}" class="btn btn-sm btn-outline-primary" title="{{ __('admin.common.edit') }}"><i class="fa-solid fa-pen"></i></a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6">
                                <x-admin.empty-state icon="fa-regular fa-newspaper" :title="__('admin.dashboard.no_news')">
                                    <a href="{{ route('admin.news.create') }}">{{ __('admin.dashboard.create_first') }}</a>.
                                </x-admin.empty-state>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @if (auth()->user()->canViewActivity())
        <div class="card mt-4">
            <div class="card-header d-flex justify-content-between align-items-center">
                <span>{{ __('admin.dashboard.recent_activity') }}</span>
                <a href="{{ route('admin.activity.index') }}" class="btn btn-sm btn-outline-primary">
                    {{ __('admin.dashboard.all_activity') }} <i class="fa-solid fa-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body py-2">
                @forelse ($recentActivity as $a)
                    <div class="activity-row d-flex gap-2 align-items-start py-2">
                        <i class="fa-solid fa-clock-rotate-left text-muted small mt-1"></i>
                        <div class="flex-grow-1">
                            <span class="small">
                                <strong>{{ $a->user->name ?? __('admin.activity.system') }}</strong>
                                {{ __('admin.activity.action_'.$a->action) }}
                                <span class="text-muted">{{ class_basename($a->subject_type) }} #{{ $a->subject_id }}</span>
                            </span>
                        </div>
                        <span class="text-muted small">{{ $a->created_at->diffForHumans() }}</span>
                    </div>
                @empty
                    <x-admin.empty-state icon="fa-regular fa-clock" :title="__('admin.activity.no_activity')" class="py-3" />
                @endforelse
            </div>
        </div>
    @endif
</div>
```

- [ ] **Step 6: Add the activity-row divider style to `resources/css/admin.css`**

Append:

```css
.activity-row { border-bottom: 1px solid var(--admin-border-soft); }
.activity-row:last-child { border-bottom: none; }
```

- [ ] **Step 7: Run tests**

Run: `php artisan test --compact --filter=AdminDashboardTest` — expected: PASS.
Run: `php artisan test --compact tests/Feature/Admin` — expected: PASS (regression).
Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 8: Commit**

```bash
git add app/Livewire/Admin/Dashboard.php resources/views/livewire/admin/dashboard.blade.php resources/css/admin.css lang/ tests/Feature/Admin/AdminDashboardTest.php
git commit -m "feat(admin): richer dashboard with quick actions and activity feed"
```

---

### Task 5: News index polish

**Files:**
- Modify: `resources/views/livewire/admin/news/index.blade.php`
- Modify: `app/Livewire/Admin/News/NewsIndex.php`
- Modify: `resources/css/admin.css`
- Test: `tests/Feature/Admin/NewsIndexUiTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/NewsIndexUiTest.php`:

```php
<?php

use App\Models\User;

describe('News index UI', function () {
    it('shows translated status pills and the translated page title', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        createNewsWithTranslation(['status' => 'draft']);

        $response = $this->actingAs($admin)->get(route('admin.news.index'));

        $response->assertOk();
        $response->assertSee('status-draft', false);
        $response->assertDontSee('>draft<', false);
        // Breadcrumb/title no longer the raw English class title.
        $response->assertDontSee('News</title>', false);
        $response->assertSee(__('admin.news.title_section').' — CERR Admin</title>', false);
    })->group('feature', 'admin');

    it('renders the category as a badge when present', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        app()->setLocale('ru');
        $category = createCategoryWithTranslation();
        createNewsWithTranslation(['category_id' => $category->id]);

        $response = $this->actingAs($admin)->get(route('admin.news.index'));

        $response->assertOk();
        $response->assertSee('cat-badge', false);
    })->group('feature', 'admin');
});
```

(Both helpers are defined in `tests/Pest.php`; the category translation is created for the current app locale, which is why `app()->setLocale('ru')` runs first.)

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=NewsIndexUiTest`
Expected: FAIL (raw `>draft<` pill text present; `cat-badge` missing).

- [ ] **Step 3: Update `app/Livewire/Admin/News/NewsIndex.php`**

Remove the `#[Title('News')]` attribute (and its import if now unused) and chain the title onto the view returned by `render()`:

```php
        return view('livewire.admin.news.index', [
            // …existing data unchanged…
        ])->title(__('admin.news.title_section'));
```

- [ ] **Step 4: Update `resources/views/livewire/admin/news/index.blade.php`**

1. Replace the page-header block (lines 2–10) with:

```blade
    <x-admin.page-header :title="__('admin.news.title_section')" :subtitle="__('admin.news.subtitle')">
        <a href="{{ route('admin.news.create') }}" class="btn btn-primary">
            <i class="fa-solid fa-plus me-1"></i> {{ __('admin.news.new_article') }}
        </a>
    </x-admin.page-header>
```

2. In the `@php` block inside the row loop, change the `$catName` fallback from `?? '—'` to `?? null`.

3. Replace the thumbnail placeholder `<div style="width: 44px; … background: #f1f5f9; … color: #cbd5e1;">` line with:

```blade
                                    <div class="thumb-placeholder"><i class="fa-regular fa-image"></i></div>
```

4. Replace the status cell `<td><span class="pill status-{{ $item->status }}">{{ str_replace('_', ' ', $item->status) }}</span></td>` with:

```blade
                            <td><x-admin.status-pill :status="$item->status" /></td>
```

5. Replace the languages cell (the `@foreach (['kr', 'uz', 'ru', 'en'] …` block) with:

```blade
                            <td><x-admin.lang-chips :available="$availableLocales" /></td>
```

6. Replace the category cell `<td><span class="text-truncate d-inline-block" style="max-width: 140px;">{{ $catName }}</span></td>` with:

```blade
                            <td>
                                @if ($catName)
                                    <span class="cat-badge" title="{{ $catName }}">{{ $catName }}</span>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
```

7. In the updated cell, change `{{ $item->updated_at?->diffForHumans() }}` to `{{ ($item->updated_at ?? $item->created_at)?->diffForHumans() ?? '—' }}`.

8. Replace the empty-state block (`<div class="empty-state">…</div>`) with:

```blade
                                <x-admin.empty-state icon="fa-regular fa-newspaper" :title="__('admin.news.no_news_match')">
                                    {{ __('admin.news.try_clear_filters') }} <a href="{{ route('admin.news.create') }}">{{ __('admin.news.create_new_article') }}</a>.
                                </x-admin.empty-state>
```

- [ ] **Step 5: Add density + placeholder styles to `resources/css/admin.css`**

In the existing rule `.table > :not(caption) > * > *`, change `padding: .75rem 1rem;` to `padding: .6rem 1rem;`. Then append:

```css
.thumb-placeholder {
    width: 44px; height: 44px; border-radius: 6px; background: var(--admin-chip-bg);
    display: flex; align-items: center; justify-content: center; color: var(--admin-text-faint);
}
```

- [ ] **Step 6: Run tests**

Run: `php artisan test --compact --filter=NewsIndexUiTest` — expected: PASS.
Run: `php artisan test --compact --filter=NewsCrudTest` — expected: PASS (regression).
Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 7: Commit**

```bash
git add resources/views/livewire/admin/news/index.blade.php app/Livewire/Admin/News/NewsIndex.php resources/css/admin.css tests/Feature/Admin/NewsIndexUiTest.php
git commit -m "feat(admin): compact news index with components and category badges"
```

---

### Task 6: News form polish (tag chips, styled controls, translated title)

**Files:**
- Modify: `resources/views/livewire/admin/news/form.blade.php`
- Modify: `app/Livewire/Admin/News/NewsForm.php`
- Modify: `resources/css/admin.css`
- Test: `tests/Feature/Admin/NewsFormUiTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/NewsFormUiTest.php`:

```php
<?php

use App\Models\User;

describe('News form UI', function () {
    it('uses a translated page title on create and edit', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.news.create'))
            ->assertOk()
            ->assertDontSee('News form</title>', false)
            ->assertSee(__('admin.news.new_article').' — CERR Admin</title>', false);

        $news = createNewsWithTranslation();
        $this->actingAs($admin)->get(route('admin.news.edit', $news))
            ->assertOk()
            ->assertSee(__('admin.news.edit_article').' — CERR Admin</title>', false);
    })->group('feature', 'admin');

    it('renders tags as toggle chips', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        \App\Models\Tag::factory()->create(['name' => 'economy']);

        $this->actingAs($admin)->get(route('admin.news.create'))
            ->assertOk()
            ->assertSee('tag-chip-list', false);
    })->group('feature', 'admin');
});
```

(If `Tag::factory()` lacks a `name` column default, adjust per `database/factories/TagFactory.php`.)

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=NewsFormUiTest`
Expected: FAIL (`News form</title>` still present, `tag-chip-list` missing).

- [ ] **Step 3: Update `app/Livewire/Admin/News/NewsForm.php`**

Remove `#[Title('News form')]` and chain onto the view in `render()`:

```php
        ->title($this->news?->exists ? __('admin.news.edit_article') : __('admin.news.new_article'));
```

- [ ] **Step 4: Update `resources/views/livewire/admin/news/form.blade.php`**

1. Replace the page-header block (lines 4–18) with:

```blade
    <x-admin.page-header
        :title="$news?->exists ? __('admin.news.edit_article') : __('admin.news.new_article')"
        :subtitle="$news?->exists
            ? '#'.$news->id.' · '.__('admin.news.last_saved', ['time' => $news->updated_at?->diffForHumans() ?? '—'])
            : __('admin.news.create_article')">
        <a href="{{ route('admin.news.index') }}" class="btn btn-outline-secondary">
            <i class="fa-solid fa-arrow-left me-1"></i> {{ __('admin.news.back_to_list') }}
        </a>
    </x-admin.page-header>
```

2. In the tags card, wrap the `@forelse ($allTags …)` list in a chip container — replace:

```blade
                    <div class="card-body" style="max-height: 280px; overflow-y: auto;">
                        @forelse ($allTags as $tag)
                            <div class="form-check">
```

with:

```blade
                    <div class="card-body" style="max-height: 280px; overflow-y: auto;">
                        <div class="tag-chip-list">
                        @forelse ($allTags as $tag)
                            <div class="form-check">
```

and close the wrapper after the `@endforelse` (before `</div>` of card-body):

```blade
                        @endforelse
                        </div>
```

3. Replace the cover preview wrapper's inline `background: #fafbfc;` (line ~115) with `background: var(--admin-surface-soft);`.

- [ ] **Step 5: Add control styles to `resources/css/admin.css`**

Append:

```css
/* ---------- Form controls ---------- */
.form-select {
    appearance: none;
    background-image: url("data:image/svg+xml;charset=utf-8,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23888' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat: no-repeat;
    background-position: right .7rem center;
    background-size: 14px;
}

input[type="datetime-local"].form-control { font-feature-settings: "tnum"; }

.tag-chip-list { display: flex; flex-wrap: wrap; gap: .35rem; }
.tag-chip-list .form-check { padding: 0; margin: 0; min-height: 0; }
.tag-chip-list .form-check-input { position: absolute; opacity: 0; pointer-events: none; }
.tag-chip-list .form-check-label {
    display: inline-flex; align-items: center; padding: .25rem .65rem;
    border: 1px solid var(--admin-border); border-radius: 999px;
    font-size: .82rem; cursor: pointer; color: var(--admin-text-muted);
    transition: background .1s, border-color .1s, color .1s;
}
.tag-chip-list .form-check-label:hover { border-color: var(--admin-primary); color: var(--admin-text); }
.tag-chip-list .form-check-input:checked + .form-check-label {
    background: var(--admin-primary-soft); border-color: var(--admin-primary); color: var(--admin-primary);
}
```

- [ ] **Step 6: Run tests**

Run: `php artisan test --compact --filter=NewsFormUiTest` — expected: PASS.
Run: `php artisan test --compact --filter="NewsCrudTest|AutoSlugTest|ImagePreviewTest"` — expected: PASS.
Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 7: Commit**

```bash
git add resources/views/livewire/admin/news/form.blade.php app/Livewire/Admin/News/NewsForm.php resources/css/admin.css tests/Feature/Admin/NewsFormUiTest.php
git commit -m "feat(admin): tag chips, styled controls, translated form titles"
```

---

### Task 7: Component sweep + translated titles for remaining pages

**Files:**
- Modify: `app/Livewire/Admin/Categories/CategoryIndex.php`, `app/Livewire/Admin/Tags/TagIndex.php`, `app/Livewire/Admin/Pages/PageIndex.php`, `app/Livewire/Admin/Pages/PageForm.php`, `app/Livewire/Admin/Videos/VideoIndex.php`, `app/Livewire/Admin/Users/UserIndex.php`, `app/Livewire/Admin/Media/MediaIndex.php`, `app/Livewire/Admin/Activity/ActivityIndex.php`
- Modify: `resources/views/livewire/admin/categories/index.blade.php`, `…/tags/index.blade.php`, `…/pages/index.blade.php`, `…/videos/index.blade.php`, `…/users/index.blade.php`, `…/media/index.blade.php`, `…/activity/index.blade.php`
- Test: `tests/Feature/Admin/AdminPageTitlesTest.php`

- [ ] **Step 1: Write the failing test**

Create `tests/Feature/Admin/AdminPageTitlesTest.php`:

```php
<?php

use App\Models\User;

describe('Admin page titles', function () {
    it('uses translated titles on every admin page', function (string $routeName, string $langKey) {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route($routeName))
            ->assertOk()
            ->assertSee(__($langKey).' — CERR Admin</title>', false);
    })->with([
        'categories' => ['admin.categories.index', 'admin.categories.title_section'],
        'tags' => ['admin.tags.index', 'admin.tags.title_section'],
        'pages' => ['admin.pages.index', 'admin.pages.title_section'],
        'videos' => ['admin.videos.index', 'admin.videos.title_section'],
        'users' => ['admin.users.index', 'admin.users.title_section'],
        'media' => ['admin.media.index', 'admin.media.title_section'],
        'activity' => ['admin.activity.index', 'admin.activity.title_section'],
    ])->group('feature', 'admin');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=AdminPageTitlesTest`
Expected: FAIL for at least `tags` (its title is the English "Tags").

- [ ] **Step 3: Translate all titles**

In each of the eight Livewire components listed above: remove the `#[Title('…')]` attribute (and unused import) and chain `->title(__('<key>'))` onto the view returned from `render()`, using:

| Component | Lang key |
|---|---|
| `Categories/CategoryIndex` | `admin.categories.title_section` |
| `Tags/TagIndex` | `admin.tags.title_section` |
| `Pages/PageIndex` | `admin.pages.title_section` |
| `Pages/PageForm` | `admin.pages.edit_page` when `$this->page?->exists`, else `admin.pages.create_page` (mirror the NewsForm pattern from Task 6) |
| `Videos/VideoIndex` | `admin.videos.title_section` |
| `Users/UserIndex` | `admin.users.title_section` |
| `Media/MediaIndex` | `admin.media.title_section` |
| `Activity/ActivityIndex` | `admin.activity.title_section` |

- [ ] **Step 4: Replace page-header markup with the component in each view**

Apply the same mechanical transformation in all seven index views. The existing markup is always:

```blade
    <div class="page-header">
        <div>
            <h1>{{ __('<title key>') }}</h1>
            <div class="subtitle">{{ __('<subtitle key>') }}</div>
        </div>
        [optional action button/link]
    </div>
```

Replace with:

```blade
    <x-admin.page-header :title="__('<title key>')" :subtitle="__('<subtitle key>')">
        [optional action button/link, unchanged]
    </x-admin.page-header>
```

Per-view specifics (action content stays exactly as it is in the current file):
- `categories/index.blade.php`, `tags/index.blade.php`, `videos/index.blade.php`, `users/index.blade.php`: the action is the existing `@if (! $showForm) <button …wire:click="startCreate"…> @endif` block — keep it verbatim inside the component slot (the `@if` works inside a slot).
- `pages/index.blade.php`: action is the existing `<a href="{{ route('admin.pages.create') }}" …>` link.
- `media/index.blade.php`, `activity/index.blade.php`: no action — use the self-closing form `<x-admin.page-header :title="…" :subtitle="…" />`.

- [ ] **Step 5: Replace lang-chip loops and empty-states in swept views**

1. `pages/index.blade.php`: replace the `@foreach (['kr','uz','ru','en'] …)` chip loop cell with `<x-admin.lang-chips :available="$available" />` (note: pages pluck `language`, the existing `@php $available = $p->translations->pluck('language')->all(); @endphp` line stays).
2. In each of the seven views, replace any `<div class="empty-state">…</div>` block with the `<x-admin.empty-state>` component, carrying over the icon class, the `fw-semibold` text as `:title`, and any remaining small text/links as the slot. Example from `media/index.blade.php`:

```blade
        <div class="card"><x-admin.empty-state icon="fa-regular fa-image" :title="__('admin.media.no_files')" /></div>
```

- [ ] **Step 6: Run tests**

Run: `php artisan test --compact --filter=AdminPageTitlesTest` — expected: PASS.
Run: `php artisan test --compact tests/Feature/Admin` — expected: PASS.
Run: `vendor/bin/pint --dirty --format agent`

- [ ] **Step 7: Commit**

```bash
git add app/Livewire/Admin resources/views/livewire/admin tests/Feature/Admin/AdminPageTitlesTest.php
git commit -m "refactor(admin): translated titles and shared components across admin pages"
```

---

### Task 8: Media library FilePond label fix

**Files:**
- Modify: `resources/views/livewire/admin/media/index.blade.php`
- Test: `tests/Feature/Admin/MediaLibraryTest.php`

- [ ] **Step 1: Write the failing test**

Append to the existing describe block (or add a new one) in `tests/Feature/Admin/MediaLibraryTest.php`:

```php
    it('does not double-escape the FilePond idle label', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.media.index'));

        $response->assertOk();
        // The raw-escaped span must never reach the page as text.
        $response->assertDontSee('&lt;span class=&quot;filepond--label-action&quot;&gt;', false);
        $response->assertDontSee('&lt;span', false);
    })->group('feature', 'admin');
```

(Match the existing file's import/use style for `User`.)

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=MediaLibraryTest`
Expected: the new test FAILS (the page currently contains `&lt;span`).

- [ ] **Step 3: Fix the label output**

In `resources/views/livewire/admin/media/index.blade.php` line 68, replace:

```js
                        labelIdle: '{{ __('admin.media.filepond_idle') }}',
```

with:

```js
                        labelIdle: @js(__('admin.media.filepond_idle')),
```

(`@js` emits a properly quoted/escaped JS string — the HTML inside the translation reaches FilePond intact, while `{{ }}` HTML-escaped it.)

- [ ] **Step 4: Run tests**

Run: `php artisan test --compact --filter=MediaLibraryTest`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add resources/views/livewire/admin/media/index.blade.php tests/Feature/Admin/MediaLibraryTest.php
git commit -m "fix(admin): FilePond upload label rendered raw HTML"
```

---

### Task 9: Final verification

**Files:** none new (fixes only if something fails)

- [ ] **Step 1: Full admin test suite**

Run: `php artisan test --compact tests/Feature/Admin`
Expected: all PASS.

- [ ] **Step 2: Formatting + build**

Run: `vendor/bin/pint --dirty --format agent` — expected: clean.
Run: `npm run build` — expected: success.

- [ ] **Step 3: Visual verification (light + dark)**

Use the Playwright setup from the design session (a throwaway install in `/tmp/admin-shots` with login `admin@example.com` / `password` against `http://localhost`) to screenshot: dashboard, news index, news edit, media — once with default (light) theme, once after clicking `#theme-toggle`. Check:
- four lang chips render on one line per row;
- status pills show Russian labels;
- drafts stat is amber, categories/tags are separate cards;
- dark theme has no unreadable text, no white flashes of hard-coded backgrounds (table hover, chips, thumbnails, sticky bar, FilePond panel);
- media upload area shows "Перетащите изображения сюда или выберите" with "выберите" as a styled link;
- browser console has no new errors.

- [ ] **Step 4: Fix anything found, re-run affected tests, commit**

```bash
git add -A resources/css/admin.css resources/views
git commit -m "polish(admin): visual fixes from screenshot pass"
```

(Skip this commit if nothing needed fixing.)
