<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PortfolioStatisticsService;

class PortfolioStatisticsServiceProvider extends ServiceProvider
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
        $this->app->singleton(PortfolioStatisticsService::class, function ($app) {
            return new PortfolioStatisticsService;
        });
    }
}
