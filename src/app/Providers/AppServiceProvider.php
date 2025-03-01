<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Blade;
use Carbon\Carbon;


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
        Carbon::setLocale('ja');

        Blade::directive('formatTime', function ($expression) {
            return "<?php echo $expression ? \Carbon\Carbon::parse($expression)->format('H:i') : '-'; ?>";
        });

        Blade::directive('formatDate', function ($date) {
            return "<?php echo \Carbon\Carbon::parse($date)->isoFormat('MM/DD（dd）'); ?>";
        });
    }
}
