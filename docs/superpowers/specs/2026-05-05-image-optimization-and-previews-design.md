# Image Optimization + Live Previews — Design

**Date:** 2026-05-05
**Status:** Approved (3 sections, brainstormed and confirmed)

## Summary

Compress and resize uploaded images during the save request so the public site
loads faster, and show a live thumbnail preview in every admin form as soon as
the user picks a file (instead of just showing the filename).

## Goals

1. Cut average uploaded-image weight by 80–90% (4 MB phone photos → 250–500 KB).
2. Cap image resolution at 1920 px on the longer side; never enlarge.
3. Strip EXIF metadata (privacy + extra weight).
4. Show a real thumbnail preview in News/Videos/Pages forms during *and* after
   upload, plus the existing post-save preview.

## Non-goals

- WebP/AVIF conversion (out of scope; user picked "Standard web", not "Aggressive").
- Multiple sizes / thumbnail variants (user picked "No thumbnails").
- Retroactive optimization of the 11 legacy images (user picked "New uploads only").
- CDN integration.
- Animated GIF re-encoding (would lose animation; we pass through).
- SVG sanitization (out of scope; existing inline-image controller already
  restricts mime types).

## Architecture

### Components

- **`App\Support\ImageOptimizer`** — new service class, single public method
  `optimize(UploadedFile $file, string $absolutePath): void`. Loads the source
  via Intervention/Image v3, scales it down (only if wider than 1920 px),
  re-encodes at quality 82, strips EXIF, and saves to the given path.
- **`App\Livewire\Concerns\HandlesImageUploads`** — existing trait. Updated so
  `storeUploadedImage()` routes through `ImageOptimizer`.
- **`App\Http\Controllers\Admin\InlineImageController`** — existing TinyMCE
  inline upload endpoint. Also routes through `ImageOptimizer`.
- **Form views** (news/videos/pages) — render a 140×96 preview tile from the
  Livewire `temporaryUrl()` of the staged file, falling back to the saved cover
  URL after save.

### Dependency

`intervention/image:^3` added via composer. Wraps GD (already installed; verify
with `php -m | grep gd` on prod before deploy).

## Data flow

```
UploadedFile → HandlesImageUploads::storeUploadedImage()
                  ↓
              ImageOptimizer::optimize()
                  ↓
              Intervention::read()
                  ↓ (extension switch)
       jpg/jpeg/png/webp:  scaleDown(1920) → save(quality=82)
       gif:                copy original (preserve animation)
       svg:                copy original (vector)
                  ↓
              file at storage/app/public/{folder}/{uuid}.{ext}
```

The path returned by `storeUploadedImage()` is unchanged in shape, so
`NewsTranslation::coverUrl()`, the media library, and the media picker all keep
working without changes.

## Optimization parameters

| Setting | Value | Reason |
|---|---|---|
| Max width | 1920 px (longer side) | Standard 1080p screens; retina laptops; 2× fits ~960 px content area |
| JPEG quality | 82 | Visually indistinguishable from 95, ~40% smaller |
| PNG | re-encoded lossless | Negligible savings but strips metadata |
| WebP | quality 82 | Match JPEG path |
| GIF | copy as-is | Re-encoding would lose animation |
| SVG | copy as-is | Vector; raster ops don't apply |
| EXIF | stripped | Privacy + size |
| EXIF orientation | applied to pixels first | Phone photos stay right-side up |
| Memory limit | `ini_set('memory_limit', '256M')` per call | 50MP photos need ~200 MB peak in GD |

## Live preview UX

Each cover-image area renders this priority chain:

1. If a fresh upload is staged (`cover_uploads.{locale}` for news,
   `imageUpload` for videos/pages) → `<img src="{{ $upload->temporaryUrl() }}">`.
2. Else if a saved cover exists → existing post-save URL
   (`coverUrl()` for news, `Storage::disk('public')->url($image)` for
   videos/pages).
3. Else → empty drop-zone hint.

Preview tile is 140×96 px with `object-fit: cover`. Live reactivity is provided
by Livewire's automatic temp upload + re-render cycle. A small spinner uses
`wire:loading wire:target="cover_uploads.{locale}"` while the temp upload is in
flight.

## Error handling

`ImageOptimizer` wraps the Intervention pipeline in `try/catch`:

- On any throwable (corrupt file, GD failure, OOM despite the bump): log
  `\Log::warning('Image optimization failed', ['file' => $name, 'error' => $msg])`
  and copy the original file unchanged to the destination.
- The upload still succeeds; the user sees the article saved.
- We do **not** write an activity-log entry per failure (would clutter the log
  for a non-critical event). The Laravel log warning is sufficient — admins
  who want to investigate can grep `storage/logs/laravel.log` for
  "Image optimization failed".

## Testing strategy

- **Unit:** `tests/Unit/Support/ImageOptimizerTest.php`
  - `it shrinks images wider than 1920px` — input 3000×2000, output max-width 1920
  - `it leaves small images alone` — input 800×600, output 800×600
  - `it strips EXIF` — input with embedded metadata, output exiftool-shows-empty
  - `it preserves orientation` — input portrait JPEG with EXIF rotation, output
    pixels rotated correctly
  - `it copies GIF unchanged` — file size byte-equal to input
  - `it copies SVG unchanged`
  - `it falls back to original on corrupt input` — assert exception is caught,
    file written, log warning emitted
- **Feature:** existing `NewsCrudTest`, `VideoCrudTest`, `PageCrudTest`
  cover-image tests already exercise the upload path and assert
  `Storage::disk('public')->assertExists($path)` (existence, not byte-equality)
  — so they continue to pass with the optimizer in the chain. No changes needed.
- **Markup:** `tests/Feature/Admin/ImagePreviewTest.php`
  - `it renders the preview block when a cover_upload is staged` — assert the
    `wire:model="cover_uploads.kr"` input plus a sibling `<img>` referencing
    `temporaryUrl` are present after `set('cover_uploads.kr', UploadedFile::fake()->image('x.jpg'))`

## Files touched

- `composer.json` / `composer.lock` — add intervention/image
- **Create** `app/Support/ImageOptimizer.php`
- **Modify** `app/Livewire/Concerns/HandlesImageUploads.php`
- **Modify** `app/Http/Controllers/Admin/InlineImageController.php`
- **Modify** `resources/views/livewire/admin/news/form.blade.php`
- **Modify** `resources/views/livewire/admin/videos/index.blade.php`
- **Modify** `resources/views/livewire/admin/pages/form.blade.php`
- **Create** `tests/Unit/Support/ImageOptimizerTest.php`
- **Create** `tests/Feature/Admin/ImagePreviewTest.php`

## Risks

| # | Risk | Mitigation |
|---|---|---|
| 1 | `ext-gd` missing on prod | Verify `php -m \| grep gd` before deploy. Composer install fails loudly otherwise. |
| 2 | Memory exhaustion on huge DSLR photos | Per-call `ini_set('memory_limit', '256M')`. Try/catch falls back to copy-original. |
| 3 | Synchronous processing slows save by 0.5–1.5s | User-accepted; better than queue worker complexity. |
| 4 | iPhone EXIF orientation makes photos sideways | Intervention auto-applies orientation before stripping. |
| 5 | New `temporaryUrl()` previews change Livewire upload behavior in tests | Pest `Storage::fake('public')` + `UploadedFile::fake()->image()` already work; verify each form's tests still pass with optimizer in path. |
