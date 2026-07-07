<?php

namespace App\Support;

use Illuminate\Http\UploadedFile;
use Intervention\Image\Drivers\Gd\Driver;
use Intervention\Image\ImageManager;
use RuntimeException;
use Throwable;

class ImageOptimizer
{
    /**
     * Allowed image MIME types mapped to the ONLY extensions we will ever store.
     *
     * The stored extension is always derived from the file's sniffed content —
     * never the attacker-controlled client filename — so a polyglot uploaded as
     * "evil.php" can never be written with a .php (or any executable) extension
     * on the web-served public disk. SVG is deliberately absent: it can carry
     * script and must never reach a served disk.
     *
     * @var array<string, string>
     */
    private const SAFE_TYPES = [
        'image/jpeg' => 'jpg',
        'image/pjpeg' => 'jpg',
        'image/png' => 'png',
        'image/gif' => 'gif',
        'image/webp' => 'webp',
    ];

    /**
     * Resolve the safe storage extension for an uploaded image from its actual
     * (content-sniffed) MIME type.
     *
     * @throws RuntimeException when the file is not an allowed raster image.
     */
    public static function safeExtension(UploadedFile $file): string
    {
        $mime = strtolower((string) $file->getMimeType());

        if (! isset(self::SAFE_TYPES[$mime])) {
            throw new RuntimeException('Unsupported image type: '.($mime !== '' ? $mime : 'unknown'));
        }

        return self::SAFE_TYPES[$mime];
    }

    /**
     * Optimize an uploaded image and write it to $absolutePath.
     *
     * - JPEG/PNG/WebP: scaled down to 1920px on the longer side, re-encoded at
     *   quality 82, EXIF stripped, orientation baked into pixels (re-encoding
     *   also strips any payload appended to a polyglot).
     * - GIF: copied unchanged (preserves animation).
     * - Anything that is not an allowed raster image (including SVG): rejected.
     * - On a processing failure of an otherwise-valid image: copy the original
     *   file and log a warning.
     *
     * @throws RuntimeException when the file is not an allowed raster image.
     */
    public function optimize(UploadedFile $file, string $absolutePath): void
    {
        $extension = self::safeExtension($file);

        if ($extension === 'gif') {
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
