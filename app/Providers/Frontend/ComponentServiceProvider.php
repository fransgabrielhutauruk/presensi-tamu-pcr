<?php

namespace App\Providers\Frontend;

use Illuminate\Support\ServiceProvider;

class ComponentServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Header Data
        view()->composer('contents.frontend.partials.common.header', function ($view) {
            //
        });
    }
}
