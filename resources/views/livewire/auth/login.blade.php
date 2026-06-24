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

    public function login(): void
    {
        $this->validate();

        $throttleKey = Str::transliterate(Str::lower($this->email).'|'.request()->ip());

        if (RateLimiter::tooManyAttempts($throttleKey, 5)) {
            $seconds = RateLimiter::availableIn($throttleKey);
            throw ValidationException::withMessages([
                'email' => __('admin.auth.too_many_attempts', ['seconds' => $seconds]),
            ]);
        }

        if (! Auth::attempt(['email' => $this->email, 'password' => $this->password], $this->remember)) {
            RateLimiter::hit($throttleKey);
            throw ValidationException::withMessages([
                'email' => __('admin.auth.invalid_credentials'),
            ]);
        }

        RateLimiter::clear($throttleKey);

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
}; ?>

<div>
    <h1>{{ __('admin.auth.sign_in') }}</h1>

    <form wire:submit.prevent="login">
        <div class="mb-3">
            <label class="form-label">{{ __('admin.auth.email') }}</label>
            <input type="email" wire:model="email" class="form-control @error('email') is-invalid @enderror" autofocus required>
            @error('email') <div class="invalid-feedback">{{ $message }}</div> @enderror
        </div>

        <div class="mb-3" x-data="{ show: false }">
            <label class="form-label">{{ __('admin.auth.password') }}</label>
            <div class="input-group has-validation">
                <input :type="show ? 'text' : 'password'" wire:model="password" class="form-control @error('password') is-invalid @enderror" required>
                <button type="button" class="btn btn-outline-secondary" @click="show = !show" tabindex="-1">
                    <span x-show="!show">{{ __('admin.auth.show_password') }}</span>
                    <span x-show="show" style="display: none;">{{ __('admin.auth.hide_password') }}</span>
                </button>
                @error('password') <div class="invalid-feedback">{{ $message }}</div> @enderror
            </div>
        </div>

        <div class="form-check mb-3">
            <input type="checkbox" wire:model="remember" id="remember" class="form-check-input">
            <label for="remember" class="form-check-label">{{ __('admin.auth.remember_me') }}</label>
        </div>

        <button type="submit" class="btn btn-primary w-100">
            <span wire:loading.remove wire:target="login">{{ __('admin.auth.sign_in_button') }}</span>
            <span wire:loading wire:target="login">{{ __('admin.auth.signing_in') }}</span>
        </button>
    </form>
</div>
