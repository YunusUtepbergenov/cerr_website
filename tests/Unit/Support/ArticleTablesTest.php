<?php

use App\Support\ArticleTables;

describe('ArticleTables', function () {
    $wordTable = '<p>Intro</p><table><tbody>'
        .'<tr><td><p>Минтақалар</p></td><td><p>ИФИ</p></td><td><p>Ҳисоблар бўйича</p><p>операциялар компоненти</p></td></tr>'
        .'<tr><td><p>Жиззах</p><p>вилояти</p></td><td><p>↑1501</p></td><td><p>↓0,9753</p></td></tr>'
        .'<tr><td><p>Тошкент шаҳри</p></td><td><p>↑1440</p></td><td><p>1,1157</p></td></tr>'
        .'</tbody></table><p>Outro</p>';

    it('leaves content without tables untouched', function () {
        expect(ArticleTables::enhance('<p>Hello <strong>world</strong></p>'))->toBe('<p>Hello <strong>world</strong></p>')
            ->and(ArticleTables::enhance(null))->toBe('')
            ->and(ArticleTables::enhance(''))->toBe('');
    })->group('unit');

    it('promotes the first row of a headerless table into a thead', function () use ($wordTable) {
        $out = ArticleTables::enhance($wordTable);

        expect($out)->toContain('<thead><tr><th scope="col">Минтақалар</th>')
            ->and($out)->toContain('<th scope="col" class="num">Ҳисоблар бўйича операциялар компоненти</th>')
            ->and(substr_count($out, '<th scope="col"'))->toBe(3)
            ->and($out)->toContain('<p>Intro</p>')
            ->and($out)->toContain('<p>Outro</p>');
    })->group('unit');

    it('joins paragraphs inside cells into one flowing value', function () use ($wordTable) {
        $out = ArticleTables::enhance($wordTable);

        expect($out)->toContain('>Жиззах вилояти</td>')
            ->and($out)->not->toMatch('/<t[dh][^>]*>\s*<p>/');
    })->group('unit');

    it('marks numeric cells, trend arrows and row labels', function () use ($wordTable) {
        $out = ArticleTables::enhance($wordTable);

        expect($out)->toContain('<td class="label">Жиззах вилояти</td>')
            ->and($out)->toContain('<td class="num up"><span class="trend" aria-hidden="true">↑</span>1501</td>')
            ->and($out)->toContain('<td class="num down"><span class="trend" aria-hidden="true">↓</span>0,9753</td>')
            ->and($out)->toContain('<td class="num">1,1157</td>')
            ->and($out)->toContain('<th scope="col" class="num">ИФИ</th>')
            ->and($out)->toContain('<th scope="col">Минтақалар</th>');
    })->group('unit');

    it('wraps every table in a scroll container exactly once', function () use ($wordTable) {
        $once = ArticleTables::enhance($wordTable);
        $twice = ArticleTables::enhance($once);

        expect(substr_count($once, '<div class="article-table"><table>'))->toBe(1)
            ->and($twice)->toBe($once);
    })->group('unit');

    it('keeps an existing thead and does not treat text cells as numbers', function () {
        $html = '<table><thead><tr><th>Year</th><th>Note</th></tr></thead><tbody><tr><td>2024</td><td>Growth of 5%</td></tr></tbody></table>';
        $out = ArticleTables::enhance($html);

        expect(substr_count($out, '<thead>'))->toBe(1)
            ->and($out)->toContain('<td class="num">2024</td>')
            ->and($out)->toContain('<td>Growth of 5%</td>')
            ->and($out)->not->toContain('class="label"');
    })->group('unit');
});
