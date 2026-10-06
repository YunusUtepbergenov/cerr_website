<?php

use App\Http\Middleware\SetAdminLocale;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Only the login screen is exposed. Registration, password reset and email
// verification were routed to Volt views that do not exist in this repo, so
// every one of them returned a 500 (and `register` would have been public
// self-signup had it worked). Accounts are created and their passwords reset
// from the admin Users screen, which generates and displays a new password, so
// nothing here needs a mailer.
Route::middleware(['guest', SetAdminLocale::class])->group(function () {
    Volt::route('login', 'auth.login')
        ->name('login');
});

Route::post('logout', App\Livewire\Actions\Logout::class)
    ->name('logout');
