<?php

namespace App\Providers;

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
        // Fix: clé trop longue pour MySQL avec utf8mb4
        \Illuminate\Support\Facades\Schema::defaultStringLength(191);
    }
}
