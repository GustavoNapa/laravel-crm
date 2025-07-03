<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class AppViewServiceProvider extends ServiceProvider
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
        app('view')->addNamespace('agenda', resource_path('views/agenda'));
        app('view')->addNamespace('whatsapp', resource_path('views/whatsapp'));
        app('view')->addNamespace('agentes', resource_path('views/agentes'));
    }
}
