<?php

use App\Support\Locales;

it('labels the cyrillic uzbek locale as ЎЗ instead of its code', function () {
    expect(Locales::label('kr'))->toBe('ЎЗ');
});

it('labels the remaining locales with their uppercase code', function () {
    expect(Locales::label('uz'))->toBe('UZ')
        ->and(Locales::label('ru'))->toBe('RU')
        ->and(Locales::label('en'))->toBe('EN');
});

it('falls back to uppercasing an unknown locale', function () {
    expect(Locales::label('de'))->toBe('DE');
});
