# Dark Mode Overhaul Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Ship the branded-navy dark mode across the entire public site per `docs/superpowers/specs/2026-07-13-dark-mode-overhaul-design.md` — fixing all 79 audited defects from one token-based `public/css/dark.css` override layer plus three small blade changes.

**Architecture:** All dark styling lives in `public/css/dark.css`, loaded last in the public layout head and scoped entirely under `html[data-theme=dark]` — light mode and the admin panel cannot regress by construction. A pre-paint inline script eliminates the light flash. The Meridian custom pages flip via one `--na-*` token re-declaration; vendor surfaces, chrome, and the search modal get targeted overrides consuming the same 13 `--dk-*` tokens.

**Tech Stack:** Laravel 12 blade layout, plain CSS (no build step — `public/css/*` is served directly, version-stamped with `filemtime`), Pest 3 feature tests.

**⚠️ State note:** Tasks 1–2 were applied to the working tree and adversarially verified during plan preparation (drafting subagents applied their sections; verifiers corrected them in place, ran Pint, and confirmed 3 passing feature tests + an 11-test regression filter). Their steps below are checked except the commits — execution starts by re-verifying and committing that landed work. Tasks 3–6 append verified CSS to `dark.css`; every selector below has had its light-rule existence, page presence (curl), and specificity win independently verified by a second agent.

**dark.css section order** (ties within the file resolve to later sections — the documented overlaps below rely on this): token block → story dividers (already present) → Meridian re-token (Task 3) → vendor retint (Task 4) → chrome (Task 5) → search modal + plates (Task 6).

---

### Task 1: Layout integration — pre-paint script, dark.css link, story dividers, feature test ✅ (applied; commit pending)

**Files:**
- Create: `public/css/dark.css` (token block + story-divider rule — already on disk)
- Modify: `resources/views/components/layouts/app.blade.php` (script after favicon ~line 24; link after `@stack('styles')` ~line 34)
- Modify: `resources/views/livewire/show-all-categories.blade.php` (hr class swap + pushed light styles)
- Test: `tests/Feature/DarkModeLayoutTest.php`

- [x] **Step 1: Feature test** (already on disk, passing)

```php
<?php

describe('Dark mode layout integration', function () {
    it('loads a version-stamped dark.css after the page-level styles stack', function () {
        $html = $this->get('/')->assertStatus(200)->getContent();

        expect($html)->toContain('css/dark.css?v=')
            ->and(strpos($html, 'css/dark.css'))
            ->toBeGreaterThan(strpos($html, 'css/site-pages.css'));
    })->group('feature');

    it('applies the stored theme before the first stylesheet to prevent a light flash', function () {
        $html = $this->get('/')->assertStatus(200)->getContent();

        $prePaintScript = strpos($html, 'echo-theme');
        $firstStylesheet = strpos($html, '<link rel="stylesheet"');

        expect($prePaintScript)->not->toBeFalse()
            ->and($firstStylesheet)->not->toBeFalse()
            ->and($prePaintScript)->toBeLessThan($firstStylesheet);
    })->group('feature');

    it('renders the data-theme attribute the dark styles are scoped to', function () {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('data-theme', false);
    })->group('feature');
});
```

- [x] **Step 2: Layout edits** (already applied)

In `app.blade.php`, after the favicon link and **before** the first stylesheet:

```html
<script>try{var t=localStorage.getItem('echo-theme')||(window.matchMedia('(prefers-color-scheme: dark)').matches?'dark':'light');document.documentElement.setAttribute('data-theme',t)}catch(e){}</script>
```

Immediately after `@stack('styles')`:

```html
<link rel="stylesheet" href="{{ asset('css/dark.css') }}?v={{ filemtime(public_path('css/dark.css')) }}">
```

(The script's logic byte-matches `helper.js:136`, same `echo-theme` key — the toggle handler keeps working; `helper.js` re-applies the identical value on load.)

- [x] **Step 3: dark.css stub** (already on disk — token block below + story-divider rule; Tasks 3–6 append to it)

```css
/* Dark theme — branded navy. Public site only.
   Every rule is scoped under html[data-theme=dark]; light mode is untouched by
   construction. Loaded LAST in the layout head (after @stack('styles')) and
   version-stamped via filemtime. Token system and fix map:
   docs/superpowers/specs/2026-07-13-dark-mode-overhaul-design.md */

html[data-theme=dark] {
    color-scheme: dark;
    --dk-bg:            #0D1321;
    --dk-band:          #111927;
    --dk-surface:       #17202F;
    --dk-surface-2:     #1E2A3D;
    --dk-text:          #E8ECF4;
    --dk-text-muted:    rgba(232,236,244,.65);
    --dk-accent:        #8FAEE0;
    --dk-accent-strong: #A8C2EC;
    --dk-accent-bg:     rgba(143,174,224,.15);
    --dk-border:        rgba(143,174,224,.16);
    --dk-border-strong: rgba(143,174,224,.34);
    --dk-scrim:         rgba(4,8,16,.7);
    --dk-plate:         #E9EDF3;
}

/* ---- Story dividers (show-all-category sidebar) ------------------------ */
/* Bootstrap's base hr rule keeps opacity:.25; at that opacity the .16-alpha
   border token would vanish, so the divider opts out and renders the token
   at its designed strength. */
html[data-theme=dark] .story-divider {
    background-color: var(--dk-border);
    opacity: 1;
}
```

- [x] **Step 4: show-all-categories hr swap** (already applied) — the two inline-styled `<hr>`s became `<hr class="story-divider story-divider--tight">` / `<hr class="story-divider">`, with the light rules in a page-local `@push('styles')` block (matches the open-data blade's style-in-blade convention; spec §5 forbids touching `site-pages.css`):

```html
@push('styles')
    <style>
        /* Story dividers — light mode; replaces the old inline hr styles exactly
           (#4c0505 at Bootstrap's hr opacity .25, first divider pulled up 10px).
           Dark treatment lives in css/dark.css. */
        .story-divider { background-color: #4c0505; }
        .story-divider--tight { margin-top: 10px; }
    </style>
@endpush
```

- [ ] **Step 5: Re-verify**

Run: `php artisan test --compact --filter="DarkModeLayout|ShowAllCategories"`
Expected: all tests pass (deprecation markers are the pre-existing PDO baseline).

Run: `vendor/bin/pint --dirty --format agent`
Expected: no fixes needed in these files.

- [ ] **Step 6: Commit**

```bash
git add public/css/dark.css resources/views/components/layouts/app.blade.php resources/views/livewire/show-all-categories.blade.php tests/Feature/DarkModeLayoutTest.php
git commit -m "feat(dark-mode): token layer, pre-paint theme script, story dividers"
```

---

### Task 2: Open-data blade retoken ✅ (applied; commit pending)

**Files:**
- Modify: `resources/views/livewire/open-data/open-data-index.blade.php` (dark block of the embedded `<style>`, ~lines 95–129)

- [x] **Step 1: Replace the blade-local dark hexes with shared tokens** (already applied). The dark block now reads (complete, as landed):

```css
/* ---- dark mode (shared --dk-* tokens from css/dark.css) ---- */
html[data-theme=dark] .od-catalog { background: var(--dk-bg); color: var(--dk-text); }
html[data-theme=dark] .od-catalog .od-eyebrow { color: var(--dk-accent); }
html[data-theme=dark] .od-catalog .od-title { color: var(--dk-text); }
html[data-theme=dark] .od-catalog .od-rail { background: var(--dk-surface); border-color: var(--dk-border); box-shadow: 0 6px 22px rgba(0,0,0,.3); }
html[data-theme=dark] .od-catalog .od-search input { background: var(--dk-surface); border-color: var(--dk-border-strong); color: var(--dk-text); }
html[data-theme=dark] .od-catalog .od-search input:focus { background: var(--dk-surface); border-color: var(--dk-accent); box-shadow: 0 0 0 3px var(--dk-accent-bg); }
html[data-theme=dark] .od-catalog .od-year { color: var(--dk-text); }
html[data-theme=dark] .od-catalog .od-year .od-count { background: var(--dk-surface-2); color: var(--dk-text-muted); }
html[data-theme=dark] .od-catalog .od-year:hover { background: var(--dk-surface-2); }
html[data-theme=dark] .od-catalog .od-year.is-active { background: var(--dk-accent-bg); color: var(--dk-accent-strong); }
html[data-theme=dark] .od-catalog .od-year.is-active::before { background: var(--dk-accent-strong); }
html[data-theme=dark] .od-catalog .od-year.is-active .od-count { background: var(--dk-accent); color: var(--dk-bg); }
html[data-theme=dark] .od-catalog .od-chip { background: var(--dk-surface); border-color: var(--dk-border-strong); color: var(--dk-text); }
html[data-theme=dark] .od-catalog .od-chip.is-active { background: var(--dk-accent); border-color: var(--dk-accent); color: var(--dk-bg); }
html[data-theme=dark] .od-catalog .od-clear { border-color: var(--dk-border); }
html[data-theme=dark] .od-catalog .od-clear button:hover { color: var(--dk-accent); }
html[data-theme=dark] .od-catalog .od-found { color: var(--dk-text); }
html[data-theme=dark] .od-catalog .od-sort select { background-color: var(--dk-surface); border-color: var(--dk-border-strong); color: var(--dk-text);
    background-image: url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='14' height='14' viewBox='0 0 24 24' fill='none' stroke='%23aeb8de' stroke-width='2.5' stroke-linecap='round' stroke-linejoin='round'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E"); }
html[data-theme=dark] .od-catalog .od-sort select:focus { border-color: var(--dk-accent); box-shadow: 0 0 0 3px var(--dk-accent-bg); }
html[data-theme=dark] .od-catalog .od-card { background: var(--dk-surface); border-color: var(--dk-border); box-shadow: 0 3px 14px rgba(0,0,0,.25); }
html[data-theme=dark] .od-catalog .od-card:hover { border-color: var(--dk-border-strong); box-shadow: 0 10px 26px rgba(0,0,0,.4); }
html[data-theme=dark] .od-catalog .od-name { color: var(--dk-text); }
html[data-theme=dark] .od-catalog .od-meta .yq { color: var(--dk-accent-strong); }
html[data-theme=dark] .od-catalog .od-dl { background: var(--dk-accent); color: var(--dk-bg); }
html[data-theme=dark] .od-catalog .od-dl:hover { background: var(--dk-accent-strong); color: var(--dk-bg); }
html[data-theme=dark] .od-catalog .od-empty { background: var(--dk-surface); border-color: var(--dk-border); color: var(--dk-text-muted); }
html[data-theme=dark] .od-catalog .od-page { color: var(--dk-text); }
html[data-theme=dark] .od-catalog .od-page:hover { background: var(--dk-surface); border-color: var(--dk-border); }
html[data-theme=dark] .od-catalog .od-page.od-nav:hover { color: var(--dk-accent); }
html[data-theme=dark] .od-catalog .od-page.is-current { background: var(--dk-accent); color: var(--dk-bg); }
```

This also fixes the four audited gaps: `.od-eyebrow` (new rule), `.od-page.od-nav:hover` (new rule at (0,5,0) > light (0,4,0)), `.od-clear button:hover` (new rule), `.od-sort select:focus` (new rule after the resting rule, same specificity → source order wins). The three `rgba(0,0,0,…)` shadows are pre-existing values carried over (shadows have no token; they read correctly on dark).

- [ ] **Step 2: Re-verify**

Run: `php artisan test --compact --filter=OpenData`
Expected: existing open-data tests pass.

Run: `curl -s http://localhost/open-data | grep -c 'var(--dk-'`
Expected: ≥ 30 (the retokened block renders).

- [ ] **Step 3: Commit**

```bash
git add resources/views/livewire/open-data/open-data-index.blade.php
git commit -m "refactor(dark-mode): open-data page consumes shared dark tokens, fix 4 dark gaps"
```

---

### Task 3: Meridian re-token — flips the ten broken custom pages

**Files:**
- Modify: `public/css/dark.css` (append at end)

- [ ] **Step 1: Append this section** (verified: every selector's light rule read at the cited line; wins vs the `@push`'d `news-article.css`/`site-pages.css` are strict specificity wins, not load-order ties)

```css
/* ---- Meridian re-token (spec fix #1) ----------------------------------- */
/* news-article.css + site-pages.css consume the --na-* tokens 136 times;
   re-declaring the palette under dark scope flips all ten Meridian pages
   in one block. html[data-theme=dark] (0,1,1) beats the light :root
   (0,1,0) declarations (news-article.css:37-54) regardless of load order.
   --na-navy-deep and the font/measure tokens are untouched by design.
   TinyMCE is unaffected: its iframe html never carries data-theme=dark. */
html[data-theme=dark] {
    --na-navy: var(--dk-text);
    --na-azure: var(--dk-accent);
    --na-azure-dark: var(--dk-accent-strong);
    --na-ink: var(--dk-text);
    --na-slate: var(--dk-text-muted);
    --na-mist: var(--dk-band);
    --na-hair: var(--dk-border);
    --na-paper: var(--dk-bg);
    --na-favorable: #45B58C; /* lightened data colors — the spec §2 exception */
    --na-adverse: #E07B63;
}

/* ---- Card surfaces: one elevation step above the --dk-bg sheet -------- */
html[data-theme=dark] .news-card,
html[data-theme=dark] .contact-card,
html[data-theme=dark] .leader-card,
html[data-theme=dark] .related-card {
    background: var(--dk-surface);
}
html[data-theme=dark] .news-card:hover {
    box-shadow: 0 14px 30px -18px var(--dk-scrim);
}
html[data-theme=dark] .related-card:hover {
    box-shadow: 0 12px 26px -16px var(--dk-scrim);
}

/* ---- Thumb placeholders: their navy fills would remap near-white ------ */
html[data-theme=dark] .news-card .nc-thumb,
html[data-theme=dark] .related-card .rc-thumb,
html[data-theme=dark] .vc-thumb,
html[data-theme=dark] .video-stage {
    background: var(--dk-surface-2);
}

/* ---- Residual literals that bypass the tokens ------------------------- */
html[data-theme=dark] .article-lead {
    color: var(--dk-text-muted);
}
html[data-theme=dark] .news-card .nc-excerpt {
    color: var(--dk-text-muted);
}
html[data-theme=dark] .article-tag {
    border-color: var(--dk-border-strong);
}
html[data-theme=dark] .news-article-body thead th,
html[data-theme=dark] .news-article-body table > tr:first-child th {
    background: var(--dk-surface-2);
    color: var(--dk-text);
}
html[data-theme=dark] .news-article-body td,
html[data-theme=dark] .news-article-body tbody th {
    border-top-color: var(--dk-border);
}
html[data-theme=dark] .news-article-body tbody tr:nth-child(even) td {
    background: var(--dk-band);
}

/* ---- Contact page ------------------------------------------------------ */
html[data-theme=dark] .contact-card .cc-ic {
    background: var(--dk-surface-2);
}
html[data-theme=dark] .cc-social a {
    background: var(--dk-accent-bg);
    color: var(--dk-accent);
}
html[data-theme=dark] .cc-social a:hover {
    background: var(--dk-accent);
    color: var(--dk-bg);
}

/* ---- "Load more" buttons (search, videos) ------------------------------ */
/* After the remap the light hover (site-pages.css:175) becomes near-white
   on near-white; resting border --na-navy would glare. Accent-fill hover
   per spec §2 (dark text on accent). */
html[data-theme=dark] .sp-more-btn {
    background: var(--dk-surface);
    border-color: var(--dk-border-strong);
    color: var(--dk-text);
}
html[data-theme=dark] .sp-more-btn:hover {
    background: var(--dk-accent);
    border-color: var(--dk-accent);
    color: var(--dk-bg);
}

/* ---- Video play discs: dark glyph on the light disc / accent fill ----- */
html[data-theme=dark] .vc-play span {
    color: var(--dk-bg);
}
html[data-theme=dark] .video-card:hover .vc-play span {
    color: var(--dk-bg);
}

/* ---- Search input focus (the light rule brightens mist -> paper) ------ */
html[data-theme=dark] .search-box-input:focus {
    background: var(--dk-surface);
}

/* ---- Home "studio band": keep it navy, keep light-on-dark text -------- */
/* Inside .home-videos the light design already IS dark: --na-navy paints
   the band and --na-paper paints the text. Re-scope both so the global
   remap doesn't wash the band near-white; --na-navy-deep (#0a2340,
   unmapped) restores the navy without introducing a new literal. */
html[data-theme=dark] .home-videos {
    --na-navy: var(--na-navy-deep);
    --na-paper: var(--dk-text);
}
html[data-theme=dark] .hv-feature:hover .hv-disc,
html[data-theme=dark] .hv-feature:focus-visible .hv-disc,
html[data-theme=dark] .hv-row:hover .hv-disc,
html[data-theme=dark] .hv-row:focus-visible .hv-disc {
    color: var(--dk-bg);
}
```

Notes locked in by the verifier: the vendor dark rules `html[data-theme=dark] body h1…/p` correctly paint Meridian headings/prose near-white on the now-dark sheets — no extra rules needed. The search-modal close-chip hover that consumes `var(--na-azure)` (style.css:11550) is handled in Task 6 (do **not** duplicate it here). Prose links inside `ul li` lose azure to the vendor `ul li a` rule — restored by Task 4's accent work only where reachable; the `.cc-social`/`.hv-row`/`.popular-item` anchors are not inside `ul/li` and are unaffected.

- [ ] **Step 2: Verify**

Run: `php artisan test --compact --filter=DarkModeLayout`
Expected: 3 pass.

Run: `curl -s http://localhost/about | grep -o 'css/dark.css?v=[0-9]*' | head -1`
Expected: a fresh version stamp (cache busts automatically via filemtime).

Guardrails: `grep -c '!important' public/css/dark.css` → `0`; `awk '/^[a-zA-Z.#\[:*]/ && !/^html\[data-theme=dark\]/' public/css/dark.css` → no output (every top-level selector dark-scoped).

- [ ] **Step 3: Commit**

```bash
git add public/css/dark.css
git commit -m "feat(dark-mode): re-token Meridian design system — flips the ten custom pages"
```

---

### Task 4: Vendor retint + accent restoration

**Files:**
- Modify: `public/css/dark.css` (append at end)

- [ ] **Step 1: Append this section** (every style.css citation verified; dark.css loads after style.css so identical selectors win on order)

```css
/* ---- Vendor retint — navy surfaces (spec §3.2) -------------------------
   Overrides the vendor's own html[data-theme=dark] rules (style.css
   ~17839-18090). dark.css loads after style.css, so identical selectors win
   on source order; the light rules cited per block are lower-specificity. */

/* Page background — style.css:17839 paints body var(--color-heading-1) #181823. */
html[data-theme=dark] body {
    background: var(--dk-bg);
}

/* Full-width sections + desktop menu bar — style.css:17890-17899 paints them
   var(--bg-dark-three) #0f0f1c (darker than the page: inverted elevation). */
html[data-theme=dark] body .echo-latest-news-area,
html[data-theme=dark] body .echo-site-main-logo-menu-social {
    background: var(--dk-band);
}

/* Category-page section wrapper — style.css:17925 → --bg-dark-three. */
html[data-theme=dark] body .echo-hero-section.inner {
    background: var(--dk-band);
}

/* Sticky menu bar — style.css:15771 (dark → --bg-dark-three), 15764 (light
   #193a51); .header-sticky is added to .echo-header-area by helper.js:97.
   Needed at (0,4,2): the light sticky rule (0,3,0) outranks the generic
   menu-bar retint above. */
html[data-theme=dark] body.home-one .header-sticky .echo-site-main-logo-menu-social {
    background: var(--dk-band);
}

/* Sidebar "top story" block — style.css:17914 painted it --bg-dark-three; in
   light mode it is a card (#F9F9F9 at 18154, .bg-right-side #eeecec at 18159),
   so in dark it sits one elevation step above the section band. */
html[data-theme=dark] body .echo-home-1-hero-area-top-story {
    background: var(--dk-surface);
}

/* Breadcrumb band — style.css:17933-17938 multiplies #181823 into the light
   breadcrumb.png (set at 11354-11359). Flat band instead: image off, blend
   off, navy band. */
html[data-theme=dark] body .echo-breadcrumb-area {
    background-color: var(--dk-band);
    background-image: none;
    background-blend-mode: normal;
}

/* Desktop dropdown panel — style.css:17961 → var(--color-heading-1) #181823;
   light panel #ffffff at 15837, its 3px border-top keeps invisible brand
   navy (15838) — retinted to the strong border token. */
html[data-theme=dark] body .echo-site-main-logo-menu-social ul.echo-desktop-menu li.echo-has-dropdown ul.echo-submenu {
    background: var(--dk-surface);
    border-top-color: var(--dk-border-strong);
}

/* Footer — permanently #181823 via style.css:30107-30110; the vendor dark rule
   (17922) targets .echo-footer-area.footer-2, a class this footer never has,
   so the retint must target the base class. Dark scope only: the light-mode
   footer keeps its designed dark plum. */
html[data-theme=dark] body .echo-footer-area {
    background-color: var(--dk-band);
}

/* ---- Accent restoration (spec §3.7) ------------------------------------ */

/* Vendor dark link system = white slide-in underline (style.css:17939-17947)
   + white hover text (17948-17955). Rebrand to the accent pair so hover reads
   as brand, not glare. */
html[data-theme=dark] body .title-hover {
    background-image: linear-gradient(to right, var(--dk-accent) 50%, transparent 50%);
}
html[data-theme=dark] body .title-hover:hover {
    color: var(--dk-accent-strong);
}

/* Desktop submenu hover — light-only var(--color-primary) #3a4c7a ≈2.1:1 on
   the dark panel (style.css:15866); the slide-in dot (15873-15884) uses the
   same invisible primary. */
html[data-theme=dark] .echo-site-main-logo-menu-social ul.echo-desktop-menu li.echo-has-dropdown ul.echo-submenu .nav-item:hover a {
    color: var(--dk-accent);
}
html[data-theme=dark] .echo-site-main-logo-menu-social ul.echo-desktop-menu li.echo-has-dropdown ul.echo-submenu .nav-item::before {
    background: var(--dk-accent);
}

/* Prose list links — the vendor rule html[data-theme=dark] body ul li a
   ((0,1,5), style.css:17849) out-ranks .news-article-body a ((0,1,1)),
   leaving authored-list links unbranded near-white at rest while their
   hover flips to the accent family. Restore the accent pair. */
html[data-theme=dark] body .news-article-body li a {
    color: var(--dk-accent);
}
html[data-theme=dark] body .news-article-body li a:hover {
    color: var(--dk-accent-strong);
}

/* ---- Small vendor components (spec §3.8, partial) ----------------------- */

/* Carousel prev/next arrows — #5E5E5E ≈2.9:1 on the band, gray hover fill
   (style.css:18359-18424). */
html[data-theme=dark] .echo-latest-news-area .echo-latest-news-content .echo-be-slider-btn .echo-latest-news-next-prev-btn .swiper-button-next,
html[data-theme=dark] .echo-latest-news-area .echo-latest-news-content .echo-be-slider-btn .echo-latest-news-next-prev-btn .swiper-button-prev {
    color: var(--dk-text-muted);
}
html[data-theme=dark] .echo-latest-news-area .echo-latest-news-content .echo-be-slider-btn .echo-latest-news-next-prev-btn .swiper-button-next:hover,
html[data-theme=dark] .echo-latest-news-area .echo-latest-news-content .echo-be-slider-btn .echo-latest-news-next-prev-btn .swiper-button-prev:hover {
    background-color: var(--dk-surface-2);
    color: var(--dk-text);
}

/* Form fields — the vendor's dark form rule (style.css:17862-17877) recolors
   text only, leaving light fills/borders. Mirrors the vendor's group minus
   `button`, which other dark rules style deliberately. */
html[data-theme=dark] body .input,
html[data-theme=dark] body select,
html[data-theme=dark] body textarea {
    background-color: var(--dk-surface);
    border-color: var(--dk-border-strong);
}

/* Focus companion: the resting rule above out-ranks the light :focus rule
   (style.css:9424-9433, (0,1,1), border → --color-primary) — and outline is
   already none there — so without this, focused fields would show no focus
   state at all. Blade-local rules (e.g. od-sort focus, (0,4,2)) still win. */
html[data-theme=dark] body .input:focus,
html[data-theme=dark] body select:focus,
html[data-theme=dark] body textarea:focus {
    border-color: var(--dk-accent);
}
```

Verified dead code deliberately **not** overridden (elements render on no page, no blade, no public JS): `.echo-popup-model` scrim, the newsletter subscribe widget, `ul.mega-menu`, `.echo-breadcrumb-area-2`, and the `#D00032` hover (gated on `.header-ten .header-three`, unreachable — this closes the spec's #D00032 finding as dead code).

- [ ] **Step 2: Verify**

Run: `php artisan test --compact --filter=DarkModeLayout` → 3 pass.
Guardrails from Task 3 Step 2 → same expected output.

- [ ] **Step 3: Commit**

```bash
git add public/css/dark.css
git commit -m "feat(dark-mode): retint vendor surfaces to navy family, restore brand accent"
```

---

### Task 5: Chrome — mobile menu, language switchers, social icons, test notice

**Files:**
- Modify: `public/css/dark.css` (append at end)

- [ ] **Step 1: Append this section** (markup verified in `app.blade.php` — site-notice L40, desktop lang L53-58, mobile popup L78-83, social L91-96, mobile menu L216-258)

```css
/* ---- Header / chrome — every page (spec §3.3, §3.4) -------------------- */

/* Mobile hamburger menu (style.css:34108-34190). Panel is hardcoded #fff
   while the vendor dark rule html[data-theme=dark] body ul li a
   (style.css:17848, (0,1,5)) turns the links near-white — white-on-white.
   Every color rule below carries at least (0,2,2), out-ranking both the
   light rules and that vendor rule. */
html[data-theme=dark] .echo-mobile-menu-wrapper {
    background-color: var(--dk-surface);
}
html[data-theme=dark] .echo-mobile-menu-content ul li {
    border-bottom-color: var(--dk-border);
}
html[data-theme=dark] .echo-mobile-menu-content a {
    color: var(--dk-text);
}
html[data-theme=dark] .echo-mobile-menu-content a:hover {
    color: var(--dk-accent);
}
html[data-theme=dark] .mobile-dropdown ul {
    background-color: var(--dk-surface-2);
}
html[data-theme=dark] .echo-mobile-menu-content .mobile-dropdown ul li a {
    color: var(--dk-text-muted);
}
html[data-theme=dark] .echo-mobile-menu-content .mobile-dropdown ul li a:hover {
    color: var(--dk-accent);
}

/* ---- Desktop language switcher (style.css:34200-34214) ---------------- */
html[data-theme=dark] .language-dropdown a {
    color: var(--dk-text-muted);
}
html[data-theme=dark] .language-dropdown a:hover {
    color: var(--dk-accent-strong);
}
html[data-theme=dark] .language-dropdown a.active {
    color: var(--dk-accent);
}

/* ---- Mobile language toggle + popup (style.css:34222-34270) ----------- */
html[data-theme=dark] .lang-toggle-btn {
    color: var(--dk-text-muted);
}
html[data-theme=dark] .lang-toggle-btn:hover {
    background: var(--dk-accent-bg);
}
html[data-theme=dark] .mobile-lang-options {
    background: var(--dk-surface);
    border: 1px solid var(--dk-border);
    box-shadow: none;
}
html[data-theme=dark] .mobile-lang-options a {
    color: var(--dk-text-muted);
}
html[data-theme=dark] .mobile-lang-options a:hover {
    background: var(--dk-surface-2);
    color: var(--dk-accent-strong);
}
html[data-theme=dark] .mobile-lang-options a.active {
    background: var(--dk-accent-bg);
    color: var(--dk-accent);
}

/* ---- Header social icon pills (style.css:34283-34298) ----------------- */
html[data-theme=dark] .echo-home-1-social-media-icons .list-group-item a {
    background: var(--dk-accent-bg);
    color: var(--dk-text-muted);
}
html[data-theme=dark] .echo-home-1-social-media-icons .list-group-item a:hover {
    background: var(--dk-accent);
    color: var(--dk-bg);
}

/* ---- Test-mode notice (style.css:34433-34454, spec §3.4) --------------
   Amber dot (::before, #d99a06) is kept from the light rule untouched;
   the amber left border is the permitted existing literal. */
html[data-theme=dark] .site-notice {
    background: var(--dk-surface);
    border-bottom: 1px solid var(--dk-border);
    border-left: 3px solid #d99a06;
    color: var(--dk-text-muted);
}
html[data-theme=dark] .site-notice strong {
    color: var(--dk-text);
}

/* ---- Footer social icons on hover (style.css:30154-30161) -------------
   The light hover fills with brand navy #3a4c7a — ≈2.1:1 on the dark
   footer band. Accent fill with dark glyph instead. */
html[data-theme=dark] .echo-footer-area .echo-row .echo-footer-content-1 .echo-footer-address .echo-footer-social-media a:hover {
    background-color: var(--dk-accent);
}
html[data-theme=dark] .echo-footer-area .echo-row .echo-footer-content-1 .echo-footer-address .echo-footer-social-media a:hover i {
    color: var(--dk-bg);
}
```

(One normalization vs the drafted section, per spec §2's accent-fill rule: the social-pill hover glyph is `var(--dk-bg)` (~8.2:1 on accent), not `var(--dk-surface-2)`. The footer-social hover pair was added from the Task 4 quality review — the light hover fill uses the brand navy that fails contrast on every dark surface.)

- [ ] **Step 2: Verify**

Run: `php artisan test --compact --filter=DarkModeLayout` → 3 pass.
Guardrails from Task 3 Step 2 → `!important` count is 0; the awk scoping check prints nothing; the only hex literals below the token block are `#d99a06`, `#45B58C`, `#E07B63` (plus hexes inside comments).

- [ ] **Step 3: Commit**

```bash
git add public/css/dark.css
git commit -m "feat(dark-mode): fix chrome — mobile menu, language switchers, social icons, test notice"
```

---

### Task 6: Search modal retoken + deliberate light plates

**Files:**
- Modify: `public/css/dark.css` (append at end)

- [ ] **Step 1: Append this section** (verified: home renders 1 modal, 8 `.hp-cell`; `/structure` renders exactly one `.echo-hero` with the lex.uz img)

```css
/* ---- Search modal retoken (spec §3.5) + light plates (spec §4) ---------
   Retires the modal's private blue-slate family (style.css:34468-34486).
   dark.css loads after style.css, so identical selectors win by order;
   rules with no dark counterpart out-specify the light originals. */
html[data-theme=dark] body .search-input-area {
    background: var(--dk-scrim);
}
html[data-theme=dark] body .search-input-area .search-input-inner {
    background: var(--dk-surface);
    border: 1px solid var(--dk-border);
}
html[data-theme=dark] body .search-input-area .search-input-inner:focus-within {
    border-color: var(--dk-accent);
    box-shadow: 0 0 0 3px var(--dk-accent-bg);
}
html[data-theme=dark] body .search-input-area .search-input-inner input {
    color: var(--dk-text);
}
html[data-theme=dark] body .search-input-area .search-input-inner input::placeholder {
    color: var(--dk-text-muted);
}
html[data-theme=dark] body .search-input-area .search-input-inner .search-close-icon i {
    background: var(--dk-surface-2);
    color: var(--dk-text-muted);
}
html[data-theme=dark] body .search-input-area .search-input-inner .search-close-icon i:hover {
    background: var(--dk-accent);
    color: var(--dk-bg);
}

/* ---- Home journals + partners: dark band, light logo plates ------------
   Section background/heading flip via the --na-* re-declare (Task 3); the
   explicit band rule below makes the pair self-contained. Partner tiles
   stay deliberately light (--dk-plate) — logos are colored artwork. */
html[data-theme=dark] .home-journals,
html[data-theme=dark] .home-partners {
    background: var(--dk-bg);
}
html[data-theme=dark] .hj-item:hover .hj-cover,
html[data-theme=dark] .hj-item:focus-visible .hj-cover {
    box-shadow: 0 14px 30px -18px var(--dk-scrim);
}
html[data-theme=dark] .hp-wall {
    background: var(--dk-border);
    border-color: var(--dk-border);
}
html[data-theme=dark] .hp-cell,
html[data-theme=dark] .hp-cell:hover,
html[data-theme=dark] .hp-cell:focus-visible {
    background: var(--dk-plate);
}
html[data-theme=dark] .hp-cell:focus-visible {
    outline-color: var(--dk-surface-2);
}
html[data-theme=dark] .hp-cell .hp-name {
    color: var(--dk-bg);
}

/* ---- Structure page: org chart on a document plate ---------------------
   The hotlinked lex.uz GIF is opaque white; a --dk-plate mat frames it as
   a document card instead of a raw white slab on the dark page. */
html[data-theme=dark] .echo-hero img[src^="https://lex.uz"] {
    background: var(--dk-plate);
    border: 1px solid var(--dk-border);
    border-radius: 12px;
    padding: clamp(14px, 3vw, 28px);
}
```

Accepted nuances (verifier-signed): the modal's magnifier icon keeps the vendor's data-URI SVG stroke (retokening it would need a new literal inside a data URI); `.hp-name`/`outline-color` use dark tokens as ink on the light plate — a sanctioned token-role stretch in lieu of minting a plate-ink token; the org-chart hook keys off the lex.uz URL (a true dark asset is the spec's follow-up).

- [ ] **Step 2: Verify**

Run: `php artisan test --compact --filter=DarkModeLayout` → 3 pass.
Guardrails from Task 5 Step 2 → same expected output.

- [ ] **Step 3: Commit**

```bash
git add public/css/dark.css
git commit -m "feat(dark-mode): retoken search modal, partner logo plates, org-chart document plate"
```

---

### Task 7: Full-suite gate + format

**Files:** none new

- [ ] **Step 1: Run the full test suite**

Run: `php artisan test --compact`
Expected: everything green (pre-existing deprecation markers aside). If anything fails, stop and fix before proceeding.

- [ ] **Step 2: Pint**

Run: `vendor/bin/pint --dirty --format agent`
Expected: no violations (CSS is untouched by Pint; this guards the blade/test files).

- [ ] **Step 3: Commit (only if Pint changed anything)**

```bash
git add -u && git commit -m "style(dark-mode): pint"
```

---

### Task 8: Dark-mode verification walk

**Files:** none — verification against the spec's Appendix checklist

- [ ] **Step 1: Automated spot checks** — for each URL below, confirm HTTP 200 and that `css/dark.css?v=` is present after the pushed page styles:

```bash
for p in / /about /contact /history /leadership /open-data "/search?query=iqtisodiyot" /show-all-category /show-category/mulohaza /structure /vacancies /videos /videos/4; do
  echo "== $p: $(curl -s -o /dev/null -w '%{http_code}' "http://localhost$p")"
done
```

Expected: thirteen `200`s.

- [ ] **Step 2: Browser walk (user or agent with a browser)** — toggle dark mode via the header switch and check each page against the spec Appendix, desktop and <992px:
  - every former "white sheet" page is now navy (`#0D1321` page, `#17202F` cards)
  - mobile hamburger menu: readable light text on dark panel
  - language switcher + social icons legible in the header
  - test-notice strip: dark with amber left border
  - search modal matches the page behind it
  - partner logos + org chart sit on deliberate light plates
  - hard-reload with dark stored: no light flash
  - flip back to light mode on 3–4 pages: identical to before this work

- [ ] **Step 3: Mark complete** — check off remaining boxes, then use superpowers:finishing-a-development-branch.
