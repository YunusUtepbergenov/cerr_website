<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;

new
#[Layout('components.layouts.auth')]
#[Title('Вход')]
class extends Component
{
    #[Validate('required|string|email')]
    public string $email = '';

    #[Validate('required|string')]
    public string $password = '';

    public bool $remember = false;

    /** Per-account limiter: stops distributed brute force on one email, even from many IPs. */
    private const MAX_EMAIL_ATTEMPTS = 5;

    private const EMAIL_DECAY_SECONDS = 900;

    /** Per-IP limiter: stops credential stuffing across many accounts from one source. */
    private const MAX_IP_ATTEMPTS = 20;

    private const IP_DECAY_SECONDS = 300;

    public function login(): void
    {
        $this->validate();

        $this->ensureIsNotRateLimited();

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($this->emailThrottleKey(), self::EMAIL_DECAY_SECONDS);
            RateLimiter::hit($this->ipThrottleKey(), self::IP_DECAY_SECONDS);

            throw ValidationException::withMessages([
                'email' => __('admin.auth.invalid_credentials'),
            ]);
        }

        RateLimiter::clear($this->emailThrottleKey());
        RateLimiter::clear($this->ipThrottleKey());

        $user = Auth::user();
        $user->forceFill(['last_login_at' => now()])->save();

        if (request()->hasSession()) {
            request()->session()->regenerate();
        }

        // Accountants can only reach Open Data; send them there unconditionally
        // rather than honoring an intended URL (e.g. /admin), which EnsureAdmin
        // would 403.
        if ($user?->isAccountant()) {
            $this->redirect(route('admin.open-data.index'), navigate: false);

            return;
        }

        $this->redirectIntended($user?->isAdmin() ? route('admin.dashboard') : '/', navigate: false);
    }

    /**
     * Block the attempt when either the per-account or per-IP limiter is tripped,
     * reporting the longer of the two remaining lockouts.
     */
    protected function ensureIsNotRateLimited(): void
    {
        $emailLocked = RateLimiter::tooManyAttempts($this->emailThrottleKey(), self::MAX_EMAIL_ATTEMPTS);
        $ipLocked = RateLimiter::tooManyAttempts($this->ipThrottleKey(), self::MAX_IP_ATTEMPTS);

        if (! $emailLocked && ! $ipLocked) {
            return;
        }

        $seconds = max(
            $emailLocked ? RateLimiter::availableIn($this->emailThrottleKey()) : 0,
            $ipLocked ? RateLimiter::availableIn($this->ipThrottleKey()) : 0,
        );

        throw ValidationException::withMessages([
            'email' => __('admin.auth.too_many_attempts', ['seconds' => $seconds]),
        ]);
    }

    protected function emailThrottleKey(): string
    {
        return 'login:'.sha1(Str::lower($this->email));
    }

    protected function ipThrottleKey(): string
    {
        return 'login-ip:'.sha1((string) request()->ip());
    }
}; ?>

<div>
    <div class="head">
        <h1>{{ __('admin.auth.sign_in') }}</h1>
        <p>{{ __('admin.auth.subtitle') }}</p>
    </div>

    <form wire:submit.prevent="login">
        <div class="field">
            <label for="email">{{ __('admin.auth.email') }}</label>
            <div class="input-wrap">
                <input id="email" type="email" wire:model="email" placeholder="you@cerr.uz" autocomplete="username"
                    class="@error('email') is-invalid @enderror" autofocus required>
            </div>
            @error('email') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <div class="field" x-data="{ show: false }">
            <label for="password">{{ __('admin.auth.password') }}</label>
            <div class="input-wrap has-toggle">
                <input id="password" type="password" :type="show ? 'text' : 'password'" wire:model="password"
                    placeholder="••••••••" autocomplete="current-password"
                    class="@error('password') is-invalid @enderror" required>
                <button type="button" class="toggle-pass" @click="show = !show" tabindex="-1"
                    :aria-label="show ? @js(__('admin.auth.hide_password')) : @js(__('admin.auth.show_password'))">
                    <svg x-show="!show" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7Z"/><circle cx="12" cy="12" r="3"/></svg>
                    <svg x-show="show" x-cloak viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"><path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
                </button>
            </div>
            @error('password') <div class="field-error">{{ $message }}</div> @enderror
        </div>

        <label class="remember">
            <input type="checkbox" wire:model="remember">
            <span class="box">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3" stroke-linecap="round" stroke-linejoin="round"><polyline points="20 6 9 17 4 12"/></svg>
            </span>
            <span class="lbl">{{ __('admin.auth.remember_me') }}</span>
        </label>

        <button type="submit" class="submit">
            <span wire:loading.remove wire:target="login">{{ __('admin.auth.sign_in_button') }}</span>
            <span wire:loading wire:target="login">{{ __('admin.auth.signing_in') }}</span>
        </button>
    </form>

    <div class="footer">© {{ now()->year }} CERR</div>
</div>
