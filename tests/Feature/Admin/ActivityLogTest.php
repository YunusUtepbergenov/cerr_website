<?php

use App\Livewire\Admin\News\NewsForm;
use App\Livewire\Admin\News\NewsIndex;
use App\Livewire\Admin\Users\UserIndex;
use App\Models\Activity;
use App\Models\News;
use App\Models\User;
use Livewire\Livewire;

beforeEach(function () {
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

describe('Activity log', function () {
    it('records a row when news is created', function () {
        Livewire::test(NewsForm::class)
            ->set('slug', 'logged')
            ->set('translations.uz.title', 't')
            ->set('translations.uz.short_description', 's')
            ->set('translations.uz.content', '<p>c</p>')
            ->call('save');

        $log = Activity::where('subject_type', News::class)->latest()->first();
        expect($log)->not->toBeNull()->and($log->action)->toBe('created');
        expect($log->user_id)->toBe(auth()->id());
    })->group('feature', 'admin');

    it('records a row when news is deleted', function () {
        $n = News::factory()->create();
        Livewire::test(NewsIndex::class)->call('delete', $n->id);

        expect(Activity::where('action', 'deleted')->where('subject_id', $n->id)->exists())->toBeTrue();
    })->group('feature', 'admin');

    it('does not store password fields in changes', function () {
        $other = User::factory()->create();

        Livewire::test(UserIndex::class)
            ->call('resetPassword', $other->id);

        $log = Activity::where('action', 'reset_password')->where('subject_id', $other->id)->first();
        expect($log)->not->toBeNull();
        $changes = $log->changes ?? [];
        expect($changes)->not->toHaveKey('password');
    })->group('feature', 'admin');
});
