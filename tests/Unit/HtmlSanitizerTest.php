<?php

use App\Support\HtmlSanitizer;

describe('HtmlSanitizer', function () {
    it('returns empty for null and blank input', function () {
        expect(HtmlSanitizer::sanitize(null))->toBe('')
            ->and(HtmlSanitizer::sanitize(''))->toBe('')
            ->and(HtmlSanitizer::sanitize('   '))->toBe('');
    })->group('unit', 'security');

    it('strips script tags', function () {
        $out = HtmlSanitizer::sanitize('<p>Hi</p><script>alert(1)</script>');
        expect($out)->not->toContain('<script')
            ->and($out)->not->toContain('alert(1)')
            ->and($out)->toContain('<p>Hi</p>');
    })->group('unit', 'security');

    it('strips inline event handlers', function () {
        $out = HtmlSanitizer::sanitize('<a href="https://example.com" onclick="alert(1)">x</a>');
        expect($out)->not->toContain('onclick')
            ->and($out)->toContain('href="https://example.com"');
    })->group('unit', 'security');

    it('strips javascript: URLs', function () {
        $out = HtmlSanitizer::sanitize('<a href="javascript:alert(1)">x</a>');
        expect($out)->not->toContain('javascript:');
    })->group('unit', 'security');

    it('strips data: URLs', function () {
        $out = HtmlSanitizer::sanitize('<img src="data:text/html,<script>alert(1)</script>">');
        expect($out)->not->toContain('data:');
    })->group('unit', 'security');

    it('removes iframes, objects and embeds', function () {
        $out = HtmlSanitizer::sanitize('<iframe src="https://evil"></iframe><object></object><embed>');
        expect($out)->not->toContain('<iframe')
            ->and($out)->not->toContain('<object')
            ->and($out)->not->toContain('<embed');
    })->group('unit', 'security');

    it('removes form/input/button elements', function () {
        $out = HtmlSanitizer::sanitize('<form action="/x"><input name="a"><button>go</button></form>');
        expect($out)->not->toContain('<form')
            ->and($out)->not->toContain('<input')
            ->and($out)->not->toContain('<button');
    })->group('unit', 'security');

    it('preserves common rich-text elements', function () {
        $html = '<h2>Title</h2><p><strong>bold</strong> <em>italic</em></p><ul><li>one</li></ul>';
        $out = HtmlSanitizer::sanitize($html);
        expect($out)->toContain('<h2>Title</h2>')
            ->and($out)->toContain('<strong>bold</strong>')
            ->and($out)->toContain('<em>italic</em>')
            ->and($out)->toContain('<ul>')
            ->and($out)->toContain('<li>one</li>');
    })->group('unit', 'security');

    it('forces noopener noreferrer on target=_blank links', function () {
        $out = HtmlSanitizer::sanitize('<a href="https://example.com" target="_blank">x</a>');
        expect($out)->toContain('rel="noopener noreferrer"');
    })->group('unit', 'security');

    it('preserves images with safe src', function () {
        $out = HtmlSanitizer::sanitize('<img src="/uploads/a.jpg" alt="a">');
        expect($out)->toContain('<img')
            ->and($out)->toContain('src="/uploads/a.jpg"')
            ->and($out)->toContain('alt="a"');
    })->group('unit', 'security');

    it('preserves UTF-8 content', function () {
        $out = HtmlSanitizer::sanitize('<p>Ўзбекча тест</p>');
        expect($out)->toContain('Ўзбекча тест');
    })->group('unit', 'security');
});
