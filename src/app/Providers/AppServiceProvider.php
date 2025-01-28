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
        // Carbonのロケールを日本語に設定
        \Carbon\Carbon::setLocale('ja');

        // @formatTimeでH:iの形式で時間を表示できるようにする
        Blade::directive('formatTime', function ($expression) {
            return "<?php echo $expression ? \Carbon\Carbon::parse($expression)->format('H:i') : '-'; ?>";
        });

        // @formatDateでXX/XX(曜日)の形式で日にちを表示できるようにする
        Blade::directive('formatDate', function ($date) {
            return "<?php echo \Carbon\Carbon::parse($date)->isoFormat('MM/DD（dd）'); ?>";
        });
    }
}
