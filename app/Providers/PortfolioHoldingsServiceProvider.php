<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use App\Services\PortfolioHoldingsService;

class PortfolioHoldingsServiceProvider extends ServiceProvider
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
        $this->app->singleton(PortfolioHoldingsService::class, function ($app, $parameters = []) {
            if ($parameters && $parameters['portfolio_id'])
            {
                return new PortfolioHoldingsService($parameters['portfolio_id']);
            }
            else
            {
                return new PortfolioHoldingsService;
            }

        });
    }
}