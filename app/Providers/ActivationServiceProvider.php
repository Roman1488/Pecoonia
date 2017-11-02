<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\ActivationService;

class ActivationServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @return void
     */
    public function boot()
    {
        //
    }

    /**
     * Register the application services.
     *
     * @return void
     */
    public function register()
    {
        $this->app->singleton(ActivationService::class, function ($app) {
            return new ActivationService;
        });
    }
}