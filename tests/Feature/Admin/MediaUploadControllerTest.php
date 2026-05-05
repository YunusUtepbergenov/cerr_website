<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

beforeEach(function () {
    Storage::fake('public');
});

describe('Media upload endpoint (FilePond)', function () {
    it('stores a single image and returns the path + url', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.media.upload'), [
            'file' => UploadedFile::fake()->image('cover.jpg'),
            'folder' => 'news/covers',
        ]);

        $response->assertOk()->assertJsonStructure(['path', 'url']);
        expect($response->json('path'))->toStartWith('news/covers/');
        Storage::disk('public')->assertExists($response->json('path'));
    })->group('feature', 'admin');

    it('rejects non-image uploads', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.media.upload'), [
            'file' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
            'folder' => 'news/covers',
        ])->assertStatus(302); // validation redirect (or 422 if AJAX-aware)
    })->group('feature', 'admin');

    it('rejects unknown folders', function () {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('admin.media.upload'), [
            'file' => UploadedFile::fake()->image('x.jpg'),
            'folder' => 'something/else',
        ])->assertStatus(302);
    })->group('feature', 'admin');

    it('blocks non-admin users', function () {
        $viewer = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($viewer)->post(route('admin.media.upload'), [
            'file' => UploadedFile::fake()->image('x.jpg'),
            'folder' => 'news/covers',
        ])->assertForbidden();
    })->group('feature', 'admin');
});
