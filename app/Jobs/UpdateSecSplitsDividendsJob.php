<?php

namespace App\Jobs;

use App;
use App\Jobs\Job;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\SecuritySplitDividend;
use App\Transaction;
use Carbon\Carbon;

use App\Services\StockSplitService;

class UpdateSecSplitsDividendsJob extends Job implements ShouldQueue
{
    use InteractsWithQueue,
        SerializesModels;

    protected $security;

    const YAHOO_STOCK_CSV_URL = 'http://ichart.finance.yahoo.com/x';

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($security = array())
    {
        $this->security = $security;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle($simulating = null)
    {
        // Prepare csv url
        // Get the csv, read it
        // Delete all existing data for the security, and add new data read from csv

        if (is_null($simulating))
        {
            $csvURL = self::YAHOO_STOCK_CSV_URL . '?s='. trim($this->security['symbol']) .'&g=v&y=0&z=30000';
        }
        else
        {
            $csvURL = \Config::get('app.url') . "/dummy-stock-split-dividend.csv";
        }

        $curl = curl_init();

        curl_setopt_array($curl, array(
            CURLOPT_RETURNTRANSFER => 1,
            CURLOPT_URL            => $csvURL
        ));

        $csvData = curl_exec($curl);
        curl_close($curl);

        if (!$csvData)
        {
            return 0;
        }

        $lines = explode(PHP_EOL, $csvData);

        $csvArray = [];

        $now = (string) Carbon::now();
        $security_id = $this->security['id'];

        foreach ($lines as $line)
        {
            $csvRow = str_getcsv($line);

            // Acceptable rows must contain exactly 3 elements only,
            // with first column value either DIVIDEND or SPLIT.

            if (count($csvRow) != 3)
            {
                continue;
            }

            $type = ($csvRow[0]) ? trim(strtolower($csvRow[0])) : '';

            if (!in_array($type, ['dividend', 'split']))
            {
                continue;
            }

            $date  = (string) Carbon::parse($csvRow[1]);
            $value = trim($csvRow[2]);

            $created_at = $updated_at = $now;

            $csvArray[] = compact('security_id', 'type', 'date', 'value', 'created_at', 'updated_at');
        }

        // Delete all existing records of this security before inserting new records
        SecuritySplitDividend::where('security_id', $security_id)->delete();

        SecuritySplitDividend::insert($csvArray);

        //variable declaration

        $stockSplits = [];

        foreach ($csvArray as $csvVal)
        {
            if ($csvVal['type'] == 'split')
            {
                $stockSplits[] = $csvVal;
            }
        }

        $stockSplitServiceObj = new StockSplitService();
        $stockSplitServiceObj->updateTransactions($stockSplits, $security_id);

        return 1;
    }
}
