<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

use App\Jobs\UpdateSecSplitsDividendsJob;
use App\Security;

class UpdateSecSplitsDividends extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:security_splits_dividends {simulatingSymbol?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update the Security Splits and Dividends data';

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // Add Jobs - each job handling 1 security at a time

        // Simulating stock split?
        $simulatingSymbol = $this->argument('simulatingSymbol');

        if (!is_null($simulatingSymbol))
        {
            $securities = Security::select('id', 'symbol', 'exchange', 'security_type')
                                    ->where('symbol', $simulatingSymbol)
                                    ->get()
                                    ->toArray();
        }
        else
        {
            $securities = Security::all('id', 'symbol', 'exchange', 'security_type')->toArray();
        }

        foreach ($securities as $security)
        {
            // ALWAYS REMEMBER to add the queue name in Kernel file "schedule" method when a job is added to a queue like below.

            $job = (new UpdateSecSplitsDividendsJob($security))->onQueue('medium');

            if (!is_null($simulatingSymbol))
            {
                $job->handle($simulatingSymbol);
            }
            else
            {
                dispatch($job);
            }
        }
    }
}
