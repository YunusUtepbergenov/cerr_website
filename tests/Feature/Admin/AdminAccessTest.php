<?php

use App\Models\User;

describe('Admin access control', function () {
    it('redirects guests to login', function () {
        $this->get('/admin')->assertRedirect(route('login'));
    })->group('feature', 'admin');

    it('forbids non-admin users', function () {
        $user = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($user)->get('/admin')->assertForbidden();
        $this->actingAs($user)->get(route('admin.news.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.categories.index'))->assertForbidden();
        $this->actingAs($user)->get(route('admin.tags.index'))->assertForbidden();
    })->group('feature', 'admin');

    it('allows admin users', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get('/admin')->assertOk();
        $this->actingAs($admin)->get(route('admin.news.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.news.create'))->assertOk();
        $this->actingAs($admin)->get(route('admin.categories.index'))->assertOk();
        $this->actingAs($admin)->get(route('admin.tags.index'))->assertOk();
    })->group('feature', 'admin');
});
