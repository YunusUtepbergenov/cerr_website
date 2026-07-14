# Dark Mode Overhaul — Design

**Date:** 2026-07-13
**Scope:** Public website only (admin panel untouched). Light mode untouched by construction.
**Decisions made with user:** full overhaul (fix broken pages + redesign palette) · palette direction "Branded navy" · keep current theme-selection behavior (system preference on first visit, header toggle remembered) but fix the light flash · implementation as one dedicated `public/css/dark.css` override layer.

## 1. Background

Dark mode is toggled by the vendor "Echo" theme: `data-theme` on `<html>` (server renders `data-theme="light"`), `public/js/helper.js` applies the stored/system theme after load and handles the header toggle (`localStorage` key `echo-theme`). Dark styling exists only as `html[data-theme=dark]` rules in the vendor's `public/css/style.css` (~189 rules).

A 31-agent audit (15 page auditors + 15 verifiers + palette analyst) of all 14 public pages plus the shared chrome found **79 verified defects**, which collapse into a small set of root causes:

- The custom "Meridian" design system (`public/css/news-article.css` + `public/css/site-pages.css`, 136 uses of `--na-*` tokens) has **zero dark rules**. Ten pages are broken: about, contact, history, leadership, search, category, news article, vacancies, videos index, video show — each renders as a full-page white sheet (`--na-paper: #ffffff`), with vendor dark rules leaking white text onto it (e.g. `html[data-theme=dark] body h1` makes `.article-title` white-on-white — invisible).
- **Site-wide chrome defects** on every page: the mobile hamburger menu is unreadable (panel hardcoded `#fff` at `style.css:34108` while the vendor rule `html[data-theme=dark] body ul li a` (0,1,5) recolors its links to `rgba(255,255,255,.8)` — near-white on white); desktop language switcher `#555` on `#181823` (~2.3:1, `style.css:34200`); header social icons nearly invisible (`style.css:34283`); mobile language popup hardcoded white (`style.css:34239`); amber test-notice strip clashes (`style.css:34433`).
- **Vendor dark palette is incoherent** (palette analyst, with computed WCAG ratios): four unrelated surface families (blue-violet `#181823`/`#0f0f1c`, dead neutral-gray tokens `--bg-dark-one/two` defined but never used, blue-slate search modal `#16202e`/`#223146`, open-data's private near-misses `#0f0f17`/`#1b1b25`); elevation inverted (sections darker than page, cards identical to page — 1.08:1 step); brand primary `#3a4c7a` fails on every dark surface (≈2.1:1, below even the 3:1 non-text minimum), so dark mode falls back to all-white links and loses the brand; hard failures like `#D00032` dropdown hover (3.37:1) and `#FFFFFF45` separators (2.46:1).
- The **open-data page already does it right** in isolation (`resources/views/livewire/open-data/open-data-index.blade.php:95-122`: correct elevation, accents `#6b7bb0`/`#5a6aa0`/`#aeb8de` passing contrast) — but as blade-local hex, invisible to the rest of the site.

The full verified inventory is in the Appendix and doubles as the implementation/verification checklist.

## 2. Token system — `public/css/dark.css`

One new stylesheet containing the entire dark theme. Every rule scoped under `html[data-theme=dark]`. Palette: branded navy, hue ≈223 — the same family as brand `#3a4c7a` (and coincidentally the existing search modal), replacing the template's purple.

```css
html[data-theme=dark] {
    color-scheme: dark;
    --dk-bg:            #0D1321;                    /* page background */
    --dk-band:          #111927;                    /* full-width alternate sections, header surfaces */
    --dk-surface:       #17202F;                    /* cards, panels, modals, inputs */
    --dk-surface-2:     #1E2A3D;                    /* hover / raised / active */
    --dk-text:          #E8ECF4;                    /* headings & body — ≥11:1 on all surfaces (AAA) */
    --dk-text-muted:    rgba(232,236,244,.65);      /* meta, captions, secondary */
    --dk-accent:        #8FAEE0;                    /* links, active nav, icons — ≈7:1 on --dk-bg */
    --dk-accent-strong: #A8C2EC;                    /* hover state of accent */
    --dk-accent-bg:     rgba(143,174,224,.15);      /* tags, chips, active fills */
    --dk-border:        rgba(143,174,224,.16);      /* hairlines, card borders */
    --dk-border-strong: rgba(143,174,224,.34);      /* inputs, interactive component borders */
    --dk-scrim:         rgba(4,8,16,.7);            /* the single modal/overlay scrim */
    --dk-plate:         #E9EDF3;                    /* deliberate light plates (logos, documents) */
}
```

Rules:

- Elevation is monotonic: page `--dk-bg` < band `--dk-band` < surface `--dk-surface` < raised `--dk-surface-2`. This replaces the vendor's inverted hierarchy.
- Exactly two border strengths, one scrim. No new hex values outside this block — every override below consumes these tokens.
- Semantic colors get dark-legible variants where used (success/danger/warning lightened ~1 step; the `--na-favorable`/`--na-adverse` data colors likewise).
- Buttons filled with `--dk-accent` use dark text (`#0D1321`) for contrast.

**Loading:** linked in `resources/views/components/layouts/app.blade.php` immediately **after** `@stack('styles')` (line 32) so it is the last stylesheet, version-stamped in the existing pattern: `?v={{ filemtime(public_path('css/dark.css')) }}`. All selectors carry the `html[data-theme=dark]` prefix (+1 attribute, +1 type specificity), which beats the light rules they override; where a light rule still wins (documented cases in the Appendix, e.g. `.od-catalog .od-page.od-nav:hover` at (0,4,0)), the dark selector is written to exceed it — no `!important` unless overriding an inline style.

## 3. Fix map (~79 findings → 9 fixes)

1. **Re-declare the `--na-*` palette under dark scope.** `news-article.css` and `site-pages.css` consume these tokens 136 times, so one block flips all ten broken pages:
   `--na-paper`→`var(--dk-bg)` (the full-page sheets read as page background), `--na-ink`→`--dk-text`, `--na-slate`→`--dk-text-muted`, `--na-mist`→`--dk-band`, `--na-hair`→`--dk-border`, `--na-navy`→`--dk-text` (as heading color on dark), `--na-azure`→`--dk-accent`, `--na-azure-dark`→`--dk-accent-strong`, `--na-favorable`/`--na-adverse`→ lightened variants. Font/measure tokens untouched. Card-like components that also consume `--na-paper` (`.news-card`, `.contact-card`, `.popular-item` context, etc.) get explicit `--dk-surface` overrides so they sit one elevation step above the sheet.
   *TinyMCE is unaffected:* the editor loads `news-article.css` inside its own iframe whose `<html>` never has `data-theme=dark`, and `dark.css` is not in its `content_css`.
   Residual hardcoded values that don't go through tokens (e.g. `.news-card` hover shadow `rgba(14,44,78,.45)`, `.article-more` band) get explicit token overrides.
2. **Retint vendor dark surfaces to the navy family.** Override the vendor's dark background rules (~20 declarations): body `#181823`→`--dk-bg`, sections/footer/mega-menu `#0f0f1c`→`--dk-band`, the reachable sticky-header rule (style.css:15771, `body.home-one`; the `#191A23` variants are home-three dead code)→`--dk-band`, breadcrumb `background-blend-mode: multiply` literal→`--dk-band`, card/submenu surfaces→`--dk-surface`, popup scrims (`rgba(0,0,0,.9)` and `rgba(2,10,20,.6)`)→`--dk-scrim`.
3. **Fix the chrome (every page).** Mobile hamburger panel `#fff`→`--dk-surface`, row borders→`--dk-border`, submenu fill→`--dk-surface-2`, link color→`--dk-text` (out-specifying the vendor `ul li a` rule); desktop language switcher `#555`→`--dk-text-muted`, active→`--dk-accent`; social icon pills `rgba(0,0,0,.05)`→`--dk-accent-bg`, glyphs→`--dk-text-muted`; mobile lang toggle + white popup→`--dk-surface` with token text/active states.
4. **Test-mode notice**: dark variant — `--dk-surface` background, `--dk-text-muted` text, with the dot and a new `border-left: 3px solid` reusing the strip's existing amber `#d99a06` (an existing project color, passes ≥3:1 on `--dk-surface`) so it still reads as a warning without glaring.
5. **Search modal**: retoken its private blue-slate family (scrim, panel `#16202e`→`--dk-surface`, chips `#223146`→`--dk-surface-2`, text→tokens). Visually near-identical; kills family #3.
6. **Open-data page**: replace the blade-local dark hexes (lines 95-122) with the shared tokens and fix its four verified gaps (`.od-eyebrow` color, pagination arrow hover, clear-filters hover, sort-select focus border). Behavior unchanged.
7. **Accent restoration**: vendor dark link treatment (white text + white gradient underline) → `--dk-accent` with matching underline; `#D00032` dropdown hover → `--dk-accent-strong`; desktop submenu hover `#3a4c7a` (invisible on dark, `style.css:15866`) → `--dk-accent`.
8. **Small components**: carousel prev/next arrows `#5E5E5E`→`--dk-text-muted`; load-more buttons (`.sp-more-btn`) → `--dk-surface` fill, `--dk-text`, `--dk-border-strong`; form inputs/selects/textareas → `--dk-surface` + `--dk-border-strong`; `.echo-home-1-hero-area-top-story` sidebar `<hr>` separators (inline `background-color:#4c0505` in `resources/views/livewire/show-all-categories.blade.php:49,63`) — replace the inline style with a class in the blade (the one permitted blade content edit), styled light and dark in CSS.
9. **Theme mechanics**: an inline `<script>` in `<head>` (before stylesheets) reads `localStorage['echo-theme']` / `prefers-color-scheme` and sets `data-theme` before first paint — eliminates the light flash; `helper.js` keeps handling the toggle (its logic already matches). `color-scheme: dark` (in the token block) makes scrollbars and native widgets render dark.

## 4. Deliberate light plates (not bugs)

- **Partner logos (home)**: `.hp-cell` tiles become `--dk-plate` (toned light, not pure white) on the dark band — partner logos are colored artwork; inverting or dark-tiling corrupts them. The section heading/background itself goes dark.
- **Org chart (structure page)**: the hotlinked GIF (opaque white, from lex.uz) sits on a white/`--dk-plate` "document plate" card with `--dk-border`. CSS `filter: invert()` would corrupt its colors. A true dark asset can be a follow-up.

## 5. Files changed

| File | Change |
|---|---|
| `public/css/dark.css` | **new** — entire dark theme (tokens + all overrides) |
| `resources/views/components/layouts/app.blade.php` | link `dark.css` after `@stack('styles')`; add pre-paint theme script |
| `resources/views/livewire/open-data/open-data-index.blade.php` | swap local dark hexes to shared tokens; fix 4 gaps |
| `resources/views/livewire/show-all-categories.blade.php` | replace inline `<hr>` styles with a class |
| `tests/Feature/…` | new/updated feature test (see §6) |

Explicitly **not** changed: `public/css/style.css`, `news-article.css`, `site-pages.css`, all other blades, everything under `admin`.

## 6. Testing & verification

- **Feature test** (Pest): public layout renders (a) the pre-paint theme script before the first stylesheet link, (b) a version-stamped `css/dark.css` link after the styles stack. Guards the two layout integration points.
- **Full suite stays green** — only additive layout changes.
- **Light mode cannot regress by construction**: every new rule is behind `html[data-theme=dark]`; the only blade content edits are the `<hr>` class swap (restyled identically for light) and the open-data token swap (dark block only).
- **Manual/agent verification**: walk all 14 audited pages in dark mode, desktop + <992px (mobile menu, language popup), using the Appendix as the checklist; confirm the light flash is gone (hard reload with dark stored); spot-check light mode on the same pages.

## 7. Out of scope

- Admin panel dark mode.
- A dark variant of the org-chart image (follow-up asset task).
- Migrating light mode onto the token system (rejected approach: "full token migration").

## Appendix — Verified audit inventory (implementation checklist)

79 verified findings from the 2026-07-13 audit (31 agents; every finding independently verified against the rendered pages and all loaded stylesheets). Severity: high = large glaring area or unreadable text; medium = clearly inconsistent element; low = polish.

### home — partial
- **high** — "Hamkorlarimiz" partners section: full-width white band + 8 `.hp-cell` tiles via `--na-paper` (`public/css/site-pages.css:299`)
- **high** — Mobile hamburger menu: white panel + vendor dark rule recolors links near-white → unreadable (`public/css/style.css:34108`)
- **medium** — Test-mode notice strip, amber on dark (`public/css/style.css:34433`)
- **medium** — Desktop language switcher `#555` ≈2.3:1 on dark body (`public/css/style.css:34200`)
- **medium** — Header social icons: `rgba(0,0,0,.05)` pills invisible, `#555` glyphs (`public/css/style.css:34283`)
- **low** — Mobile language options panel hardcoded white (`public/css/style.css:34239`)
- **low** — Carousel prev/next arrows `#5E5E5E` ≈2.9:1 on dark band (`public/css/style.css:18359`)

### /about — broken
- **high** — Entire `.news-article-section`: white sheet, invisible title, leaked white body text (`public/css/news-article.css:57`)
- **high** — Mobile hamburger menu (as home) (`public/css/style.css:34108`)
- **medium** — Desktop language switcher (`public/css/style.css:34200`)
- **medium** — Header social icons (`public/css/style.css:34283`)
- **medium** — Test-mode notice (`public/css/style.css:34433`)
- **low** — Mobile language popup (`public/css/style.css:34239`)

### /contact — broken
- **high** — `h1.article-title` white-on-white (vendor dark h1 rule beats `.article-title`) (`public/css/style.css:17901`)
- **high** — Entire content section incl. contact cards `.contact-card`/`.cc-lbl`/`.cc-val`/`.cc-ic` — na-tokens light-only (`public/css/news-article.css:57`)
- **medium** — Language switcher, all variants (`public/css/style.css:34200`)
- **medium** — Test-mode notice (`public/css/style.css:34433`)

### /history — broken
- **high** — Article title + body paragraphs: vendor dark text rules leak white text onto white sheet (`public/css/style.css:17843`)
- **high** — `.news-article-section` white slab (`public/css/news-article.css:57`)
- **medium** — `strong` (`--na-ink`) and links (`--na-azure`) in body lack dark treatment (`public/css/news-article.css:153`)

### /leadership — broken
- **high** — Entire content area: section + breadcrumb + title + leader cards, all na-tokens (`public/css/news-article.css:57`)
- **high** — Mobile hamburger menu (`public/css/style.css:34108`)
- **medium** — Desktop language switcher (`public/css/style.css:34200`)
- **low** — Test-mode notice (`public/css/style.css:34433`)
- **low** — Mobile language popup (`public/css/style.css:34239`)

### /open-data — partial
- **medium** — Test-mode notice (`public/css/style.css:34433`)
- **medium** — Desktop language switcher (`public/css/style.css:34200`)
- **medium** — `.od-eyebrow` stays `#3a4c7a` ≈2:1 on dark (`…/open-data-index.blade.php:12`)
- **low** — Pagination `.od-nav:hover` color `#3a4c7a` (light rule (0,4,0) outranks dark) (`…/open-data-index.blade.php:82`)
- **low** — Clear-filters button hover color (`…/open-data-index.blade.php:42`)
- **low** — Sort select focus border dim (dark resting rule ties+beats light focus rule) (`…/open-data-index.blade.php:51`)
- **low** — Mobile language popup (`public/css/style.css:34239`)

### /search — broken
- **high** — Entire content area: breadcrumb, title, search box, count, result cards, load-more on white island (`public/css/news-article.css:57`)
- **high** — `h1.article-title` white-on-white (`public/css/style.css:17901`)
- **high** — `.search-count`/`.search-empty` `<p>` recolored near-white on white (`public/css/style.css:17843`)
- **high** — `.sp-more-btn` label near-white on light fill (`public/css/style.css:17862`)
- **high** — Mobile hamburger menu (`public/css/style.css:34108`)
- **medium** — Test-mode notice (`public/css/style.css:34433`)
- **medium** — Desktop language switcher (`public/css/style.css:34200`)
- **medium** — Header social icons (`public/css/style.css:34283`)
- **low** — Mobile language popup (`public/css/style.css:34239`)

### /show-all-category — partial
- **medium** — Test-mode notice (`public/css/style.css:34433`)
- **medium** — Header social icons (`public/css/style.css:34283`)
- **medium** — Desktop language switcher (`public/css/style.css:34200`)
- **medium** — Mobile hamburger menu (`public/css/style.css:34108`)
- **low** — Mobile language popup (`public/css/style.css:34239`)
- **low** — Sidebar `<hr>` inline `background-color:#4c0505` ≈1.2:1 on dark (`…/show-all-categories.blade.php:49,63`)
- **low** — Desktop submenu hover `#3a4c7a` ≈2.1:1 on dark submenu (`public/css/style.css:15866`)

### /show-category/{slug} — broken
- **high** — `h1.article-title` + `h2.article-more-title` white-on-white (`public/css/style.css:17901`)
- **high** — `.news-article-section` white sheet (`public/css/news-article.css:57`)
- **high** — `.article-more` band `--na-mist` light (`public/css/news-article.css:230`)
- **medium** — `.news-card` grid light-only (latent on empty category, active with content) (`public/css/site-pages.css:88`)
- **low** — `.popular-item` rows light-only (`public/css/news-article.css:302`)
- **low** — `.article-breadcrumb` slate, no dark treatment (`public/css/news-article.css:71`)

### /show-news/{slug} — broken
- **high** — Entire content: `.news-article-section` + `.article-more` (headline, lead, prose, read-next) (`public/css/news-article.css:57`)
- **medium** — Desktop language switcher (`public/css/style.css:34200`)
- **medium** — Mobile hamburger menu (`public/css/style.css:34108`)
- **low** — Test-mode notice (`public/css/style.css:34433`)

### /structure — partial
- **high** — Org chart GIF (opaque white, hotlinked from lex.uz) floats raw on dark bg (`resources/views/livewire/structure.blade.php:16`)
- **high** — Mobile hamburger menu (`public/css/style.css:34108`)
- **medium** — Test-mode notice (`public/css/style.css:34433`)
- **medium** — Desktop language switcher (`public/css/style.css:34200`)
- **low** — Mobile language popup (`public/css/style.css:34239`)

### /vacancies — broken
- **high** — Entire article area on white (`public/css/news-article.css:58`)
- **high** — `.article-more` popular module light band (`public/css/news-article.css:231`)
- **medium** — Language switcher, all variants (`public/css/style.css:34200`)
- **medium** — Test-mode notice (`public/css/style.css:34433`)

### /videos — broken
- **high** — Entire content: breadcrumb, title, video grid on white (`public/css/news-article.css:58`)
- **high** — `.article-more` popular band light (`public/css/news-article.css:231`)
- **high** — `h1` + `h2` headings white-on-light (`public/css/style.css:17901`)
- **low** — Load-more button label near-white on light fill (renders >8 videos) (`public/css/style.css:17862`)
- **low** — Test-mode notice (`public/css/style.css:34433`)

### /videos/{id} — broken
- **high** — `h1.article-title` + `h2.article-more-title` white-on-white (`public/css/style.css:17901`)
- **high** — `.news-article-section` white sheet + `.article-more` light band (`public/css/news-article.css:57`)

### chrome (all pages) — partial
- **high** — Desktop language switcher (`public/css/style.css:34200`)
- **high** — Header social icons (`public/css/style.css:34283`)
- **high** — Mobile hamburger menu incl. white-on-white links (`public/css/style.css:34108`)
- **medium** — Test-mode notice (`public/css/style.css:34433`)
- **medium** — Mobile language options popup (`public/css/style.css:34239`)

### Palette-level defects (site-wide, from the palette analyst)
- Four surface families → unify on the navy tokens (§2)
- Inverted/flat elevation (cards = page, 1.08:1) → monotonic scale (§2)
- Dead tokens `--bg-dark-one`/`--bg-dark-two` → unused; superseded by `--dk-*` (left in vendor file, unreferenced)
- Brand `#3a4c7a` ≈2.1:1 on dark → `--dk-accent #8FAEE0` (§3.7)
- `#D00032` hover 3.37:1, `#FFFFFF45` separators 2.46:1, inconsistent border alphas → two border tokens + accent hover (§2, §3.7)
- Two different modal scrims → `--dk-scrim` (§3.2, §3.5)
