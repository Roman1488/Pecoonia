<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Exceptions\HttpException;
use App\Helpers\YahooApiClient;
use App\CurrenciesPair;

class UpdateCurrenciesPairs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:currencies_pairs';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update currencies pairs through Yahoo API';

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
        try {
            $client = new YahooApiClient(120);

            $allCurrencies = \App\Currency::all('symbol')->toArray();

            if ($allCurrencies)
            {
                $pairs = [];
                $now = (string) \Carbon\Carbon::now(\Config('app.timezone'));

                // Prepare all pairs
                foreach ($allCurrencies as $a)
                {
                    foreach ($allCurrencies as $b)
                    {
                        /*
                        if (($a['symbol'] != $b['symbol']) &&
                            !in_array($a['symbol'] . $b['symbol'], $pairs) &&
                            !in_array($b['symbol'] . $a['symbol'], $pairs)
                        )
                        */
                        if (($a['symbol'] != $b['symbol']) &&
                            !in_array($a['symbol'] . $b['symbol'], $pairs)
                        )
                        {
                            $pairs[] = $a['symbol'] . $b['symbol'];
                        }
                    }
                }

                // Instead of passing all currency pairs in one Yahoo call,
                // pass in chunks in multiple calls to avoid potential issue of too long query string

                $chunkedCurrencies = array_chunk($pairs, 20, true);
                $data = [];

                foreach ($chunkedCurrencies as $currencies)
                {
                    //Fetch data set
                    $currenciesResult = $client->getQuotes($currencies);

                    foreach ($currenciesResult['query']['results']['rate'] as $c)
                    {
                        $data[] = [
                            'name' => $c['id'],
                            'value' => $c['Rate'],
                            'updated_at' => $now
                        ];

                        // For the inverse currency pair, do 1/rate
                        /*
                        $invCurrPairArr = explode("/", $c['Name']);
                        $invCurrPair = $invCurrPairArr[1] . $invCurrPairArr[0];

                        $data[] = [
                            'name' => $invCurrPair,
                            'value' => round((1 / (double) $c['Rate']), 5),
                            'updated_at' => $now
                        ];
                        */
                    }
                }

                if ($data)
                {
                    CurrenciesPair::truncate()->insert($data);
                }
            }
        }
        catch (HttpException $e) {
            $this->comment($e->getMessage() . ' ' . $e->getCode());
        }
    }
}
