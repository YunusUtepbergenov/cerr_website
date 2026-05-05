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
