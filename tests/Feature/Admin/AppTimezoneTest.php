<?php

describe('Application timezone', function () {
    it('uses Tashkent time so scheduling matches local expectations', function () {
        expect(config('app.timezone'))->toBe('Asia/Tashkent')
            ->and(now()->utcOffset())->toBe(300);
    })->group('feature', 'admin');
});
