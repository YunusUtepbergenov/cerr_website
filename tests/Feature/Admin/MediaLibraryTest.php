<?php

use App\Livewire\Admin\Media\MediaIndex;
use App\Models\User;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;

beforeEach(function () {
    Storage::fake('public');
    $this->actingAs(User::factory()->create(['role' => 'admin']));
});

describe('Media library', function () {
    it('lists files from managed folders', function () {
        Storage::disk('public')->put('news/covers/a.jpg', 'fake');
        Storage::disk('public')->put('news/inline/b.png', 'fake');

        Livewire::test(MediaIndex::class)
            ->assertSee('a.jpg')
            ->assertSee('b.png');
    })->group('feature', 'admin');

    it('deletes a file from disk', function () {
        Storage::disk('public')->put('news/covers/del.jpg', 'fake');

        Livewire::test(MediaIndex::class)->call('delete', 'news/covers/del.jpg');

        Storage::disk('public')->assertMissing('news/covers/del.jpg');
    })->group('feature', 'admin');

    it('does not delete paths outside managed folders', function () {
        Storage::disk('public')->put('something/else.jpg', 'fake');

        Livewire::test(MediaIndex::class)->call('delete', 'something/else.jpg');

        Storage::disk('public')->assertExists('something/else.jpg');
    })->group('feature', 'admin');

    it('does not double-escape the FilePond idle label', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.media.index'));

        $response->assertOk();
        // The raw-escaped span must never reach the page as text.
        $response->assertDontSee('&lt;span class=&quot;filepond--label-action&quot;&gt;', false);
        $response->assertDontSee('&lt;span', false);
    })->group('feature', 'admin');
});
