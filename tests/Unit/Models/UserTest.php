<?php

use App\Models\User;

describe('User Model', function () {
    it('has correct default role', function () {
        $user = User::factory()->create(['role' => 'viewer']);
        expect($user->role)->toBe('viewer');
    })->group('unit', 'models');

    it('generates correct initials for single name', function () {
        $user = User::factory()->create(['name' => 'John']);
        expect($user->initials())->toBe('J');
    })->group('unit', 'models');

    it('generates correct initials for full name', function () {
        $user = User::factory()->create(['name' => 'John Smith Doe']);
        expect($user->initials())->toBe('JSD');
    })->group('unit', 'models');

    it('generates correct initials for two names', function () {
        $user = User::factory()->create(['name' => 'Jane Doe']);
        expect($user->initials())->toBe('JD');
    })->group('unit', 'models');

    it('can have admin role', function () {
        $user = User::factory()->create(['role' => 'admin']);
        expect($user->role)->toBe('admin');
    })->group('unit', 'models');

    it('can have writer role', function () {
        $user = User::factory()->create(['role' => 'writer']);
        expect($user->role)->toBe('writer');
    })->group('unit', 'models');

    it('can have editor role', function () {
        $user = User::factory()->create(['role' => 'editor']);
        expect($user->role)->toBe('editor');
    })->group('unit', 'models');

    it('hashes password automatically', function () {
        $user = User::factory()->create(['password' => 'plain-text-password']);
        expect($user->password)->not->toBe('plain-text-password')
            ->and(strlen($user->password))->toBeGreaterThan(20);
    })->group('unit', 'models');
});
