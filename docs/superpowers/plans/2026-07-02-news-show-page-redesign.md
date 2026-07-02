# News Show Page Redesign & Content Fidelity — Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Rebuild the public news article page (`ShowNews`) in an editorial two-column layout and make cover images, in-content images, and text styling authored in the TinyMCE editor render faithfully on the public site.

**Architecture:** Fix the sanitizer to preserve a safe subset of inline `style`, add one shared article stylesheet (`public/css/news-article.css`) used by both the public page and the TinyMCE editor (true WYSIWYG), extend the `ShowNews` Livewire component with eager-loads / related-news / reading-time / SEO data, and rebuild the Blade view. Everything is covered by Pest unit + feature tests.

**Tech Stack:** Laravel 12, Livewire 4, Pest 3, TinyMCE 7, Bootstrap 5, Alpine (bundled with Livewire).

**Branch:** `news-show-page-redesign` (already created; the approved spec is committed there). All commits below land on this branch.

**Spec:** `docs/superpowers/specs/2026-07-02-news-show-page-redesign-design.md`

---

## File Structure

| File | Responsibility | Action |
|------|----------------|--------|
| `app/Support/HtmlSanitizer.php` | Allow a curated inline-`style` property allowlist | Modify |
| `app/Models/NewsTranslation.php` | `readingTime()` helper | Modify |
| `app/Livewire/ShowNews.php` | Eager-loads, related news, SEO layout data | Modify |
| `resources/views/livewire/show-news.blade.php` | Editorial two-column layout | Rewrite |
| `public/css/news-article.css` | Article typography + page component styles | Create |
| `resources/views/components/layouts/app.blade.php` | Dynamic `<title>` / description / OpenGraph | Modify |
| `resources/views/livewire/admin/news/form.blade.php` | TinyMCE `content_css` + `body_class` | Modify |
| `lang/{uz,kr,ru,en}/messages.php` | UI strings (related, share, views, min read) | Modify |
| `tests/Unit/HtmlSanitizerTest.php` | Style-allowlist tests | Modify |
| `tests/Unit/Models/NewsTranslationTest.php` | Reading-time tests | Create |
| `tests/Feature/Livewire/ShowNewsTest.php` | Related-news + SEO + render tests | Modify |

---

## Task 1: Sanitizer — curated inline-`style` allowlist

**Files:**
- Modify: `app/Support/HtmlSanitizer.php`
- Test: `tests/Unit/HtmlSanitizerTest.php`

- [ ] **Step 1: Write the failing tests**

Append inside the existing `describe('HtmlSanitizer', function () { ... })` block in `tests/Unit/HtmlSanitizerTest.php` (before the closing `});`):

```php
    it('preserves safe text-align styling', function () {
        $out = HtmlSanitizer::sanitize('<p style="text-align:center">x</p>');
        expect($out)->toContain('text-align')
            ->and($out)->toContain('center');
    })->group('unit', 'security');

    it('drops disallowed style properties but keeps allowed ones', function () {
        $out = HtmlSanitizer::sanitize('<p style="position:fixed;text-align:center">x</p>');
        expect($out)->not->toContain('position')
            ->and($out)->toContain('text-align');
    })->group('unit', 'security');

    it('drops style declarations with dangerous values', function () {
        $out = HtmlSanitizer::sanitize('<p style="width:expression(alert(1));text-align:left">x</p>');
        expect($out)->not->toContain('expression')
            ->and($out)->toContain('text-align');
    })->group('unit', 'security');

    it('drops url() based style values', function () {
        $out = HtmlSanitizer::sanitize('<div style="background:url(javascript:alert(1))">x</div>');
        expect($out)->not->toContain('url(')
            ->and($out)->not->toContain('javascript');
    })->group('unit', 'security');

    it('keeps image width and height attributes', function () {
        $out = HtmlSanitizer::sanitize('<img src="/a.jpg" width="300" height="200" alt="a">');
        expect($out)->toContain('width="300"')
            ->and($out)->toContain('height="200"');
    })->group('unit', 'security');
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --compact --filter=HtmlSanitizer`
Expected: FAIL — `text-align` is stripped (the new assertions fail; existing ones pass).

- [ ] **Step 3: Add the style property allowlist constant**

In `app/Support/HtmlSanitizer.php`, add this constant right after the `SAFE_URL_SCHEMES` constant:

```php
    /**
     * CSS properties permitted inside inline style attributes.
     *
     * @var list<string>
     */
    private const ALLOWED_STYLE_PROPS = [
        'text-align', 'text-decoration',
        'width', 'height', 'max-width',
        'float', 'margin', 'margin-left', 'margin-right',
    ];
```

- [ ] **Step 4: Allow `style` on every tag**

In the `ALLOWED_ATTRS` map, change the `'*'` entry from:

```php
        '*' => ['class', 'id', 'lang', 'dir'],
```

to:

```php
        '*' => ['class', 'id', 'lang', 'dir', 'style'],
```

- [ ] **Step 5: Filter the style value inside the attribute loop**

In the `foreach ($attrNames as $name)` loop, immediately after the existing `href`/`src` `if (in_array($lower, ['href', 'src'], true)) { ... }` block, add:

```php
                if ($lower === 'style') {
                    $clean = self::sanitizeStyle($el->getAttribute($name));
                    if ($clean === '') {
                        $el->removeAttribute($name);
                    } else {
                        $el->setAttribute($name, $clean);
                    }
                }
```

- [ ] **Step 6: Add the `sanitizeStyle()` helper**

Add this method right after the `isSafeUrl()` method:

```php
    /**
     * Reduce an inline style string to a safe, allowlisted subset.
     */
    private static function sanitizeStyle(string $style): string
    {
        $safe = [];

        foreach (explode(';', $style) as $declaration) {
            if (trim($declaration) === '') {
                continue;
            }

            $parts = explode(':', $declaration, 2);
            if (count($parts) !== 2) {
                continue;
            }

            $prop = strtolower(trim($parts[0]));
            $value = trim($parts[1]);

            if (! in_array($prop, self::ALLOWED_STYLE_PROPS, true)) {
                continue;
            }

            if ($value === '' || preg_match('/url\(|expression|javascript:|@import|[<>\\\\]/i', $value) === 1) {
                continue;
            }

            $safe[] = $prop.': '.$value;
        }

        return implode('; ', $safe);
    }
```

- [ ] **Step 7: Run tests to verify they pass**

Run: `php artisan test --compact --filter=HtmlSanitizer`
Expected: PASS (all assertions, old and new).

- [ ] **Step 8: Commit**

```bash
git add app/Support/HtmlSanitizer.php tests/Unit/HtmlSanitizerTest.php
git commit -m "feat(sanitizer): allowlist safe inline styles (text-align, image sizing)"
```

---

## Task 2: `NewsTranslation::readingTime()`

**Files:**
- Modify: `app/Models/NewsTranslation.php`
- Test: `tests/Unit/Models/NewsTranslationTest.php` (create)

- [ ] **Step 1: Write the failing test**

Create `tests/Unit/Models/NewsTranslationTest.php`:

```php
<?php

use App\Models\NewsTranslation;

describe('NewsTranslation reading time', function () {
    it('returns at least one minute for empty content', function () {
        $t = new NewsTranslation(['content' => '']);
        expect($t->readingTime())->toBe(1);
    })->group('unit', 'models');

    it('estimates one minute for short content', function () {
        $t = new NewsTranslation(['content' => '<p>Just a few words here.</p>']);
        expect($t->readingTime())->toBe(1);
    })->group('unit', 'models');

    it('scales with word count at about 200 wpm', function () {
        $content = '<p>'.str_repeat('word ', 400).'</p>';
        $t = new NewsTranslation(['content' => $content]);
        expect($t->readingTime())->toBe(2);
    })->group('unit', 'models');
});
```

- [ ] **Step 2: Run test to verify it fails**

Run: `php artisan test --compact --filter=NewsTranslation`
Expected: FAIL with "Call to undefined method ...::readingTime()".

- [ ] **Step 3: Implement `readingTime()`**

In `app/Models/NewsTranslation.php`, add this method after `coverUrl()`:

```php
    /**
     * Estimated reading time of the body content in whole minutes (min 1).
     */
    public function readingTime(): int
    {
        $text = trim(strip_tags((string) $this->content));

        if ($text === '') {
            return 1;
        }

        $words = count(preg_split('/\s+/', $text, -1, PREG_SPLIT_NO_EMPTY) ?: []);

        return max(1, (int) ceil($words / 200));
    }
```

- [ ] **Step 4: Run test to verify it passes**

Run: `php artisan test --compact --filter=NewsTranslation`
Expected: PASS.

- [ ] **Step 5: Commit**

```bash
git add app/Models/NewsTranslation.php tests/Unit/Models/NewsTranslationTest.php
git commit -m "feat(news): add reading-time estimate to NewsTranslation"
```

---

## Task 3: Add i18n strings

**Files:**
- Modify: `lang/uz/messages.php`, `lang/kr/messages.php`, `lang/ru/messages.php`, `lang/en/messages.php`

No automated test (verified in Task 7's rendering test). Each file returns a flat `['key' => 'value']` array; add the new keys before the closing `];`.

- [ ] **Step 1: Add keys to `lang/uz/messages.php`**

```php
    'related_news' => 'O‘xshash yangiliklar',
    'min_read' => 'daqiqa o‘qish',
    'views_label' => 'ko‘rishlar',
    'share' => 'Ulashish',
    'copy_link' => 'Havolani nusxalash',
    'link_copied' => 'Havola nusxalandi',
```

- [ ] **Step 2: Add keys to `lang/kr/messages.php`**

```php
    'related_news' => 'Ўхшаш янгиликлар',
    'min_read' => 'дақиқа ўқиш',
    'views_label' => 'кўришлар',
    'share' => 'Улашиш',
    'copy_link' => 'Ҳаволани нусхалаш',
    'link_copied' => 'Ҳавола нусхаланди',
```

- [ ] **Step 3: Add keys to `lang/ru/messages.php`**

```php
    'related_news' => 'Похожие новости',
    'min_read' => 'мин чтения',
    'views_label' => 'просмотров',
    'share' => 'Поделиться',
    'copy_link' => 'Копировать ссылку',
    'link_copied' => 'Ссылка скопирована',
```

- [ ] **Step 4: Add keys to `lang/en/messages.php`**

```php
    'related_news' => 'Related news',
    'min_read' => 'min read',
    'views_label' => 'views',
    'share' => 'Share',
    'copy_link' => 'Copy link',
    'link_copied' => 'Link copied',
```

- [ ] **Step 5: Verify the keys resolve**

Run: `php artisan tinker --execute 'app()->setLocale("ru"); echo __("messages.related_news");'`
Expected output: `Похожие новости`

- [ ] **Step 6: Commit**

```bash
git add lang/uz/messages.php lang/kr/messages.php lang/ru/messages.php lang/en/messages.php
git commit -m "i18n: add news article page strings"
```

---

## Task 4: `ShowNews` backend — eager-loads, related news, SEO data

**Files:**
- Modify: `app/Livewire/ShowNews.php`
- Test: `tests/Feature/Livewire/ShowNewsTest.php`

- [ ] **Step 1: Write the failing tests**

Add these `it(...)` blocks inside the existing `describe('ShowNews Component', ...)` in `tests/Feature/Livewire/ShowNewsTest.php` (before the closing `});`). Also add the imports `use App\Models\Tag;` and `use App\Models\Category;` at the top of the file if missing:

```php
    it('shows related news sharing a tag and excludes the current article', function () {
        setAppLocale('uz');
        $tag = Tag::factory()->create();

        $main = createNewsWithTranslation(['slug' => 'main', 'category_id' => null]);
        $main->tags()->attach($tag->id);

        $related = createNewsWithTranslation(['slug' => 'related-one']);
        $related->tags()->attach($tag->id);

        $unrelated = createNewsWithTranslation(['slug' => 'unrelated', 'category_id' => null]);

        Livewire::test(ShowNews::class, ['slug' => 'main'])
            ->assertSet('related_news', fn ($v) => $v->contains('id', $related->id)
                && ! $v->contains('id', $main->id)
                && ! $v->contains('id', $unrelated->id));
    })->group('feature', 'livewire');

    it('falls back to same-category news when there are no tag matches', function () {
        setAppLocale('uz');
        $category = createCategoryWithTranslation();

        $main = createNewsWithTranslation(['slug' => 'main', 'category_id' => $category->id]);
        $sibling = createNewsWithTranslation(['slug' => 'sibling', 'category_id' => $category->id]);

        Livewire::test(ShowNews::class, ['slug' => 'main'])
            ->assertSet('related_news', fn ($v) => $v->contains('id', $sibling->id)
                && ! $v->contains('id', $main->id));
    })->group('feature', 'livewire');
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --compact --filter=ShowNews`
Expected: FAIL — property `related_news` does not exist on the component.

- [ ] **Step 3: Add the `related_news` property**

In `app/Livewire/ShowNews.php`, add below the existing `public $popular_news;`:

```php
    public $related_news;
```

- [ ] **Step 4: Rewrite `mount()` with eager-loads and related news**

Replace the whole `mount()` method with:

```php
    public function mount($slug): void
    {
        $news = News::with(['translation', 'category.translation', 'tags', 'user'])
            ->where('slug', $slug)
            ->first();

        if (! $news || ! $news->translation) {
            abort(404);
        }

        $isPubliclyVisible = News::published()->whereKey($news->id)->exists();

        if (! $isPubliclyVisible && ! auth()->user()?->canEditNews($news)) {
            abort(404);
        }

        $this->news = $news;

        if ($isPubliclyVisible) {
            $this->trackView();
        }

        $locale = app()->getLocale();

        $this->popular_news = News::published()
            ->whereHas('translations', fn ($query) => $query->where('lang', $locale))
            ->with('translation')
            ->orderBy('view_count', 'DESC')
            ->limit(6)
            ->get();

        $this->related_news = $this->loadRelatedNews($news, $locale);
    }
```

- [ ] **Step 5: Add the `loadRelatedNews()` helper**

Add this method after `mount()` (before `trackView()`), and add `use Illuminate\Support\Collection;` to the imports at the top:

```php
    /**
     * Related articles: published news sharing a tag with the current article,
     * topped up with same-category news when there aren't enough tag matches.
     */
    private function loadRelatedNews(News $news, string $locale): Collection
    {
        $tagIds = $news->tags->pluck('id');

        $related = News::published()
            ->whereHas('translations', fn ($query) => $query->where('lang', $locale))
            ->where('id', '!=', $news->id)
            ->when(
                $tagIds->isNotEmpty(),
                fn ($query) => $query->whereHas('tags', fn ($tag) => $tag->whereIn('tags.id', $tagIds)),
                fn ($query) => $query->whereRaw('1 = 0'),
            )
            ->with('translation')
            ->latest()
            ->limit(3)
            ->get();

        if ($related->count() < 3 && $news->category_id) {
            $exclude = $related->pluck('id')->push($news->id);

            $fallback = News::published()
                ->whereHas('translations', fn ($query) => $query->where('lang', $locale))
                ->where('category_id', $news->category_id)
                ->whereNotIn('id', $exclude)
                ->with('translation')
                ->latest()
                ->limit(3 - $related->count())
                ->get();

            $related = $related->concat($fallback);
        }

        return $related;
    }
```

- [ ] **Step 6: Replace `render()` to pass SEO layout data**

Replace the existing `render()` method with:

```php
    public function render()
    {
        $translation = $this->news->translation;
        $title = $translation->seo_title ?: $translation->title;

        return view('livewire.show-news')
            ->layout('components.layouts.app', [
                'title' => $title,
                'metaDescription' => $translation->seo_description ?: $translation->short_description,
                'ogTitle' => $title,
                'ogImage' => $translation->coverUrl(),
                'canonical' => route('show.news', $this->news->slug),
            ]);
    }
```

- [ ] **Step 7: Run tests to verify they pass**

Run: `php artisan test --compact --filter=ShowNews`
Expected: PASS (new related-news tests plus all existing popular-news/404/view-tracking tests).

- [ ] **Step 8: Commit**

```bash
git add app/Livewire/ShowNews.php tests/Feature/Livewire/ShowNewsTest.php
git commit -m "feat(news): related news, eager-loads and SEO data for ShowNews"
```

---

## Task 5: Dynamic `<title>` / description / OpenGraph in the app layout

**Files:**
- Modify: `resources/views/components/layouts/app.blade.php`

No standalone test (asserted end-to-end in Task 7).

- [ ] **Step 1: Make the head tags dynamic**

In `resources/views/components/layouts/app.blade.php`, find this block in `<head>`:

```blade
    <meta name="description" content="Center for Economic Research and Reforms">
    <meta name="keywords" content="Center for Economic Research and Reforms">
    <link rel="canonical" href="https://cerr.uz" />

    <title>Center for Economic Research and Reforms</title>
```

Replace it with:

```blade
    <meta name="description" content="{{ $metaDescription ?? 'Center for Economic Research and Reforms' }}">
    <meta name="keywords" content="Center for Economic Research and Reforms">
    <link rel="canonical" href="{{ $canonical ?? 'https://cerr.uz' }}" />

    <title>{{ $title ?? 'Center for Economic Research and Reforms' }}</title>

    @isset($ogTitle)
        <meta property="og:type" content="article">
        <meta property="og:title" content="{{ $ogTitle }}">
    @endisset
    @isset($metaDescription)
        <meta property="og:description" content="{{ $metaDescription }}">
    @endisset
    @isset($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endisset
```

- [ ] **Step 2: Verify other pages still render (no regression)**

Run: `php artisan test --compact --filter=Home`
Expected: PASS (home page still renders with the default title; `$title` etc. simply fall back).

- [ ] **Step 3: Commit**

```bash
git add resources/views/components/layouts/app.blade.php
git commit -m "feat(seo): dynamic title, description and OpenGraph tags in app layout"
```

---

## Task 6: Article stylesheet

**Files:**
- Create: `public/css/news-article.css`

No standalone test (static asset; exercised visually and via `npm run build` not required — it's a plain public file).

- [ ] **Step 1: Create `public/css/news-article.css`**

```css
/* News article page. Shared by the public show page and the TinyMCE editor
   (loaded via content_css + body_class="news-article-body") for true WYSIWYG. */

[x-cloak] { display: none !important; }

/* ---- Article header --------------------------------------------------- */
.news-article { max-width: 780px; }

.article-breadcrumb { font-size: 14px; color: #6b7280; margin-bottom: 18px; }
.article-breadcrumb a { color: #6b7280; text-decoration: none; }
.article-breadcrumb a:hover { color: #2563eb; }
.article-breadcrumb .sep { margin: 0 8px; color: #cbd5e1; }

.article-category-badge {
    display: inline-block;
    background: #2563eb;
    color: #fff;
    font-size: 12px;
    font-weight: 600;
    text-transform: uppercase;
    letter-spacing: .4px;
    padding: 4px 12px;
    border-radius: 20px;
    text-decoration: none;
    margin-bottom: 14px;
}
.article-category-badge:hover { background: #1d4ed8; color: #fff; }

.article-title {
    font-size: clamp(28px, 4vw, 40px);
    line-height: 1.2;
    font-weight: 800;
    color: #111827;
    margin: 0 0 16px;
}

.article-meta {
    display: flex;
    flex-wrap: wrap;
    gap: 18px;
    font-size: 14px;
    color: #6b7280;
    padding-bottom: 20px;
    border-bottom: 1px solid #eceff3;
    margin-bottom: 24px;
}
.article-meta i { margin-right: 6px; color: #9ca3af; }

.article-lead {
    font-size: 20px;
    line-height: 1.6;
    color: #374151;
    font-weight: 500;
    margin-bottom: 24px;
}

.article-cover { margin: 0 0 28px; }
.article-cover img { width: 100%; height: auto; border-radius: 12px; display: block; }

/* ---- Article body (prose) --------------------------------------------- */
.news-article-body { font-size: 18px; line-height: 1.8; color: #1f2937; }
.news-article-body > *:first-child { margin-top: 0; }
.news-article-body p { margin: 0 0 1.25em; }
.news-article-body h1,
.news-article-body h2,
.news-article-body h3,
.news-article-body h4,
.news-article-body h5,
.news-article-body h6 {
    color: #111827; font-weight: 700; line-height: 1.3; margin: 1.8em 0 .6em;
}
.news-article-body h1 { font-size: 1.8em; }
.news-article-body h2 { font-size: 1.5em; }
.news-article-body h3 { font-size: 1.3em; }
.news-article-body h4 { font-size: 1.1em; }
.news-article-body a { color: #2563eb; text-decoration: underline; }
.news-article-body ul,
.news-article-body ol { margin: 0 0 1.25em; padding-left: 1.5em; }
.news-article-body li { margin-bottom: .5em; }
.news-article-body blockquote {
    margin: 1.5em 0;
    padding: .6em 1.2em;
    border-left: 4px solid #2563eb;
    background: #f8fafc;
    color: #475569;
    font-style: italic;
    border-radius: 0 8px 8px 0;
}
.news-article-body pre {
    background: #0f172a; color: #e2e8f0; padding: 1em 1.2em;
    border-radius: 8px; overflow-x: auto; margin: 1.5em 0;
}
.news-article-body code { background: #f1f5f9; padding: .15em .4em; border-radius: 4px; font-size: .9em; }
.news-article-body pre code { background: transparent; padding: 0; }
.news-article-body img { max-width: 100%; height: auto; border-radius: 10px; margin: 1em 0; }
.news-article-body figure { margin: 1.5em 0; }
.news-article-body figure img { margin: 0; }
.news-article-body figcaption { text-align: center; font-size: 14px; color: #6b7280; margin-top: .5em; }
.news-article-body table { width: 100%; border-collapse: collapse; margin: 1.5em 0; font-size: 16px; }
.news-article-body th,
.news-article-body td { border: 1px solid #e5e7eb; padding: 10px 14px; text-align: left; }
.news-article-body thead th { background: #f8fafc; font-weight: 600; }
.news-article-body tbody tr:nth-child(even) { background: #fbfcfe; }
.news-article-body hr { border: 0; border-top: 1px solid #e5e7eb; margin: 2em 0; }

/* ---- Footer: tags + share --------------------------------------------- */
.article-footer {
    display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between;
    gap: 16px; margin-top: 32px; padding-top: 20px; border-top: 1px solid #eceff3;
}
.article-tags { display: flex; flex-wrap: wrap; gap: 8px; }
.article-tag { background: #f1f5f9; color: #475569; font-size: 13px; padding: 5px 12px; border-radius: 6px; }
.article-share { display: flex; align-items: center; gap: 10px; }
.article-share .share-label { font-size: 14px; color: #6b7280; }
.share-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 38px; height: 38px; border-radius: 50%;
    border: 1px solid #e5e7eb; background: #fff; color: #475569;
    cursor: pointer; transition: all .15s; text-decoration: none;
}
.share-btn:hover { background: #2563eb; color: #fff; border-color: #2563eb; }
.article-share .copied { font-size: 13px; color: #16a34a; }

/* ---- Related news ------------------------------------------------------ */
.related-news { margin-top: 48px; }
.related-title {
    font-size: 22px; font-weight: 700; color: #111827;
    margin-bottom: 20px; padding-bottom: 10px;
    border-bottom: 2px solid #2563eb; display: inline-block;
}
.related-card { display: block; text-decoration: none; color: inherit; }
.related-thumb {
    aspect-ratio: 16 / 10; border-radius: 10px; overflow: hidden;
    background: #eef1f5; margin-bottom: 10px;
}
.related-thumb img { width: 100%; height: 100%; object-fit: cover; transition: transform .3s; }
.related-card:hover .related-thumb img { transform: scale(1.05); }
.related-card-title { font-size: 16px; font-weight: 600; line-height: 1.4; color: #1f2937; margin: 0 0 6px; }
.related-card:hover .related-card-title { color: #2563eb; }
.related-card-date { font-size: 13px; color: #9ca3af; }

@media (max-width: 767px) {
    .article-footer { flex-direction: column; align-items: flex-start; }
    .related-news .col-md-4 { margin-bottom: 24px; }
}
```

- [ ] **Step 2: Commit**

```bash
git add public/css/news-article.css
git commit -m "feat(news): shared article typography stylesheet"
```

---

## Task 7: Rebuild the show-news Blade view

**Files:**
- Rewrite: `resources/views/livewire/show-news.blade.php`
- Test: `tests/Feature/Livewire/ShowNewsTest.php`

- [ ] **Step 1: Write the failing tests**

Add to `tests/Feature/Livewire/ShowNewsTest.php` inside the `describe(...)` block:

```php
    it('sets the page title from seo_title', function () {
        setAppLocale('uz');
        createNewsWithTranslation(['slug' => 'seo-news'], ['seo_title' => 'Custom SEO Title', 'title' => 'Normal Title']);

        $this->get(route('show.news', 'seo-news'))->assertSee('Custom SEO Title', false);
    })->group('feature', 'livewire');

    it('falls back to the article title when seo_title is empty', function () {
        setAppLocale('uz');
        createNewsWithTranslation(['slug' => 'no-seo'], ['seo_title' => null, 'title' => 'Just The Title']);

        $this->get(route('show.news', 'no-seo'))->assertSee('Just The Title', false);
    })->group('feature', 'livewire');

    it('renders the article body wrapper, preserved styling, tags and related section', function () {
        setAppLocale('uz');
        $tag = Tag::factory()->create(['name' => 'economy']);

        $news = createNewsWithTranslation(
            ['slug' => 'full-render', 'category_id' => null],
            ['content' => '<p style="text-align:center">Centered body copy</p>'],
        );
        $news->tags()->attach($tag->id);

        $related = createNewsWithTranslation(['slug' => 'rel-article']);
        $related->tags()->attach($tag->id);

        $this->get(route('show.news', 'full-render'))
            ->assertSee('news-article-body', false)
            ->assertSee('text-align', false)
            ->assertSee(__('messages.min_read'))
            ->assertSee('economy')
            ->assertSee(__('messages.related_news'));
    })->group('feature', 'livewire');

    it('renders no cover figure when the cover image is missing', function () {
        setAppLocale('uz');
        createNewsWithTranslation(['slug' => 'no-cover'], ['image_url' => '']);

        $this->get(route('show.news', 'no-cover'))
            ->assertOk()
            ->assertDontSee('article-cover', false);
    })->group('feature', 'livewire');
```

- [ ] **Step 2: Run tests to verify they fail**

Run: `php artisan test --compact --filter=ShowNews`
Expected: FAIL — the current view has no `news-article-body` wrapper or related section, and the title is still hardcoded.

- [ ] **Step 3: Rewrite the view**

Replace the entire contents of `resources/views/livewire/show-news.blade.php` with:

```blade
<div>
    @push('styles')
        <link rel="stylesheet" href="{{ asset('css/news-article.css') }}">
    @endpush

    @php($views = (int) $news->view_count)

    <section class="echo-hero-section inner inner-post">
        <div class="container">
            <div class="row gx-5 sticky-coloum-wrap">
                <div class="col-xl-8 col-lg-8">
                    <article class="news-article">
                        <nav class="article-breadcrumb" aria-label="breadcrumb">
                            <a href="{{ route('home') }}">@lang('messages.main')</a>
                            @if ($news->category?->translation)
                                <span class="sep">/</span>
                                <a href="{{ route('show.category', $news->category->slug) }}">{{ $news->category->translation->name }}</a>
                            @endif
                        </nav>

                        @if ($news->category?->translation)
                            <a href="{{ route('show.category', $news->category->slug) }}" class="article-category-badge">{{ $news->category->translation->name }}</a>
                        @endif

                        <h1 class="article-title">{{ $news->translation->title }}</h1>

                        <div class="article-meta">
                            <span><i class="fa-light fa-clock"></i> {{ $news->created_at->format('d.m.Y') }}</span>
                            <span><i class="fa-light fa-eye"></i> {{ $views >= 1000 ? round($views / 1000, 1).'k' : $views }} @lang('messages.views_label')</span>
                            <span><i class="fa-light fa-book-open"></i> {{ $news->translation->readingTime() }} @lang('messages.min_read')</span>
                        </div>

                        @if ($news->translation->short_description)
                            <p class="article-lead">{{ $news->translation->short_description }}</p>
                        @endif

                        @if ($news->translation->coverUrl())
                            <figure class="article-cover">
                                <img src="{{ $news->translation->coverUrl() }}" alt="{{ $news->translation->title }}">
                            </figure>
                        @endif

                        <div class="news-article-body">
                            @sanitized($news->translation->content)
                        </div>

                        <div class="article-footer">
                            @if ($news->tags->isNotEmpty())
                                <div class="article-tags">
                                    @foreach ($news->tags as $tag)
                                        <span class="article-tag">#{{ $tag->name }}</span>
                                    @endforeach
                                </div>
                            @else
                                <span></span>
                            @endif

                            <div class="article-share" x-data="articleShare(@js(route('show.news', $news->slug)))">
                                <span class="share-label">@lang('messages.share'):</span>
                                <a :href="telegram" target="_blank" rel="noopener" class="share-btn"><i class="fa-brands fa-telegram"></i></a>
                                <a :href="facebook" target="_blank" rel="noopener" class="share-btn"><i class="fa-brands fa-facebook-f"></i></a>
                                <a :href="twitter" target="_blank" rel="noopener" class="share-btn"><i class="fa-brands fa-x-twitter"></i></a>
                                <button type="button" @click="copy()" class="share-btn" title="@lang('messages.copy_link')"><i class="fa-light fa-link"></i></button>
                                <span class="copied" x-show="copied" x-cloak>@lang('messages.link_copied')</span>
                            </div>
                        </div>
                    </article>

                    @if ($related_news->isNotEmpty())
                        <section class="related-news">
                            <h3 class="related-title">@lang('messages.related_news')</h3>
                            <div class="row">
                                @foreach ($related_news as $item)
                                    <div class="col-md-4" wire:key="related-{{ $item->id }}">
                                        <a href="{{ route('show.news', $item->slug) }}" class="related-card">
                                            <div class="related-thumb">
                                                @if ($item->translation?->coverUrl())
                                                    <img src="{{ $item->translation->coverUrl() }}" alt="{{ $item->translation->title }}">
                                                @endif
                                            </div>
                                            <h4 class="related-card-title">{{ $item->translation?->title }}</h4>
                                            <span class="related-card-date">{{ $item->created_at->format('d.m.Y') }}</span>
                                        </a>
                                    </div>
                                @endforeach
                            </div>
                        </section>
                    @endif
                </div>

                <div class="col-xl-4 col-lg-4 sticky-coloum-item">
                    <div class="echo-right-ct-1">
                        <div class="echo-home-1-hero-area-top-story">
                            <h5 class="text-center">@lang('messages.popular')</h5>
                            @foreach ($popular_news as $item)
                                <div class="echo-top-story" wire:key="popular-{{ $item->id }}">
                                    <div class="echo-story-picture img-transition-scale">
                                        <a href="{{ route('show.news', $item->slug) }}"><img src="{{ $item->translation->coverUrl() }}" alt="{{ $item->translation->title }}" class="img-hover"></a>
                                    </div>
                                    <div class="echo-story-text">
                                        <h6><a href="{{ route('show.news', $item->slug) }}" class="title-hover">{{ $item->translation->title }}</a></h6>
                                        <a href="{{ route('show.news', $item->slug) }}" class="pe-none"><i class="fa-light fa-clock"></i> {{ $item->created_at->format('d-m-Y') }}</a>
                                    </div>
                                </div>
                                <hr>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
        <script>
            document.addEventListener('alpine:init', () => {
                Alpine.data('articleShare', (url) => ({
                    copied: false,
                    get encoded() { return encodeURIComponent(url); },
                    get telegram() { return 'https://t.me/share/url?url=' + this.encoded; },
                    get facebook() { return 'https://www.facebook.com/sharer/sharer.php?u=' + this.encoded; },
                    get twitter() { return 'https://twitter.com/intent/tweet?url=' + this.encoded; },
                    copy() {
                        navigator.clipboard.writeText(url).then(() => {
                            this.copied = true;
                            setTimeout(() => { this.copied = false; }, 2000);
                        });
                    },
                }));
            });
        </script>
    @endpush
</div>
```

- [ ] **Step 4: Run tests to verify they pass**

Run: `php artisan test --compact --filter=ShowNews`
Expected: PASS (title, styling, tags, related-section assertions plus all prior tests).

- [ ] **Step 5: Commit**

```bash
git add resources/views/livewire/show-news.blade.php tests/Feature/Livewire/ShowNewsTest.php
git commit -m "feat(news): editorial two-column article layout with related news & share"
```

---

## Task 8: TinyMCE — load the article stylesheet for WYSIWYG

**Files:**
- Modify: `resources/views/livewire/admin/news/form.blade.php`
- Test: `tests/Feature/Admin/NewsFormUiTest.php` (regression only)

- [ ] **Step 1: Add `content_css` + `body_class` to the editor config**

In `resources/views/livewire/admin/news/form.blade.php`, inside the `window.tinymce.init({ ... })` object, find:

```javascript
                        branding: false,
```

and add the two lines immediately after it:

```javascript
                        content_css: '{{ asset('css/news-article.css') }}',
                        body_class: 'news-article-body',
```

- [ ] **Step 2: Verify the admin form still renders (regression)**

Run: `php artisan test --compact --filter=NewsFormUi`
Expected: PASS (the form still loads; the editor now points at the shared stylesheet).

- [ ] **Step 3: Commit**

```bash
git add resources/views/livewire/admin/news/form.blade.php
git commit -m "feat(admin): match TinyMCE preview to the public article typography"
```

---

## Task 9: Format, full test run, and wrap-up

**Files:** none (verification only)

- [ ] **Step 1: Run Pint on changed files**

Run: `vendor/bin/pint --dirty --format agent`
Expected: no style violations remain (auto-fixes applied).

- [ ] **Step 2: Commit any Pint fixes**

```bash
git add -A
git commit -m "style: pint" || echo "nothing to format"
```

- [ ] **Step 3: Run the full news-related suite**

Run: `php artisan test --compact --filter='HtmlSanitizer|NewsTranslation|ShowNews|NewsForm|NewsCrud|NewsStatus|NewsIndex'`
Expected: PASS across sanitizer, reading-time, show-news, and admin news tests.

- [ ] **Step 4: Run the whole test suite as a final gate**

Run: `php artisan test --compact`
Expected: PASS (no regressions anywhere).

- [ ] **Step 5: Manual smoke check (report results, do not self-approve)**

Ask the user to run `npm run dev` (or confirm assets are built) and open a published article via the admin form's "View on site" link, verifying: cover image shows, an inline image and a centered paragraph render as authored, metadata bar + tags + related grid appear, and the sidebar popular list still works.

---

## Notes / Gotchas

- **`->layout(view, data)`** is how the SEO title/description/OG values reach `app.blade.php`; other pages don't pass those vars, so the `?? default` / `@isset` fallbacks keep them unchanged.
- **`@push('styles')` / `@push('scripts')`** work here because `ShowNews` is a *full-page* Livewire component rendered through the layout (stacks are shared in a single render pass).
- **Alpine** is auto-injected by Livewire; registering `articleShare` on `alpine:init` guarantees it exists before Alpine initializes the `x-data`.
- **`whereRaw('1 = 0')`** in the no-tags branch guarantees the tag query returns nothing so the category fallback is the sole source of related news.
- The **variable-shadowing bug** (`@foreach($popular_news as $news)`) is fixed by iterating with `$item` in both the popular and related loops.
```
