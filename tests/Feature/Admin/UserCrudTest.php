<?php

use App\Livewire\Admin\Users\UserIndex;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Js;
use Livewire\Livewire;

beforeEach(function () {
    $this->admin = User::factory()->create(['role' => 'admin']);
    $this->actingAs($this->admin);
    app()->setLocale('ru');
});

describe('User CRUD', function () {
    it('creates a user', function () {
        Livewire::test(UserIndex::class)
            ->call('startCreate')
            ->set('name', 'New Editor')
            ->set('email', 'editor@x.test')
            ->set('role', 'editor')
            ->set('password', 'Str0ng#Pass2026')
            ->call('save')
            ->assertHasNoErrors();

        $u = User::where('email', 'editor@x.test')->first();
        expect($u)->not->toBeNull()->and($u->role)->toBe('editor');
        expect(Hash::check('Str0ng#Pass2026', $u->password))->toBeTrue();
    })->group('feature', 'admin');

    it('rejects a weak password', function () {
        Livewire::test(UserIndex::class)
            ->call('startCreate')
            ->set('name', 'Weak')
            ->set('email', 'weak@x.test')
            ->set('role', 'editor')
            ->set('password', 'secret-pass')
            ->call('save')
            ->assertHasErrors(['password']);

        expect(User::where('email', 'weak@x.test')->exists())->toBeFalse();
    })->group('feature', 'admin');

    it('rejects duplicate email', function () {
        User::factory()->create(['email' => 'taken@x.test']);

        Livewire::test(UserIndex::class)
            ->call('startCreate')
            ->set('name', 'X')
            ->set('email', 'taken@x.test')
            ->set('password', 'Str0ng#Pass2026')
            ->call('save')
            ->assertHasErrors(['email']);
    })->group('feature', 'admin');

    it('lets you edit a user without re-entering the password', function () {
        $editor = User::factory()->create(['role' => 'editor']);

        Livewire::test(UserIndex::class)
            ->call('edit', $editor->id)
            ->set('name', 'Renamed Editor')
            ->call('save')
            ->assertHasNoErrors();

        expect($editor->fresh()->name)->toBe('Renamed Editor');
    })->group('feature', 'admin');

    it('rejects a weak new password on edit', function () {
        $editor = User::factory()->create(['role' => 'editor']);

        Livewire::test(UserIndex::class)
            ->call('edit', $editor->id)
            ->set('password', 'weak')
            ->call('save')
            ->assertHasErrors(['password']);
    })->group('feature', 'admin');

    it('blocks self-demotion', function () {
        $component = Livewire::test(UserIndex::class);
        $component->set('currentUserId', $this->admin->id);
        $component->assertSet('currentUserId', $this->admin->id);
        $component->call('edit', $this->admin->id);
        $component->assertSet('editingId', $this->admin->id);
        $component->set('role', 'editor');
        $component->call('save');
        $component->assertHasErrors(['role']);

        expect($this->admin->fresh()->role)->toBe('admin');
    })->group('feature', 'admin');

    it('blocks self-deletion', function () {
        Livewire::test(UserIndex::class)
            ->set('currentUserId', $this->admin->id)
            ->call('delete', $this->admin->id);

        expect(User::find($this->admin->id))->not->toBeNull();
    })->group('feature', 'admin');

    it('resets password with a new random value', function () {
        $other = User::factory()->create();
        $oldHash = $other->password;

        Livewire::test(UserIndex::class)
            ->call('resetPassword', $other->id);

        expect($other->fresh()->password)->not->toBe($oldHash);
    })->group('feature', 'admin');

    it('escapes the generated password for the clipboard copy button', function () {
        $nasty = 'a\\b\'c"d>e';

        Livewire::test(UserIndex::class)
            ->set('generatedPassword', $nasty)
            ->assertSee('writeText('.Js::from($nasty)->toHtml().')', false);
    })->group('feature', 'admin');
});
