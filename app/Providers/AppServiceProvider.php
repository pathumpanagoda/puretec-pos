<?php

namespace App\Providers;

use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // When running inside Electron, redirect storage to AppData
        $electronStoragePath = env('ELECTRON_STORAGE_PATH');
        if ($electronStoragePath) {
            $this->app->useStoragePath($electronStoragePath);
        }
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Use Bootstrap 5 pagination styling (not Tailwind with large SVG icons)
        Paginator::useBootstrapFive();
    }
}
