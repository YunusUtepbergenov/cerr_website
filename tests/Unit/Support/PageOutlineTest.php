<?php

use App\Support\PageOutline;

describe('PageOutline', function () {
    it('leaves bodies without h2 untouched and reports no chapters', function () {
        $outline = PageOutline::build('<p>Only <strong>prose</strong></p>');

        expect($outline->html)->toBe('<p>Only <strong>prose</strong></p>')
            ->and($outline->chapters)->toBe([])
            ->and(PageOutline::build(null)->html)->toBe('');
    })->group('unit');

    it('gives every h2 an id and lists them as chapters', function () {
        $outline = PageOutline::build('<p>Intro</p><h2>Новые задачи</h2><p>a</p><h2>New tasks</h2><h2 id="custom">Kept</h2>');

        expect($outline->chapters)->toBe([
            ['id' => 'chapter-1', 'title' => 'Новые задачи'],
            ['id' => 'chapter-2', 'title' => 'New tasks'],
            ['id' => 'custom', 'title' => 'Kept'],
        ])
            ->and($outline->html)->toContain('<h2 id="chapter-1">Новые задачи</h2>')
            ->and($outline->html)->toContain('<h2 id="chapter-2">New tasks</h2>')
            ->and($outline->html)->toContain('<h2 id="custom">Kept</h2>')
            ->and($outline->html)->toStartWith('<p>Intro</p>');
    })->group('unit');

    it('keeps ids unique next to editor-set ones and skips empty headings', function () {
        $outline = PageOutline::build('<h2 id="chapter-2">Шарҳ</h2><h2>Янги</h2><h2>   </h2><h2>Ҳ</h2>');

        expect(array_column($outline->chapters, 'id'))->toBe(['chapter-2', 'chapter-2-2', 'chapter-3'])
            ->and(array_column($outline->chapters, 'title'))->toBe(['Шарҳ', 'Янги', 'Ҳ']);
    })->group('unit');
});
