<?php

use Illuminate\Support\Facades\Storage;

it('builds a resolvable public storage url, never a schemeless host', function () {
    $url = Storage::disk('public')->url('news/covers/example.png');

    // An <img src> must be absolute or host-relative; a bare "host/storage/..."
    // (which happens when APP_URL has no scheme) resolves relative to the
    // current path and 404s, so the media library shows broken thumbnails.
    expect($url)->toMatch('#^(/|https?://)#');
})->group('feature', 'admin');
