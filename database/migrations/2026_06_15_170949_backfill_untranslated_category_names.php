<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Seeded categories that shipped with a slug but no translation rows.
     *
     * The public site and the news index fell back to showing the bare slug
     * for these, because category_translations had no entry in any language.
     * Backfill a name for every site locale (kr = Cyrillic Uzbek, uz = Latin
     * Uzbek, ru, en), derived from the slug's meaning.
     *
     * @var array<string, array<string, string>>
     */
    private const NAMES = [
        'trendlar' => ['kr' => 'Трендлар', 'uz' => 'Trendlar', 'ru' => 'Тренды', 'en' => 'Trends'],
        'bozorlar' => ['kr' => 'Бозорлар', 'uz' => 'Bozorlar', 'ru' => 'Рынки', 'en' => 'Markets'],
        'mulohaza' => ['kr' => 'Мулоҳаза', 'uz' => 'Mulohaza', 'ru' => 'Мнения', 'en' => 'Commentary'],
        'events' => ['kr' => 'Тадбирлар', 'uz' => 'Tadbirlar', 'ru' => 'События', 'en' => 'Events'],
    ];

    /**
     * Insert a translation for each (category, locale) pair that is missing
     * one. Existing translations are never overwritten, so this is safe to run
     * repeatedly and on top of any names an editor has already filled in.
     */
    public function up(): void
    {
        $now = now();

        foreach (self::NAMES as $slug => $names) {
            $categoryId = DB::table('categories')->where('slug', $slug)->value('id');

            if (! $categoryId) {
                continue;
            }

            foreach ($names as $language => $name) {
                $alreadyTranslated = DB::table('category_translations')
                    ->where('category_id', $categoryId)
                    ->where('language', $language)
                    ->exists();

                if ($alreadyTranslated) {
                    continue;
                }

                DB::table('category_translations')->insert([
                    'category_id' => $categoryId,
                    'language' => $language,
                    'name' => $name,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]);
            }
        }
    }

    /**
     * Irreversible: once names exist they are indistinguishable from any other
     * editor-supplied translation, and deleting them could discard later edits.
     */
    public function down(): void
    {
        // No-op — see up() for why this correction is not safely reversible.
    }
};
