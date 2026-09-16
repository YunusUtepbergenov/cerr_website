<?php

use Illuminate\Support\Facades\Route;

describe('Trusted proxies', function () {
    beforeEach(function () {
        Route::get('/_test/proxy-probe', fn () => response()->json([
            'secure' => request()->isSecure(),
            'ip' => request()->ip(),
        ]));
    });

    it('treats requests forwarded as https by the TLS edge as secure', function () {
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.20'])
            ->get('/_test/proxy-probe', ['X-Forwarded-Proto' => 'https'])
            ->assertOk()
            ->assertJson(['secure' => true]);
    })->group('feature', 'middleware');

    it('resolves the client ip from the forwarded for header', function () {
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.20'])
            ->get('/_test/proxy-probe', ['X-Forwarded-For' => '203.0.113.7'])
            ->assertOk()
            ->assertJson(['ip' => '203.0.113.7']);
    })->group('feature', 'middleware');

    it('keeps plain http requests insecure without a forwarded proto header', function () {
        $this->withServerVariables(['REMOTE_ADDR' => '192.168.1.20'])
            ->get('/_test/proxy-probe')
            ->assertOk()
            ->assertJson(['secure' => false, 'ip' => '192.168.1.20']);
    })->group('feature', 'middleware');
});
