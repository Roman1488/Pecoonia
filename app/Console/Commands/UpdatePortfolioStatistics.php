<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Jobs\SavePortfolioDailyValuesJob;
use App\Portfolio;
use DB;

class UpdatePortfolioStatistics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:portfolio_statistics';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Save most recent updated statistics for each portfolio';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Add Jobs - one portfolio at a time

        $portfolios = Portfolio::select('portfolios.id', 'portfolios.user_id', DB::raw('currencies.id as currency_id'))
                        ->join('currencies', 'currencies.id', '=', 'portfolios.currency_id')
                        ->where('status', 1)->get()->toArray();

        foreach ($portfolios as $portfolio)
        {
            $portfolioArr = [
                'id'              => $portfolio['id'],
                'user_id'         => $portfolio['user_id'],
                'currency_id'     => $portfolio['currency']['id'],
                'currency_symbol' => $portfolio['currency']['symbol']
            ];

            // ALWAYS REMEMBER to add the queue name in Kernel file "schedule" method when a job is added to a queue like below.

            $job = (new SavePortfolioDailyValuesJob($portfolioArr, TRUE))->onQueue('low');
            dispatch($job);
        }
    }
}
