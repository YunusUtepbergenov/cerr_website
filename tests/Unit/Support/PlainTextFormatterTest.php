<?php

use App\Support\PlainTextFormatter;

describe('PlainTextFormatter', function () {
    it('detects block-level markup', function () {
        expect(PlainTextFormatter::looksLikeHtml('<p>Hi</p>'))->toBeTrue()
            ->and(PlainTextFormatter::looksLikeHtml('<ul><li>x</li></ul>'))->toBeTrue()
            ->and(PlainTextFormatter::looksLikeHtml("Plain text\n\nSecond paragraph"))->toBeFalse()
            ->and(PlainTextFormatter::looksLikeHtml('Inline <strong>bold</strong> only'))->toBeFalse();
    })->group('unit');

    it('wraps blank-line separated text into paragraphs and escapes it', function () {
        $html = PlainTextFormatter::toHtml("First <b>para</b>.\n\n\nSecond\npara continues.");

        expect($html)->toBe("<p>First &lt;b&gt;para&lt;/b&gt;.</p>\n<p>Second para continues.</p>");
    })->group('unit');

    it('turns short unpunctuated lines into h2 headings', function () {
        $html = PlainTextFormatter::toHtml("Новые задачи\n\nБольшинство задач носят широкий характер.\n\nТрансформация");

        expect($html)->toContain('<h2>Новые задачи</h2>')
            ->and($html)->toContain('<p>Большинство задач носят широкий характер.</p>')
            ->and($html)->toContain('<h2>Трансформация</h2>');
    })->group('unit');

    it('does not treat short sentences or lowercase lines as headings', function () {
        $html = PlainTextFormatter::toHtml("Марказ 1999 йилда ташкил этилган.\n\nкичик изоҳ\n\na short title that is far too long to be a heading because it runs past the limit set");

        expect($html)->not->toContain('<h2>');
    })->group('unit');

    it('groups consecutive dash items into one list', function () {
        $html = PlainTextFormatter::toHtml("Задачи:\n\n- первая;\n\n- вторая.\n\nДалее\n\n– третья\n\n• четвёртая");

        expect($html)->toBe(
            "<p>Задачи:</p>\n<ul>\n<li>первая;</li>\n<li>вторая.</li>\n</ul>\n<h2>Далее</h2>\n<ul>\n<li>третья</li>\n<li>четвёртая</li>\n</ul>"
        );
    })->group('unit');

    it('links bare URLs with the host as label', function () {
        $html = PlainTextFormatter::toHtml('Постановление «О мерах» (https://lex.uz/ru/docs/4552322) возлагает задачи.');

        expect($html)->toBe('<p>Постановление «О мерах» (<a href="https://lex.uz/ru/docs/4552322" target="_blank" rel="noopener noreferrer">lex.uz</a>) возлагает задачи.</p>');
    })->group('unit');

    it('puts a bare URL in parentheses so the host label reads inline', function () {
        $html = PlainTextFormatter::toHtml('Қарорига https://lex.uz/ru/docs/4552322 мувофиқ.');

        expect($html)->toBe('<p>Қарорига (<a href="https://lex.uz/ru/docs/4552322" target="_blank" rel="noopener noreferrer">lex.uz</a>) мувофиқ.</p>');
    })->group('unit');

    it('rejoins fragments split around a URL into the previous paragraph', function () {
        $text = "Президентининг ПҚ-4471-сонли қарори\n\nhttps://lex.uz/ru/docs/4552322\n\n қабул қилинди.\n\nҚарорига\n\nhttps://lex.uz/ru/docs/5576378\n мувофиқ вазифалар юклатилди.";

        $html = PlainTextFormatter::toHtml($text);

        expect($html)->toBe(
            '<p>Президентининг ПҚ-4471-сонли қарори (<a href="https://lex.uz/ru/docs/4552322" target="_blank" rel="noopener noreferrer">lex.uz</a>) қабул қилинди.</p>'."\n"
            .'<p>Қарорига (<a href="https://lex.uz/ru/docs/5576378" target="_blank" rel="noopener noreferrer">lex.uz</a>) мувофиқ вазифалар юклатилди.</p>'
        );
    })->group('unit');

    it('returns an empty string for blank input', function () {
        expect(PlainTextFormatter::toHtml("  \n\n "))->toBe('');
    })->group('unit');
});
