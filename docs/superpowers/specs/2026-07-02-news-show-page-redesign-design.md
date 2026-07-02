# News Show Page — Redesign & Content Fidelity

**Date:** 2026-07-02
**Status:** Approved (design) — pending implementation plan
**Component:** `App\Livewire\ShowNews` / `resources/views/livewire/show-news.blade.php`

## Goal

Rebuild the public article page (route `show.news`, `/show-news/{slug}`) in an **editorial
two-column** layout, and fix the authoring→display pipeline so that the **cover image,
in-content images, and text styling** created in the admin TinyMCE editor render faithfully
on the public site.

Two intertwined workstreams:

1. **Content fidelity** — what the editor saves is what the reader sees.
2. **Layout redesign** — a polished, information-rich article page.

## Background / Current State

- **Authoring:** `App\Livewire\Admin\News\NewsForm` uses TinyMCE 7 (toolbar: styles, bold/
  italic/underline, lists, link/image/media/table, align left/center/right, code). In-content
  images are uploaded to `admin.inline-image.store` or chosen from the media picker
  (`news/inline`). On save, content is passed through `App\Support\HtmlSanitizer::sanitize()`.
- **Display:** `resources/views/livewire/show-news.blade.php` renders the body with the
  `@sanitized(...)` Blade directive (defined in `AppServiceProvider`, calls the same
  sanitizer). The component is rendered through the default Livewire layout
  `components.layouts.app`.
- **Cover image:** `NewsTranslation::coverUrl()` resolves legacy bare filenames
  (`public/images/news/`), `news/…` storage paths, and absolute URLs.

### Problems identified

1. **Text styling is stripped.** `HtmlSanitizer::ALLOWED_ATTRS` permits `class`/`id` but
   **not `style`**. TinyMCE writes text alignment as inline `style="text-align:center"`
   (and image float/size as inline style), so alignment and similar inline styling are
   silently lost on save.
2. **No typography scope.** The article body is dropped directly into `.echo-hero-baner`
   with no dedicated prose wrapper, so headings, lists, blockquotes, tables, and in-content
   images inherit arbitrary global theme CSS. Large/inline images can overflow the column.
3. **Editor ≠ output.** TinyMCE has no `content_css`/`content_style`, so the authoring
   preview does not resemble the published article (not true WYSIWYG).
4. **Latent variable shadowing.** The popular-news `@foreach($popular_news as $news)`
   reuses the `$news` variable, shadowing the main article. Harmless today (loop is last),
   but fragile once a related-news section is added below it.
5. **SEO fields unused.** `seo_title`/`seo_description` are captured in the form but the
   public `<title>`/meta description are hardcoded in `layouts/app.blade.php`.

## Design

### Part 1 — Content fidelity

**1a. Sanitizer: curated `style` allowlist.**
Extend `App\Support\HtmlSanitizer` so `style` is a permitted attribute, but its value is
parsed and filtered to a **property allowlist**. Retained properties (initial set):

- `text-align`
- `text-decoration`
- image sizing/position: `width`, `height`, `max-width`, `float`, `margin`,
  `margin-left`, `margin-right`

Any declaration whose property is not on the allowlist is dropped. Any declaration whose
**value** contains a dangerous token is dropped entirely: `url(`, `expression`,
`javascript:`, `@import`, `position`, `<`, `\` (backslash), or HTML/CSS escapes. If nothing
survives, the `style` attribute is removed. This preserves author intent (alignment,
image sizing) while remaining injection-safe — content is admin-authored **and** allowlisted.

*Rejected alternative:* reconfigure TinyMCE to emit Bootstrap alignment classes
(`text-center`) instead of inline styles. More locked-down but more moving parts and worse
in-editor WYSIWYG. The style-allowlist was chosen for faithful, low-friction rendering.

**1b. Shared article stylesheet.**
Add `public/css/news-article.css` scoping a `.news-article-body` wrapper. It styles:
headings (`h1`–`h6`), paragraphs, `ul`/`ol`/`li`, `blockquote`, `pre`/`code`, `table`
(with borders/zebra), `hr`, links, and — critically — `img { max-width:100%; height:auto }`
plus `figure`/`figcaption` (centered, captioned). Respects author `text-align` from 1a.
Loaded on the show page via `@push('styles')` (the app layout already exposes
`@stack('styles')`).

**1c. True WYSIWYG.**
Point TinyMCE's `content_css` at `/css/news-article.css` (add to the `tinymce.init` config
in the news form) so the editor renders the article body with the same typography as the
public page.

### Part 2 — Layout (Editorial two-column, "Option A")

Rebuild `resources/views/livewire/show-news.blade.php`. Main column (order):

1. **Breadcrumb** — Home / Category / (current title).
2. **Category badge + H1 title.**
3. **Metadata bar** — publish date, **view count**, **reading time** (see Part 3),
   (author name OFF by default).
4. **Lead** — `short_description`, styled as a distinct standfirst paragraph.
5. **Cover image** — contained, responsive; render nothing (no broken `<img>`) when
   `coverUrl()` is null.
6. **Article body** — wrapped in `.news-article-body`, rendered via `@sanitized(...)`.
7. **Footer row** — tags list + share buttons.

Right column: **Popular** sidebar (sticky) — the existing query, restyled.
Below the article: **Related news** grid.

Layout collapses to a single column on mobile (Bootstrap grid); the sidebar and related
grid stack below the article. The commented-out like/comment/share placeholder markup is
removed.

**Share buttons:** copy-link + Telegram, Facebook, X (Telegram prioritized for the
audience). Static share URLs built from the canonical article URL — no JS SDKs.

### Part 3 — Backend (`App\Livewire\ShowNews`)

- Eager-load `translation`, `category.translations`, `tags`, `user` on the article to power
  the metadata bar and breadcrumb and avoid N+1.
- **Reading time:** compute from the sanitized body word count (≈200 wpm), exposed to the
  view (helper method on the component or model accessor).
- **Related news:** published articles sharing tags with the current article, **falling back
  to same category** when there aren't enough; exclude the current article; cap (e.g. 3–6);
  require a translation in the active locale. Keep the existing popular-news query.
- Rename the sidebar loop variable (kill the `$news` shadowing).
- **SEO:** expose page title (`seo_title` → title → site name) and meta description
  (`seo_description` → `short_description`) plus `og:image` (cover URL). Make the `<title>`
  and `<meta name="description">` in `resources/views/components/layouts/app.blade.php` fall
  back to optional `$title`/`$metaDescription` variables (defaulting to the current
  hardcoded values) so a page can override them. OpenGraph/`og:image` tags are emitted from
  the show page via the existing `@push('styles')` block, which already renders inside
  `<head>` — no new stack required.

### Part 4 — Testing

- **Unit — `tests/Unit/HtmlSanitizerTest.php`:**
  - `style="text-align:center"` survives sanitization.
  - Disallowed properties (e.g. `position:fixed`, `background:url(...)`) are stripped.
  - Dangerous values (`javascript:`, `expression`, `@import`) are stripped.
  - `<script>` / `onerror` / event handlers still removed (regression guard).
  - In-content `<img>` retains `src`/`alt`/`width`/`height`.
- **Unit — `tests/Unit/Models/NewsTest.php`:** reading-time / related-news helpers if placed
  on the model.
- **Feature — `tests/Feature/Livewire/ShowNewsTest.php`:**
  - Published article renders title, cover, body, metadata (date/views/reading time), tags.
  - Related-news section shows tag/category matches and excludes the current article.
  - Unpublished article 404s for guests; view tracking unaffected.
  - Missing cover renders no broken image.

Run affected tests with `php artisan test --compact --filter=...` after each change.

## Out of Scope (YAGNI)

- Comments and likes.
- Changes to the auto-publish / scheduling logic.
- New dependencies or framework/package upgrades.
- Admin form redesign (only the `content_css` addition to `tinymce.init`).

## Decisions (confirmed)

1. Fidelity mechanism: **curated inline-`style` allowlist** (not TinyMCE→CSS classes).
2. Metadata bar extras: **view count + reading time + share buttons ON; author name OFF.**
3. Related-news selection: **shared tags, falling back to same category.**

## Files Touched (anticipated)

- `app/Support/HtmlSanitizer.php` — style allowlist.
- `app/Livewire/ShowNews.php` — eager loads, related news, reading time, SEO data.
- `resources/views/livewire/show-news.blade.php` — full layout rebuild.
- `public/css/news-article.css` — new prose stylesheet.
- `resources/views/livewire/admin/news/form.blade.php` — TinyMCE `content_css`.
- `resources/views/components/layouts/app.blade.php` — dynamic title/description/OG.
- Tests: `tests/Unit/HtmlSanitizerTest.php`, `tests/Feature/Livewire/ShowNewsTest.php`
  (and `tests/Unit/Models/NewsTest.php` if helpers live on the model).
