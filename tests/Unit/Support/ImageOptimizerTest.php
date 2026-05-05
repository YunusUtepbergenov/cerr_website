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
