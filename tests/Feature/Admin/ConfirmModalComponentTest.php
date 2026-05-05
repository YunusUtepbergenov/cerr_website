<?php

use App\Models\News;
use App\Models\User;

describe('Admin confirm modal', function () {
    it('mounts on every admin page', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get(route('admin.dashboard'));

        $response->assertOk()
            ->assertSee('@open-confirm.window', false);
    })->group('feature', 'admin');

    it('replaces wire:confirm on news delete buttons', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        News::factory()->create()->translations()->create([
            'lang' => 'kr',
            'title' => 't',
            'short_description' => 's',
            'content' => '<p>c</p>',
            'image_url' => '',
        ]);

        $response = $this->actingAs($admin)->get(route('admin.news.index'));

        $response->assertOk()
            ->assertSee('open-confirm', false)
            ->assertDontSee('wire:confirm', false);
    })->group('feature', 'admin');
});
