<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        Commands\UpdateCurrenciesPairs::class,
        Commands\UpdateSecurities::class,
        Commands\UpdateSecSplitsDividends::class,
        Commands\SavePortfolioDailyValues::class,
        Commands\SavePortfolioHoldings::class,
        Commands\UpdatePortfolioStatistics::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // ALWAYS REMEMBER to add the queue name below when a job is added to a queue

        // $schedule->command('queue:work --queue="update_portfolio_statistics,save_portfolio_daily_values,save_portfolio_holdings,update_sec_splits_divs" --tries=5')->everyMinute();

        //$schedule->command('inspire')->hourly();
        $schedule->command('update:currencies_pairs')->hourly();

        $schedule->command('update:securities')->hourly();

        $schedule->command('update:security_splits_dividends')->dailyAt('20:00');

        $schedule->command('save:portfolio_daily_values')->weekdays()->at('00:01');

        $schedule->command('update:portfolio_statistics')->everyMinute();

        $schedule->command('save:portfolio_holdings')->everyTenMinutes();
    }
}
