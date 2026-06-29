<?php

use App\Models\News;
use App\Models\User;

// `viewer` is a removed role — retained here as a fixture proving any
// unrecognized / non-staff role is denied across the admin panel (deny-by-default).
describe('removed / unrecognized role', function () {
    it('cannot access /admin and gets 403', function () {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($viewer)->get('/admin')->assertForbidden();
        $this->actingAs($viewer)->get(route('admin.news.index'))->assertForbidden();
        $this->actingAs($viewer)->get(route('admin.users.index'))->assertForbidden();
        $this->actingAs($viewer)->get(route('admin.pages.index'))->assertForbidden();
        $this->actingAs($viewer)->get(route('admin.categories.index'))->assertForbidden();
        $this->actingAs($viewer)->get(route('admin.activity.index'))->assertForbidden();
    })->group('feature', 'admin');
})->group('feature', 'admin');

describe('editor role', function () {
    it('can access news, pages, categories, activity but not users', function () {
        $editor = User::factory()->create(['role' => 'editor']);

        $this->actingAs($editor)->get(route('admin.news.index'))->assertOk();
        $this->actingAs($editor)->get(route('admin.pages.index'))->assertOk();
        $this->actingAs($editor)->get(route('admin.categories.index'))->assertOk();
        $this->actingAs($editor)->get(route('admin.activity.index'))->assertOk();
        $this->actingAs($editor)->get(route('admin.users.index'))->assertForbidden();
    })->group('feature', 'admin');
})->group('feature', 'admin');

describe('admin role', function () {
    it('can access every admin route', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('admin.news.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.pages.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.categories.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.activity.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.users.index'))->assertOk();
    })->group('feature', 'admin');

    it('can edit any news regardless of owner', function () {
        $admin = User::factory()->create(['role' => 'admin']);
        $other = User::factory()->create(['role' => 'editor']);
        $news = News::factory()->create(['user_id' => $other->id]);

        $this->actingAs($admin)
            ->get(route('admin.news.edit', $news))
            ->assertOk();
    })->group('feature', 'admin');
})->group('feature', 'admin');
