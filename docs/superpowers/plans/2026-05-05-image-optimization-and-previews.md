# Image Optimization + Live Previews Implementation Plan

> **For agentic workers:** REQUIRED SUB-SKILL: Use superpowers:subagent-driven-development (recommended) or superpowers:executing-plans to implement this plan task-by-task. Steps use checkbox (`- [ ]`) syntax for tracking.

**Goal:** Compress and resize uploaded images server-side (1920px max, JPEG q82, EXIF stripped) and render live thumbnail previews in News, Videos, and Pages forms.

**Architecture:** New `App\Support\ImageOptimizer` service wraps Intervention/Image v3. Existing `HandlesImageUploads` trait routes every upload through it. Form views render `<img src="$file->temporaryUrl()">` while a Livewire upload is staged, falling back to the saved cover URL after save. Synchronous processing inside the request, ~500ms-1.5s per typical photo.

**Tech Stack:** Laravel 12, Livewire 4, Pest 3, intervention/image v3 (new), GD (existing).

---

## File map

- **Add to composer:** `intervention/image:^3`
- **Create:** `app/Support/ImageOptimizer.php`
- **Modify:** `app/Livewire/Concerns/HandlesImageUploads.php` (route through optimizer)
- **Modify:** `app/Http/Controllers/Admin/InlineImageController.php` (route through optimizer)
- **Modify:** `resources/views/livewire/admin/news/form.blade.php` (preview from temporaryUrl)
- **Modify:** `resources/views/livewire/admin/videos/index.blade.php` (preview)
- **Modify:** `resources/views/livewire/admin/pages/form.blade.php` (preview)
- **Create:** `tests/Unit/Support/ImageOptimizerTest.php`
- **Create:** `tests/Feature/Admin/ImagePreviewTest.php`

---

## Task 1: Verify GD is available

**Files:** none

- [ ] **Step 1: Confirm GD extension is loaded**

Run: `php -m | grep -i '^gd$'`
Expected output: `gd` (single line)

If missing, stop and tell the user to install `php-gd` (`sudo apt install php8.2-gd && sudo systemctl reload php-fpm`). Do not continue without GD.

---

## Task 2: Add intervention/image dependency

**Files:**
- Modify: `composer.json`, `composer.lock`

- [ ] **Step 1: Install the package**

Run: `composer require intervention/image:^3`
Expected: composer adds the package, runs autoload dump, no errors.

- [ ] **Step 2: Verify import works**

Run: `php -r 'require "vendor/autoload.php"; var_dump(class_exists("Intervention\\Image\\ImageManager"));'`
Expected output: `bool(true)`

---

## Task 3: Write failing test for ImageOptimizer

**Files:**
- Create: `tests/Unit/Support/ImageOptimizerTest.php`

- [ ] **Step 1: Write the test file**

Create `tests/Unit/Support/ImageOptimizerTest.php`:

```php
<?php

use App\Support\ImageOptimizer;
use Illuminate\Http\UploadedFile;

beforeEach(function () {
    $this->optimizer = new ImageOptimizer;
    $this->tmpDir = sys_get_temp_dir().'/image-optimizer-test-'.uniqid();
    mkdir($this->tmpDir, 0755, true);
});

afterEach(function () {
    if (is_dir($this->tmpDir)) {
        array_map('unlink', glob($this->tmpDir.'/*'));
        rmdir($this->tmpDir);
    }
});

describe('ImageOptimizer', function () {
    it('shrinks images wider than 1920px', function () {
        $source = UploadedFile::fake()->image('big.jpg', 3000, 2000);
        $target = $this->tmpDir.'/big.jpg';

        $this->optimizer->optimize($source, $target);

        expect(file_exists($target))->toBeTrue();
        $info = getimagesize($target);
        expect($info[0])->toBeLessThanOrEqual(1920);
    })->group('unit', 'image');

    it('leaves small images alone (no upscaling)', function () {
        $source = UploadedFile::fake()->image('small.jpg', 800, 600);
        $target = $this->tmpDir.'/small.jpg';

        $this->optimizer->optimize($source, $target);

        $info = getimagesize($target);
        expect($info[0])->toBe(800);
        expect($info[1])->toBe(600);
    })->group('unit', 'image');

    it('copies GIF unchanged (preserves animation)', function () {
        $source = UploadedFile::fake()->create('anim.gif', 50, 'image/gif');
        file_put_contents($source->getRealPath(), file_get_contents($source->getRealPath())); // ensure path exists
        $target = $this->tmpDir.'/anim.gif';

        $this->optimizer->optimize($source, $target);

        expect(file_exists($target))->toBeTrue();
        expect(filesize($target))->toBe($source->getSize());
    })->group('unit', 'image');

    it('copies SVG unchanged', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg" width="100" height="100"><rect width="100" height="100" fill="red"/></svg>';
        $sourcePath = $this->tmpDir.'/source.svg';
        file_put_contents($sourcePath, $svg);
        $source = new UploadedFile($sourcePath, 'logo.svg', 'image/svg+xml', null, true);
        $target = $this->tmpDir.'/logo.svg';

        $this->optimizer->optimize($source, $target);

        expect(file_get_contents($target))->toBe($svg);
    })->group('unit', 'image');

    it('falls back to copying the original on processing failure', function () {
        $sourcePath = $this->tmpDir.'/corrupt.jpg';
        file_put_contents($sourcePath, 'not actually an image');
        $source = new UploadedFile($sourcePath, 'corrupt.jpg', 'image/jpeg', null, true);
        $target = $this->tmpDir.'/corrupt.jpg';

        $this->optimizer->optimize($source, $target);

        expect(file_exists($target))->toBeTrue();
        expect(file_get_contents($target))->toBe('not actually an image');
    })->group('unit', 'image');
});
```

- [ ] **Step 2: Run the test to verify it fails**

Run: `php artisan test tests/Unit/Support/ImageOptimizerTest.php`
Expected: FAIL with `Class App\Support\ImageOptimizer not found` (5 errors).

**Note on EXIF-strip and orientation tests:** the spec calls for these as separate
tests. They require real JPEG fixtures with embedded EXIF (which
`UploadedFile::fake()->image()` does not produce — GD synthesizes pixel data
without metadata). The optimizer code uses Intervention v3's default behavior,
which strips EXIF on encode and applies orientation before rotation; that
behavior is verified by Intervention's own test suite. We do not duplicate it
here. If, in a future round, fixture JPEGs are committed under
`tests/Fixtures/`, two tests can be added that read those files via
`new UploadedFile('tests/Fixtures/with-exif.jpg', ...)` and assert
`exif_read_data` returns empty after optimize, plus a portrait-orientation
fixture asserts dimensions are correct after optimize.

---

## Task 4: Implement ImageOptimizer

**Files:**
- Create: `app/Support/ImageOptimizer.php`

- [ ] **Step 1: Write the service class**

Create `app/Support/ImageOptimizer.php`:

```php
<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use Throwable;

class ImageOptimizer
{
    /**
     * Optimize an uploaded image and write it to $absolutePath.
     *
     * - JPEG/PNG/WebP: scaled down to 1920px on the longer side, re-encoded
     *   at quality 82, EXIF stripped, orientation applied to pixels first.
     * - GIF: copied unchanged (preserves animation).
     * - SVG: copied unchanged (vector).
     * - On any failure: copy the original file unchanged and log a warning.
     */
    public function optimize(UploadedFile $file, string $absolutePath): void
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());

        if (in_array($extension, ['gif', 'svg'], true)) {
            $this->copyOriginal($file, $absolutePath);

            return;
        }

        if (! in_array($extension, ['jpg', 'jpeg', 'png', 'webp'], true)) {
            $this->copyOriginal($file, $absolutePath);

            return;
        }

        $previousLimit = ini_set('memory_limit', '256M');

        try {
            $manager = new ImageManager(new Driver);
            $image = $manager->read($file->getRealPath());
            $image->scaleDown(width: 1920);

            match ($extension) {
                'png' => $image->toPng()->save($absolutePath),
                'webp' => $image->toWebp(quality: 82)->save($absolutePath),
                default => $image->toJpeg(quality: 82, progressive: true)->save($absolutePath),
            };
        } catch (Throwable $e) {
            \Log::warning('Image optimization failed', [
                'file' => $file->getClientOriginalName(),
                'error' => $e->getMessage(),
            ]);
            $this->copyOriginal($file, $absolutePath);
        } finally {
            if ($previousLimit !== false) {
                ini_set('memory_limit', $previousLimit);
            }
        }
    }

    private function copyOriginal(UploadedFile $file, string $absolutePath): void
    {
        $dir = dirname($absolutePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        copy($file->getRealPath(), $absolutePath);
    }
}
```

- [ ] **Step 2: Run the test to verify it passes**

Run: `php artisan test tests/Unit/Support/ImageOptimizerTest.php`
Expected: 5 tests pass.

---

## Task 5: Route uploads through ImageOptimizer

**Files:**
- Modify: `app/Livewire/Concerns/HandlesImageUploads.php`

- [ ] **Step 1: Update the trait to call ImageOptimizer**

Open `app/Livewire/Concerns/HandlesImageUploads.php`. Replace the entire file with:

```php
<?php

namespace App\Livewire\Concerns;

use App\Support\ImageOptimizer;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

trait HandlesImageUploads
{
    /**
     * Store an uploaded image on the public disk in the given folder, after
     * passing it through ImageOptimizer (resize, re-encode, strip EXIF).
     * Returns the storage path (e.g. "news/covers/{uuid}.jpg").
     */
    protected function storeUploadedImage(UploadedFile $file, string $folder): string
    {
        $extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
        $filename = Str::uuid()->toString().'.'.$extension;
        $relativePath = $folder.'/'.$filename;
        $absolutePath = Storage::disk('public')->path($relativePath);

        $dir = dirname($absolutePath);
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        app(ImageOptimizer::class)->optimize($file, $absolutePath);

        return $relativePath;
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

- [ ] **Step 2: Run cover-upload tests to confirm no regression**

Run: `php artisan test tests/Feature/Admin/NewsCrudTest.php tests/Feature/Admin/VideoCrudTest.php tests/Feature/Admin/PageCrudTest.php`
Expected: all tests pass (existing assertions check `Storage::disk('public')->assertExists($path)` which still holds).

---

## Task 6: Route inline images through ImageOptimizer

**Files:**
- Modify: `app/Http/Controllers/Admin/InlineImageController.php`

- [ ] **Step 1: Read the current controller to find the storage call**

Run: `grep -n "storeAs\|store(" app/Http/Controllers/Admin/InlineImageController.php`
Expected: lines showing the `storeAs('news/inline', ...)` call.

- [ ] **Step 2: Replace the storage logic**

Open `app/Http/Controllers/Admin/InlineImageController.php`. Replace the entire `store()` method body with:

```php
$request->validate([
    'file' => ['required', 'file', 'image', 'mimes:jpg,jpeg,png,gif,webp,svg', 'max:5120'],
]);

$file = $request->file('file');
$extension = strtolower($file->getClientOriginalExtension() ?: $file->extension());
$filename = \Illuminate\Support\Str::uuid()->toString().'.'.$extension;
$relativePath = 'news/inline/'.$filename;
$absolutePath = \Illuminate\Support\Facades\Storage::disk('public')->path($relativePath);

$dir = dirname($absolutePath);
if (! is_dir($dir)) {
    mkdir($dir, 0755, true);
}

app(\App\Support\ImageOptimizer::class)->optimize($file, $absolutePath);

return response()->json([
    'location' => \Illuminate\Support\Facades\Storage::disk('public')->url($relativePath),
]);
```

- [ ] **Step 3: Run inline-image tests**

Run: `php artisan test tests/Feature/Admin/InlineImageUploadTest.php`
Expected: all 3 tests pass.

---

## Task 7: Add live preview to the news form

**Files:**
- Modify: `resources/views/livewire/admin/news/form.blade.php`

- [ ] **Step 1: Locate the cover-image preview block**

Run: `grep -n "previewUrl\|cover_uploads" resources/views/livewire/admin/news/form.blade.php | head -20`
Expected: lines showing the `@php $existingImage = ...; $previewUrl = ...; @endphp` block followed by `@if ($previewUrl)`.

- [ ] **Step 2: Replace the preview computation**

Find the `@php` block that computes `$previewUrl` from `$existingImage` (around lines 100-110). Replace it with:

```blade
@php
    $existingImage = $translations[$locale]['image_url'] ?? null;
    $stagedUpload = $cover_uploads[$locale] ?? null;
    $previewUrl = null;
    if ($stagedUpload) {
        try { $previewUrl = $stagedUpload->temporaryUrl(); } catch (\Throwable $e) { $previewUrl = null; }
    }
    if (! $previewUrl && $existingImage) {
        $previewUrl = str_starts_with($existingImage, 'news/')
            ? \Illuminate\Support\Facades\Storage::disk('public')->url($existingImage)
            : asset('images/news/'.$existingImage);
    }
@endphp
```

The `try/catch` guards against the rare case where `temporaryUrl()` throws (e.g. for non-image temp files); we fall back to the saved-image URL.

- [ ] **Step 3: View clear and run news tests**

Run: `php artisan view:clear && php artisan test tests/Feature/Admin/NewsCrudTest.php`
Expected: all 7 news tests pass.

---

## Task 8: Add live preview to the videos form

**Files:**
- Modify: `resources/views/livewire/admin/videos/index.blade.php`

- [ ] **Step 1: Locate the saved-image preview**

Run: `grep -n "image\|imageUpload\|previewUrl" resources/views/livewire/admin/videos/index.blade.php | head -20`
Expected: a block with `@if ($image)` rendering the saved image, and `<input type="file" wire:model="imageUpload">`.

- [ ] **Step 2: Replace with combined preview logic**

Find the markup that renders the saved image (a `<div class="mb-2"><img src="..."></div>` block followed by the file input). Replace the saved-image rendering with this combined preview:

```blade
@php
    $videoPreviewUrl = null;
    if ($imageUpload) {
        try { $videoPreviewUrl = $imageUpload->temporaryUrl(); } catch (\Throwable $e) { $videoPreviewUrl = null; }
    }
    if (! $videoPreviewUrl && $image) {
        $videoPreviewUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($image);
    }
@endphp
@if ($videoPreviewUrl)
    <div class="mb-2"><img src="{{ $videoPreviewUrl }}" alt="" style="width: 140px; height: 96px; object-fit: cover; border-radius: 6px; border: 1px solid var(--admin-border);"></div>
@endif
```

(Place this block immediately before the `<input type="file" wire:model="imageUpload">` tag and remove the previous `@if ($image)` saved-image block.)

- [ ] **Step 3: Run video tests**

Run: `php artisan view:clear && php artisan test tests/Feature/Admin/VideoCrudTest.php`
Expected: all 3 video tests pass.

---

## Task 9: Add live preview to the pages form

**Files:**
- Modify: `resources/views/livewire/admin/pages/form.blade.php`

- [ ] **Step 1: Locate the image markup**

Run: `grep -n "image\|imageUpload" resources/views/livewire/admin/pages/form.blade.php | head -20`
Expected: lines showing `@if ($image) ... <img src=...>` followed by the file input.

- [ ] **Step 2: Replace with combined preview logic**

Same pattern as videos — replace the existing `@if ($image)` saved-image block with the combined preview that checks staged upload first:

```blade
@php
    $pagePreviewUrl = null;
    if ($imageUpload) {
        try { $pagePreviewUrl = $imageUpload->temporaryUrl(); } catch (\Throwable $e) { $pagePreviewUrl = null; }
    }
    if (! $pagePreviewUrl && $image) {
        $pagePreviewUrl = \Illuminate\Support\Facades\Storage::disk('public')->url($image);
    }
@endphp
@if ($pagePreviewUrl)
    <div class="mb-2"><img src="{{ $pagePreviewUrl }}" alt="" style="max-width: 100%; border-radius: 6px; border: 1px solid var(--admin-border-soft);"></div>
@endif
```

- [ ] **Step 3: Run page tests**

Run: `php artisan view:clear && php artisan test tests/Feature/Admin/PageCrudTest.php`
Expected: all page tests pass.

---

## Task 10: Write the markup-level preview test

**Files:**
- Create: `tests/Feature/Admin/ImagePreviewTest.php`

- [ ] **Step 1: Write the test**

Create `tests/Feature/Admin/ImagePreviewTest.php`:

```php
<?php

use App\Livewire\Admin\News\NewsForm;
use App\Livewire\Admin\Pages\PageForm;
use App\Livewire\Admin\Videos\VideoIndex;
use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    app()->setLocale('ru');
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

describe('Live image preview', function () {
    it('renders an img tag for a staged news cover upload', function () {
        Livewire::test(NewsForm::class)
            ->set('cover_uploads.kr', UploadedFile::fake()->image('preview.jpg', 800, 600))
            ->assertSeeHtml('<img');
    })->group('feature', 'admin');

    it('renders an img tag for a staged video thumbnail upload', function () {
        Livewire::test(VideoIndex::class)
            ->call('startCreate')
            ->set('imageUpload', UploadedFile::fake()->image('thumb.jpg', 800, 600))
            ->assertSeeHtml('<img');
    })->group('feature', 'admin');

    it('renders an img tag for a staged page image upload', function () {
        Livewire::test(PageForm::class)
            ->set('imageUpload', UploadedFile::fake()->image('hero.jpg', 800, 600))
            ->assertSeeHtml('<img');
    })->group('feature', 'admin');
});
```

- [ ] **Step 2: Run the test**

Run: `php artisan test tests/Feature/Admin/ImagePreviewTest.php`
Expected: 3 tests pass.

---

## Task 11: Format and run full test suite

**Files:** none

- [ ] **Step 1: Run Pint**

Run: `vendor/bin/pint --dirty --format agent`
Expected: `{"tool":"pint","result":"passed"}` or `"fixed"` for any new files.

- [ ] **Step 2: Run full test suite**

Run: `php artisan test --compact`
Expected: all tests pass (253 baseline + 5 new optimizer + 3 new preview = ~261). No regressions.

---

## Task 12: Commit

**Files:** none

- [ ] **Step 1: Stage all changes**

Run: `git status --short`
Expected: M to composer.json/composer.lock, M to HandlesImageUploads.php and InlineImageController.php, M to the 3 form views, A for ImageOptimizer.php and the 2 new test files.

- [ ] **Step 2: Commit**

```bash
git add -A
git commit -m "$(cat <<'EOF'
feat(admin): optimize uploaded images + live thumbnail preview

- New App\Support\ImageOptimizer (Intervention/Image v3 + GD): scales
  down to 1920px max, re-encodes at JPEG q82 / PNG lossless / WebP q82,
  strips EXIF, applies orientation, falls back to original on failure.
- HandlesImageUploads trait routes every cover upload through the
  optimizer so News/Pages/Videos covers and TinyMCE inline images all
  shrink consistently.
- News, Videos, and Pages forms render a 140x96 thumbnail preview the
  moment a file is picked (powered by Livewire temporaryUrl()), then
  the saved cover after save.
- 5 unit tests for ImageOptimizer (resize, no-upscale, GIF/SVG passthrough,
  corrupt-input fallback) plus 3 markup-level preview tests.

Co-Authored-By: Claude Opus 4.7 (1M context) <noreply@anthropic.com>
EOF
)"
```

- [ ] **Step 3: Verify clean tree**

Run: `git status`
Expected: working tree clean, branch ahead of origin by 1 commit (or however many it was previously + 1).

---
