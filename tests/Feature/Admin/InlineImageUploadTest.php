<?php

use App\Models\User;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

describe('Inline image upload', function () {
    it('returns location for an uploaded image', function () {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.inline-image.store'), [
            'file' => UploadedFile::fake()->image('inline.png'),
        ]);

        $response->assertOk()
            ->assertJsonStructure(['location']);

        $stored = Storage::disk('public')->files('news/inline');
        expect($stored)->not->toBeEmpty();
    })->group('feature', 'admin');

    it('rejects non-image uploads', function () {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        $response = $this->actingAs($admin)->post(route('admin.inline-image.store'), [
            'file' => UploadedFile::fake()->create('doc.pdf', 10, 'application/pdf'),
        ]);

        $response->assertStatus(302); // redirected back with validation errors
    })->group('feature', 'admin');

    it('stores an image-content upload under a safe extension, not the executable client one', function () {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        // Real GIF content, but a client filename with an executable extension
        // that slips past Laravel's own .php/.phtml block list (e.g. .pht).
        // The stored file must take its extension from the sniffed content, so
        // it can never be written as a script under the web-served disk (RCE).
        $gifPath = sys_get_temp_dir().'/rce-'.uniqid().'.gif';
        file_put_contents($gifPath, base64_decode('R0lGODlhAQABAIAAAAAAAP///yH5BAEAAAAALAAAAAABAAEAAAIBRAA7'));
        $file = new UploadedFile($gifPath, 'evil.pht', 'image/gif', 0, true);

        $response = $this->actingAs($admin)->post(route('admin.inline-image.store'), ['file' => $file]);

        $response->assertOk();
        $stored = Storage::disk('public')->files('news/inline');
        expect($stored)->not->toBeEmpty();
        foreach ($stored as $path) {
            expect($path)->toEndWith('.gif')
                ->and($path)->not->toContain('.pht');
        }
        @unlink($gifPath);
    })->group('feature', 'admin', 'security');

    it('rejects SVG uploads to prevent stored XSS', function () {
        Storage::fake('public');
        $admin = User::factory()->create(['role' => 'admin']);

        // An SVG can carry <script>; served from our origin it would execute.
        $svg = <<<'SVG'
        <svg xmlns="http://www.w3.org/2000/svg"><script>alert(document.domain)</script></svg>
        SVG;
        $file = UploadedFile::fake()->createWithContent('logo.svg', $svg);

        $response = $this->actingAs($admin)->post(route('admin.inline-image.store'), [
            'file' => $file,
        ]);

        $response->assertStatus(302); // rejected by validation
        expect(Storage::disk('public')->files('news/inline'))->toBeEmpty();
    })->group('feature', 'admin', 'security');

    it('blocks non-admins', function () {
        $user = User::factory()->create(['role' => 'viewer']);

        $this->actingAs($user)
            ->post(route('admin.inline-image.store'), ['file' => UploadedFile::fake()->image('x.png')])
            ->assertForbidden();
    })->group('feature', 'admin');
});
