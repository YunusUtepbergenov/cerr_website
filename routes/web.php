<?php

use App\Http\Controllers\Admin\InlineImageController;
use App\Http\Controllers\Admin\MediaUploadController;
use App\Http\Controllers\OpenDataDownloadController;
use App\Livewire\About;
use App\Livewire\Admin\Activity\ActivityIndex;
use App\Livewire\Admin\Categories\CategoryIndex;
use App\Livewire\Admin\Dashboard as AdminDashboard;
use App\Livewire\Admin\Media\MediaIndex;
use App\Livewire\Admin\News\NewsForm;
use App\Livewire\Admin\News\NewsIndex;
use App\Livewire\Admin\Pages\PageForm;
use App\Livewire\Admin\Pages\PageIndex;
use App\Livewire\Admin\Tags\TagIndex;
use App\Livewire\Admin\Users\UserIndex;
use App\Livewire\Admin\Videos\VideoIndex as AdminVideoIndex;
use App\Livewire\Contact;
use App\Livewire\History;
use App\Livewire\Home;
use App\Livewire\Leadership;
use App\Livewire\OpenData\OpenDataIndex;
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

Route::get('/open-data', OpenDataIndex::class)->name('open-data.index');
Route::get('/open-data/{openData}/download', OpenDataDownloadController::class)->name('open-data.download');

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboard::class)->name('dashboard');

    Route::get('/news', NewsIndex::class)->name('news.index');
    Route::get('/news/create', NewsForm::class)->name('news.create');
    Route::get('/news/{news}/edit', NewsForm::class)->name('news.edit');

    Route::get('/categories', CategoryIndex::class)->name('categories.index')->middleware('manage-content');
    Route::get('/tags', TagIndex::class)->name('tags.index')->middleware('manage-content');

    Route::get('/users', UserIndex::class)->name('users.index')->middleware('admin-only');

    Route::get('/pages', PageIndex::class)->name('pages.index')->middleware('manage-content');
    Route::get('/pages/create', PageForm::class)->name('pages.create')->middleware('manage-content');
    Route::get('/pages/{page}/edit', PageForm::class)->name('pages.edit')->middleware('manage-content');

    Route::get('/videos', AdminVideoIndex::class)->name('videos.index')->middleware('manage-content');

    Route::get('/media', MediaIndex::class)->name('media.index');
    Route::get('/activity', ActivityIndex::class)->name('activity.index')->middleware('manage-content');

    Route::post('/inline-image', [InlineImageController::class, 'store'])->name('inline-image.store');
    Route::post('/media/upload', [MediaUploadController::class, 'store'])->name('media.upload');
});

require __DIR__.'/auth.php';
