<?php

namespace App\Jobs;

use App;
use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use DB;
use App\Services\PortfolioHoldingsService;
use App\PortfolioHoldings;
use Carbon\Carbon;

class SavePortfolioHoldingsJob extends Job implements ShouldQueue
{
    use InteractsWithQueue, SerializesModels;

    private $portfolioId;
    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($portfolioId)
    {
        $this->portfolioId = $portfolioId;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $portfolioHoldingsService = new PortfolioHoldingsService($this->portfolioId);

        $userSpecifiedTimezone = $portfolioHoldingsService->getPortfolioTimezone();

        if ($userSpecifiedTimezone)
        {
            //Get Current Timestamp in given timezone
            $dateTimeObj = Carbon::now($userSpecifiedTimezone);
        }
        else
        {
            //Get Current Timestamp in default server timezone
            $dateTimeObj = Carbon::now();
        }

        $dateTime = $dateTimeObj->toDateTimeString();
        $date     = $dateTimeObj->toDateString();

        $existingRecord = PortfolioHoldings::where('portfolio_id', $this->portfolioId)
                                ->whereDate('date',  '=', $date)
                                ->count();

        if ($existingRecord > 0)
        {
            // Records already added for the given date for the given portfolio, no need to process further.
            return;
        }

        $holdings = $portfolioHoldingsService->getPortfolioHoldings();

        foreach ($holdings as $securityHolding)
        {
            $portfolioHoldings = new PortfolioHoldings();

            $portfolioHoldings->security_name        = $securityHolding->security->name;
            $portfolioHoldings->date                 = $dateTime;
            $portfolioHoldings->total_inventory      = $securityHolding->total_inventory;
            $portfolioHoldings->purchase_value       = $securityHolding->purchase_value;
            $portfolioHoldings->app                  = $securityHolding->app;
            $portfolioHoldings->price                = $securityHolding->price;
            $portfolioHoldings->market_value         = $securityHolding->market_value;
            $portfolioHoldings->gain_loss            = $securityHolding->gain_loss;
            $portfolioHoldings->return               = $securityHolding->return;
            $portfolioHoldings->market_value_in_base = $securityHolding->market_value_in_base;
            $portfolioHoldings->gain_loss_in_base    = $securityHolding->gain_loss_in_base;
            $portfolioHoldings->weight               = $securityHolding->weight;
            $portfolioHoldings->currency_symbol      = $securityHolding->security->currency->symbol;
            $portfolioHoldings->security_type        = $securityHolding->security->security_type;
            $portfolioHoldings->bank_id              = $securityHolding->bank->id;
            $portfolioHoldings->portfolio_id         = $securityHolding->portfolio->id;
            $portfolioHoldings->security_id          = $securityHolding->security->id;
            $portfolioHoldings->user_id              = $securityHolding->portfolio->user_id;

            $portfolioHoldings->save();
        }
    }
}
