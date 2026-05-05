<?php

namespace App\Providers;

use App\Support\HtmlSanitizer;
use Illuminate\Support\Facades\Blade;
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
    }
}
