<?php

use App\Livewire\About;
use App\Livewire\Admin\Post;
use App\Livewire\{Contact, History, Home, Leadership, ShowAllCategories, ShowCategory, ShowNews, Structure, Vacancies};
use App\Livewire\Vides\VideoIndex;
use App\Livewire\Vides\VideoShow;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Route;
use Livewire\Volt\Volt;

// Route::get('/', function () {
//     return view('welcome');
// })->name('home');

Route::middleware(['web'])->group(function () {
    Route::get('/', Home::class)->name('home');

    Route::get('lang/{locale}', function ($locale) {
        session(['locale' => $locale]);

        return redirect()->back();
    })->name('lang.switch');
});

// Route::get('/', Home::class)->name('home');
Route::get('/history', History::class)->name('history');
Route::get('/about', About::class)->name('about');
Route::get('/leadership', Leadership::class)->name('leadership');
Route::get('/structure', Structure::class)->name('structure');
Route::get('/contact', Contact::class)->name('contact');
Route::get('/vacancies', Vacancies::class)->name('vacancies');
Route::get('/show-news/{slug}', ShowNews::class)->name('show.news');
Route::get('/show-category/{slug}', ShowCategory::class)->name('show.category');
Route::get('/show-all-category', ShowAllCategories::class)->name('show.all.category');

Route::get('/videos', VideoIndex::class)->name('videos.index');
Route::get('/videos/{id}', VideoShow::class)->name('videos.show');

// Route::get('lang/{locale}', function ($locale) {
//     if (in_array($locale, ['en', 'uz', 'ru'])) {
//         session(['locale' => $locale]);
//     }
//     return back();
// })->name('lang.switch');
// Route::get('/', );


// Route::view('/admin', 'dashboard')
//     ->middleware(['auth', 'verified'])
//     ->name('dashboard');
    
// Route::get('/admin/posts', Post::class);

// Route::middleware(['auth'])->group(function () {
//     Route::redirect('settings', 'settings/profile');

//     Volt::route('settings/profile', 'settings.profile')->name('settings.profile');
//     Volt::route('settings/password', 'settings.password')->name('settings.password');
//     Volt::route('settings/appearance', 'settings.appearance')->name('settings.appearance');
// });

require __DIR__.'/auth.php';
