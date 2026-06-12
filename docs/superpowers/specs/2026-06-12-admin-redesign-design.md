# Admin Panel Redesign — Design Spec

**Date:** 2026-06-12
**Branch:** admin-improvements-i18n (or a new branch off it)
**Direction:** Polish the existing Linear-style theme + structural refactor to Vite/Blade components + dark mode.

## Background

The admin panel (`/admin`) is a custom Linear-inspired theme (Inter, indigo `#5e6ad2`, flat white surfaces) over Bootstrap 5. All ~230 lines of CSS and the sidebar JS live inline in `resources/views/components/layouts/admin.blade.php`. The look is clean but sparse: uniform visual weight, no elevation, no color hierarchy, a bare dashboard, and several visible defects.

### Defects observed (must be fixed by this work)

1. **Media library shows raw HTML** in the FilePond drop label. The translation `lang/ru/admin.php` key `admin.media.filepond_idle` contains a `<span>` but is output escaped via `{{ }}` at `resources/views/livewire/admin/media/index.blade.php:68`. Output it unescaped (trusted, our own lang string) or restructure the label.
2. **Dashboard "Обновлено" column is empty** in the recent-news table.
3. **Status pills show raw English enum values** ("published", "draft") in a Russian UI.
4. **"Черновики" stat value is styled near-invisible** (faint gray); "7 / 6" for "Категории / Теги" is cryptic.
5. **Breadcrumb shows untranslated titles** (e.g. "News form" on the news edit page).
6. **Native form controls clash**: `mm/dd/yyyy` date input, unstyled selects, tags as a raw checkbox list.

## Scope

### 1. Structure (refactor)

- **`resources/css/admin.css`** — new Vite input. The inline `<style>` block from the admin layout moves here, organized by section (tokens, base, shell/sidebar, topbar, cards, tables, forms, buttons, pills/chips, misc). Loaded via `@vite` in the admin layout. Bootstrap continues to load from `public/css/vendor/bootstrap.min.css` before it.
- **`resources/js/admin.js`** — new Vite input. Sidebar toggle script moves here; theme toggle logic added. A small inline `<head>` snippet (a few lines, stays inline by design) applies the persisted theme before first paint to prevent flash-of-wrong-theme.
- **Blade components** in `resources/views/components/admin/` (sibling to existing `confirm-modal.blade.php`):
  - `page-header` — h1 + subtitle + actions slot (replaces repeated `.page-header` markup).
  - `stat-card` — label, value, optional icon + accent color.
  - `status-pill` — accepts a status enum/string, renders translated label + color class.
  - `lang-chips` — accepts the set of locales with translation-presence flags, renders one compact horizontal indicator.
  - `empty-state` — icon + title + optional hint (replaces repeated `.empty-state` markup).
- Admin Livewire views are updated to use these components where the equivalent markup currently exists. No behavioral changes to Livewire components themselves beyond what the visual changes require (e.g. exposing `updated_at` where missing).

### 2. Theme & dark mode

- The existing `:root` CSS variables remain the single source of truth; values may be tuned but names stay.
- A `[data-admin-theme="dark"]` override block defines the dark palette: dark gray surfaces (not pure black), adjusted borders/text tiers, lightened indigo accent for contrast, adjusted soft status backgrounds.
- Topbar gains a sun/moon toggle button. Preference persists in `localStorage` (`cerr-admin-theme`); default follows `prefers-color-scheme`. The `<html>` element carries both `data-admin-theme` and Bootstrap's `data-bs-theme` so Bootstrap-rendered widgets (modals, dropdowns) follow the theme.

### 3. Visual upgrades

- **Dashboard** (`resources/views/livewire/admin/dashboard.blade.php`):
  - Stat cards: icon + accent per card; drafts use the warning color; "Категории / Теги" splits into two cards.
  - Recent-news table: translated status pills, populated "Обновлено" (relative time), compact lang indicator.
  - New quick-actions row (New article, Upload media — links to existing routes, role-gated like the sidebar).
  - New recent-activity panel reusing the existing activity log data (visible only to users with `canViewActivity()`).
- **News index**: lang chips collapse to the single-line `lang-chips` component; category rendered as a soft badge; row vertical padding reduced.
- **Forms (news/pages)**: CSS-styled selects (custom chevron), date/datetime inputs styled to match the input suite, tag checkbox list restyled as toggle chips (CSS-only, same inputs underneath).
- **Global**: cards get a faint elevation shadow; table headers get a hover affordance consistent with the rest; icon-button actions consistent across tables.

### 4. Explicitly out of scope

- No Flux UI / Tailwind migration.
- No chart libraries (no sparklines in this iteration).
- No bulk actions, no new admin pages, no changes to public site styling.
- No new JS or PHP dependencies.

## Error handling

- Theme toggle degrades gracefully: with JS disabled or `localStorage` unavailable, the panel renders the light theme (or `prefers-color-scheme` via media query for the pre-paint snippet's fallback).
- `lang-chips` and `status-pill` components must tolerate unknown/missing values (render a neutral fallback, never throw).

## Testing

- Pest feature tests: each admin route (dashboard, news index/create/edit, categories, tags, pages, videos, media, users, activity) returns 200 for an admin user and contains key markers of the new layout (e.g. translated status label, `lang-chips` markup, `@vite` asset tags).
- Blade component render tests for `status-pill` (each status → translated label + class; unknown status → fallback) and `lang-chips` (present/missing locales).
- Regression: existing admin test suite stays green.
- Build verification: `npm run build` succeeds; `vendor/bin/pint --dirty` clean.
- Visual verification: Playwright screenshot pass of dashboard, news index, news edit, media in both light and dark themes.

## Success criteria

- No inline `<style>`/sidebar `<script>` blocks remain in the admin layout (except the pre-paint theme snippet).
- All six defects listed above are fixed.
- Dark mode toggles and persists across navigation and reloads.
- All admin pages visually consistent with the refreshed theme in both modes.
