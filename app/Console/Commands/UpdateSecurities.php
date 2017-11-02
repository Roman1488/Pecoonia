<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Security;
use App\SecurityData;
use DirkOlbrich\YahooFinanceQuery\YahooFinanceQuery;

class UpdateSecurities extends Command
{

    use DispatchesJobs;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:securities';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'To update securities data.';

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
        $securitySymbols = Security::lists('id', 'symbol')->toArray();

        if ($securitySymbols)
        {
            $chunked = array_chunk($securitySymbols, 10, true);

            foreach ($chunked as $symbols)
            {
                $symbolArr = array_keys($symbols);

                $yahooSecurityData = $this->_checkYahooFinance($symbolArr);

                if ($yahooSecurityData)
                {
                    foreach ($yahooSecurityData as $security)
                    {
                        $securityId = $securitySymbols[$security['Symbol']];

                        $updated = $this->_updateSecurityData($securityId, $security);

                        $this->line($security['Symbol'] . ' processed and updated');
                    }
                }
            }

            $this->line('Done');
        }
    }

    /**
     * Return the array of the query to Yahoo finance with match in symbol
     *
     * @param $symbol
     *
     * @return mixed
     */

    private function _checkYahooFinance($symbol)
    {
        $query = new YahooFinanceQuery();

        if (is_array($symbol))
        {
            $result = $query->quote($symbol, config('pecoonia.yahoo_security_fields'))->get();
            return $result;
        }
        else if (is_string($symbol))
        {
            $result = $query->quote(array($symbol), config('pecoonia.yahoo_security_fields'))->get();
            return $result[0];
        }
    }


    private function _updateSecurityData($securityId, $response)
    {
        $securityData = SecurityData::where('security_id', $securityId)
                                    ->first();

        if ($securityData)
        {
            // Get change and PercentChange (always split by " - ")
            $change_percentChange = explode(" - ", $response['Change_PercentChange']);

            $securityData->average_daily_volume  = $response['AverageDailyVolume'] ?: 0;
            $securityData->change                = $response['Change'] ?: 0;
            $securityData->percentage_change     = (isset($change_percentChange[1])) ? substr($change_percentChange[1], 0, -1) : 0;

            $lastTradeDate = '0000-00-00 00:00';
            if ($response['LastTradeDate'] && $response['LastTradeDate'] != "N/A")
            {
                list($m, $d, $y) = explode('/', $response['LastTradeDate']);
                $lastTradeDate   = $y . '-' . $m . '-' . $d . ' 00:00';
            }

            $securityData->last_trade_date       = $lastTradeDate;
            $securityData->last_trade_price_only = $response['LastTradePriceOnly'] ?: 0;
            $securityData->days_low              = $response['DaysLow'] ?: 0;
            $securityData->days_high             = $response['DaysHigh'] ?: 0;
            $securityData->year_low              = $response['YearLow'] ?: 0;
            $securityData->year_high             = $response['YearHigh'] ?: 0;
            $securityData->volume                = $response['Volume'] ?: 0;
            $securityData->last_trade_time       = $response['LastTradeTime'] ?: '';
            $securityData->market_capitalization = $response['MarketCapitalization'] ?: '';
            $securityData->open                  = $response['Open'] ?: 0;
            $securityData->previous_close        = $response['PreviousClose'] ?: 0;

            return $securityData->save();
        }

        return false;
    }
}