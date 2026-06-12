<?php

use App\Models\User;

describe('Admin layout assets', function () {
    it('serves admin styles via vite instead of an inline style block', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->get('/admin');

        $response->assertOk();
        // The shell markup is still there…
        $response->assertSee('admin-shell', false);
        // …but the big inline stylesheet is gone (this selector only existed inline).
        $response->assertDontSee('.admin-sidebar {', false);
        // And the sidebar toggle script moved out too.
        $response->assertDontSee("localStorage.getItem(STORAGE_KEY)", false);
    })->group('feature', 'admin');
});
