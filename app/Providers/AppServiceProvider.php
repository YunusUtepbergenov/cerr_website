<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\News;
use App\Models\Tag;
use App\Support\HtmlSanitizer;
use Illuminate\Support\Facades\Blade;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Blade::directive('sanitized', function (string $expression): string {
            return '<?php echo \\'.HtmlSanitizer::class."::sanitize($expression); ?>";
        });

        // Live content counts shown as badges in the admin sidebar navigation.
        // Skipped for accountants, who only ever see the Open Data section.
        View::composer('components.layouts.admin', function ($view): void {
            $user = auth()->user();

            if ($user && ! $user->isAccountant()) {
                $view->with('navCounts', [
                    'news' => News::count(),
                    'categories' => Category::count(),
                    'tags' => Tag::count(),
                ]);
            }
        });
    }
}
