<?php

use App\Livewire\About;
use App\Livewire\Contact;
use App\Livewire\History;
use App\Livewire\Home;
use App\Livewire\Leadership;
use App\Livewire\ShowAllCategories;
use App\Livewire\ShowCategory;
use App\Livewire\ShowNews;
use App\Livewire\Structure;
use App\Livewire\Vacancies;
use App\Livewire\Videos\VideoIndex;
use App\Livewire\Videos\VideoShow;
use Illuminate\Support\Facades\Route;

Route::get('/', Home::class)->name('home');

Route::get('lang/{locale}', function (string $locale) {
    if (in_array($locale, ['kr', 'uz', 'ru', 'en'], true)) {
        session(['locale' => $locale]);
    }

    $previous = url()->previous();
    $sameHost = parse_url($previous, PHP_URL_HOST) === request()->getHost();

    return redirect($sameHost ? $previous : route('home'));
})->name('lang.switch');

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

require __DIR__.'/auth.php';
