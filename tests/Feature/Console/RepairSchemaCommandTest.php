<?php

describe('db:repair-schema command', function () {
    it('refuses to run on non-PostgreSQL connections', function () {
        // Test DB is sqlite per phpunit.xml; the command should bail out cleanly.
        $this->artisan('db:repair-schema')
            ->expectsOutputToContain('only supports PostgreSQL')
            ->assertExitCode(1);
    })->group('feature', 'console');

    it('exits cleanly when called with --dry-run on non-PostgreSQL', function () {
        $this->artisan('db:repair-schema', ['--dry-run' => true])
            ->expectsOutputToContain('only supports PostgreSQL')
            ->assertExitCode(1);
    })->group('feature', 'console');
});
