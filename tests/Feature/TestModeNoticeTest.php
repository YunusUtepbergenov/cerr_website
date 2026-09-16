<?php

describe('Test mode notice', function () {
    it('shows the test mode notice outside production', function () {
        $this->get('/')
            ->assertStatus(200)
            ->assertSee('class="site-notice"', false);
    })->group('feature');

    it('hides the test mode notice in production', function () {
        app()->detectEnvironment(fn () => 'production');

        $this->get('/')
            ->assertStatus(200)
            ->assertDontSee('class="site-notice"', false);
    })->group('feature');
});
