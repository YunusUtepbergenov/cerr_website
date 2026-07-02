<?php

use App\Models\NewsTranslation;

describe('NewsTranslation reading time', function () {
    it('returns at least one minute for empty content', function () {
        $t = new NewsTranslation(['content' => '']);
        expect($t->readingTime())->toBe(1);
    })->group('unit', 'models');

    it('estimates one minute for short content', function () {
        $t = new NewsTranslation(['content' => '<p>Just a few words here.</p>']);
        expect($t->readingTime())->toBe(1);
    })->group('unit', 'models');

    it('scales with word count at about 200 wpm', function () {
        $content = '<p>'.str_repeat('word ', 400).'</p>';
        $t = new NewsTranslation(['content' => $content]);
        expect($t->readingTime())->toBe(2);
    })->group('unit', 'models');
});
