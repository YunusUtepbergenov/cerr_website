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
        expect(filesize($target))->toBe(filesize($source->getRealPath()));
    })->group('unit', 'image');

    it('refuses SVG and writes nothing (defense in depth against stored XSS)', function () {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"><script>alert(1)</script></svg>';
        $sourcePath = $this->tmpDir.'/source.svg';
        file_put_contents($sourcePath, $svg);
        $source = new UploadedFile($sourcePath, 'logo.svg', 'image/svg+xml', null, true);
        $target = $this->tmpDir.'/logo.svg';

        expect(fn () => $this->optimizer->optimize($source, $target))
            ->toThrow(RuntimeException::class);
        expect(file_exists($target))->toBeFalse();
    })->group('unit', 'image', 'security');

    it('falls back to copying the original when a valid image cannot be processed', function () {
        // A real JPEG signature (so it sniffs as image/jpeg and passes the
        // content check) but an unprocessable body, to exercise the fallback.
        $corrupt = "\xFF\xD8\xFF\xE0\x00\x10JFIF\x00\x01\x02\x00\x00\x01\x00\x01\x00\x00".str_repeat("\x00", 64);
        $sourcePath = $this->tmpDir.'/corrupt.jpg';
        file_put_contents($sourcePath, $corrupt);
        $source = new UploadedFile($sourcePath, 'corrupt.jpg', 'image/jpeg', null, true);
        $target = $this->tmpDir.'/corrupt-out.jpg';

        $this->optimizer->optimize($source, $target);

        expect(file_exists($target))->toBeTrue()
            ->and(file_get_contents($target))->toBe($corrupt);
    })->group('unit', 'image');

    it('safeExtension derives the extension from content, not the .php client name', function () {
        // 1x1 transparent GIF bytes, presented with an executable client filename.
        $gif = base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7');
        $path = $this->tmpDir.'/real.gif';
        file_put_contents($path, $gif);
        $file = new UploadedFile($path, 'evil.php', 'image/gif', null, true);

        expect(ImageOptimizer::safeExtension($file))->toBe('gif');
    })->group('unit', 'image', 'security');

    it('safeExtension rejects a php script disguised as .jpg', function () {
        $path = $this->tmpDir.'/shell.jpg';
        file_put_contents($path, "<?php echo 'pwned'; ?>");
        $file = new UploadedFile($path, 'shell.jpg', 'image/jpeg', null, true);

        expect(fn () => ImageOptimizer::safeExtension($file))->toThrow(RuntimeException::class);
    })->group('unit', 'image', 'security');
});
