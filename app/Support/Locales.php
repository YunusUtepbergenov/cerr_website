<?php

namespace App\Support;

class Locales
{
    /**
     * Short display labels for the content locales, shown in the admin
     * language switchers and chips. 'kr' is Uzbek written in Cyrillic, so it
     * is labelled in that script rather than with its internal code.
     *
     * @var array<string, string>
     */
    public const LABELS = [
        'uz' => 'UZ',
        'kr' => 'ЎЗ',
        'ru' => 'RU',
        'en' => 'EN',
    ];

    public static function label(string $locale): string
    {
        return self::LABELS[$locale] ?? mb_strtoupper($locale);
    }
}
