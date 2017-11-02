<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\StockSplitService;

class StockSplitServiceProvider extends ServiceProvider
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
        $this->app->singleton(StockSplitService::class, function ($app) {
            return new StockSplitService;
        });
    }
}