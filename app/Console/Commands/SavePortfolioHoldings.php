<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Jobs\SavePortfolioHoldingsJob;
use App\Portfolio;
use Carbon\Carbon;
use App\User;
use App\PortfolioHoldings;

class SavePortfolioHoldings extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'save:portfolio_holdings {portfolioId?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This Command Will Save Porfolio Holdings For Today Of Given Portfolio.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $portfolioId = $this->argument('portfolioId');

        if (!is_null($portfolioId))
        {
            $portfolioExists = Portfolio::where('id', $portfolioId)->get()->first();

            if ($portfolioExists)
            {
                $job = (new SavePortfolioHoldingsJob($portfolioId))->onQueue('medium');
                $job->handle();
            }
        }
        else
        {
            // Fetch all users, and loop over each
            // Get current date-time based on timezone of current user in iteration
            // Check if that user already has record in portfolio_holdings table for current date
            // If record exists, skip to next user
            // Else, check current time of that user timezone
            // If time >= 23:00, then add that user to ProcessUsers list
            // Fetch all portfolios (ProcessPortfolios list) of all users in ProcessUsers list
            // Finally, ProcessPortfolios list contains all portfolios for which job should be dispatched.

            $processPortfoliosList = [];
            $processUsersList = [];

            $users = User::all();

            foreach ($users as $user)
            {
                if ($user->timezone_code)
                {
                    //Get Current Timestamp in given timezone
                    $currentUserDateTimeObj = Carbon::now($user->timezone_code);
                }
                else
                {
                    //Get Current Timestamp in default server timezone
                    $currentUserDateTimeObj = Carbon::now();
                }

                $existingRecord = PortfolioHoldings::where('user_id', '=', $user->id)
                                    ->whereDate('date', '=', $currentUserDateTimeObj->toDateString())
                                    ->count();

                if ($existingRecord > 0)
                {
                    // Records already added for the given date for the given user, no need to process further.
                    continue;
                }

                if ($currentUserDateTimeObj->hour >= 23)
                {
                    $processUsersList[] = $user->id;
                }
            }

            $processPortfoliosList = Portfolio::select('id')
                                        ->where('status', 1)
                                        ->whereIn('user_id', $processUsersList)
                                        ->get()
                                        ->pluck('id')
                                        ->toArray();

            foreach ($processPortfoliosList as $portfolioId)
            {
                $job = (new SavePortfolioHoldingsJob($portfolioId))->onQueue('medium');
                dispatch($job);
            }
        }
    }
}
