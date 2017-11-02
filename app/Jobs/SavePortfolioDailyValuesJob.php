<?php

namespace App\Jobs;

use App;
use App\Jobs\Job;

use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Services\PortfolioHoldingsService;
use App\PortfolioDailyValue;
use App\Transaction;
use App\CurrenciesPair;
use App\CurrencyDistribution;
use App\Bank;
use App\PortfolioStatistics;
use App\PortfolioStatsCurrencyDistribution;
use App\Services\PortfolioStatisticsService;
use DB;
use Carbon\Carbon;

class SavePortfolioDailyValuesJob extends Job implements ShouldQueue
{
    use InteractsWithQueue,
        SerializesModels;

    protected $portfolio;
    protected $updateStatistics;
    protected $portfolioStatisticsService;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($portfolio = array(), $updateStatistics = FALSE )
    {
        $this->portfolio = $portfolio;
        $this->updateStatistics = $updateStatistics;
        $this->portfolioStatisticsService = new PortfolioStatisticsService($this->portfolio['id']);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {

        try {

            // Calculate total_marketvalue_base and total_all_bank_balance

            $marketValueBaseSum = 0;
            $totalAllBankBalance = 0;

            $transactionsQuery = Transaction::select(
                                'id',
                                'security_id',
                                'portfolio_id',
                                'date',
                                DB::raw('SUM(inventory) as total_inventory')
                            )
                            ->where('transactions.portfolio_id', '=', $this->portfolio['id'])
                            ->where('transactions.transaction_type', '=', 'buy')
                            ->groupBy('transactions.security_id');

            if (!$this->updateStatistics) {
                $transactionsQuery->where('transactions.date', '<', Carbon::today()->toDateString());
            }

            $transactions = $transactionsQuery->havingRaw('SUM(transactions.inventory) > 0')->get();

            if ($transactions)
            {
                foreach ($transactions as $transaction)
                {
                    $transaction->total_inventory = (int) $transaction->total_inventory;

                    if ($transaction->security->currency->id === $transaction->portfolio->currency->id)
                    {
                        $currentCurrencyRate = 1;
                    }
                    else
                    {
                        $currencyPair = $transaction->security->currency->symbol . $transaction->portfolio->currency->symbol;

                        $currencyPair = CurrenciesPair::where('name', '=', $currencyPair)->first();
                        $currentCurrencyRate = ($currencyPair) ? $currencyPair->value : 0;
                    }

                    $transactionPrice           = (double) $transaction->security->data->last_trade_price_only;
                    $transactionMarketValInBase = (($transaction->total_inventory * $transactionPrice) * $currentCurrencyRate);

                    $marketValueBaseSum += $transactionMarketValInBase;
                }
            }

            // Total of all banks balance -- If a bank account is in a currency different from base currency
            // then multiply the current balance with the currency rate relative to the base currency.

            $totalBalanceResult = $this->portfolioStatisticsService->allBanksBalanceTotal();
            $totalAllBankBalance = $totalBalanceResult['totalAllBankBalance'];
            $currencyWiseBalance = $totalBalanceResult['currencyWiseBalance'];

            $equityHoldings             = $this->portfolioStatisticsService->getEquityHoldings();
            $fundHoldings               = $this->portfolioStatisticsService->getFundHoldings();
            $otherHoldings              = $this->portfolioStatisticsService->getOtherHoldings();
            // Securities Up : Count securities with positive value in change
            $countOfUpSecurities        = $this->portfolioStatisticsService->getUpSecuritiesCount();
            // Securities Down : Count securities with negative value in change
            $countOfDownSecurities      = $this->portfolioStatisticsService->getDownSecuritiesCount();
            // Securities Unchanged : Count securities with unchanged value (0)
            $countOfUnchangedSecurities = $this->portfolioStatisticsService->getUnchangedSecuritiesCount();

            $marketvalueChange       = 0;
            $currencyDistributionSet = [];

            //fetch current portfolio holdings to process Equity share & Fund share
            $holdings = $this->portfolioStatisticsService->getPortfolioHoldings();

            foreach ($holdings as $securityHolding)
            {
                //Portfolio Change : For each security with inventory above 0 multiply the current quantity with "change" multiplied by the current currency rate

                if ($securityHolding->security->currency->symbol == $securityHolding->portfolio->currency->symbol)
                {
                    $marketvalueChange += $securityHolding->total_inventory * $securityHolding->security->data->change;
                }
                else
                {
                    $currencyPair =  $securityHolding->security->currency->symbol . $securityHolding->portfolio->currency->symbol;

                    $currenciesPairObj = CurrenciesPair::where('name', $currencyPair)->get()->first();

                    $marketvalueChange += $securityHolding->total_inventory * $securityHolding->security->data->change * (($currenciesPairObj) ? $currenciesPairObj->value : 0);
                }

                //Currency Distribution : For each used currency in the portfolio (securities and bank accounts) multiply with currency rate relative to base currency and add together for each currency.

                $currencyOfSecurity = $securityHolding->security->currency->symbol;

                if (array_key_exists($currencyOfSecurity, $currencyDistributionSet))
                {
                    $currencyDistributionSet[$currencyOfSecurity]['market_value_base'] += $securityHolding->market_value_in_base;
                }
                else
                {
                    $currencyDistributionSet[$currencyOfSecurity] = [
                        'currency_symbol'    => $currencyOfSecurity,
                        'market_value_base'  => $securityHolding->market_value_in_base,
                        'bank_balance_base'  => 0,
                        'share_of_portfolio' => 0
                    ];
                }
            }

            //calculate the sum of market_value_base for equityHoldings
            $sumOfEquity = $equityHoldings->sum('market_value_in_base');

            //calculate the sum of market_value_base for fundHoldings
            $sumOfFund = $fundHoldings->sum('market_value_in_base');

            // Calculate portfolio value
            $portfolioValue = $marketValueBaseSum + $totalAllBankBalance;

            // Save values
            if ($this->updateStatistics)
            {
                $portfolioStatisticsObj = PortfolioStatistics::where('portfolio_id', $this->portfolio['id'])->first();

                if (!$portfolioStatisticsObj)
                {
                    $portfolioStatisticsObj = new PortfolioStatistics();
                }
            }
            else
            {
                //delete existing records of PortfolioDailyValue

                $deleteStatus = PortfolioDailyValue::where('portfolio_id', $this->portfolio['id'])
                                    ->whereDate('created_at', '=', \Carbon\Carbon::today()->toDateString())
                                    ->delete();

                $portfolioStatisticsObj = new PortfolioDailyValue();
            }

            $portfolioStatisticsObj->portfolio_id               = $this->portfolio['id'];
            $portfolioStatisticsObj->total_marketvalue_base     = $marketValueBaseSum;
            $portfolioStatisticsObj->total_all_bank_balance     = $totalAllBankBalance;
            $portfolioStatisticsObj->portfolio_value            = $portfolioValue;
            $portfolioStatisticsObj->cash_share                 = ($portfolioValue) ? (($totalAllBankBalance / $portfolioValue) * 100) : 0;
            $portfolioStatisticsObj->equity_share               = ($portfolioValue) ? (($sumOfEquity / $portfolioValue) * 100) : 0;
            $portfolioStatisticsObj->fund_share                 = ($portfolioValue) ? (($sumOfFund / $portfolioValue) * 100) : 0;
            $portfolioStatisticsObj->securities_up              = $countOfUpSecurities;
            $portfolioStatisticsObj->securities_down            = $countOfDownSecurities;
            $portfolioStatisticsObj->securities_unchanged       = $countOfUnchangedSecurities;
            $portfolioStatisticsObj->marketvalue_change         = $marketvalueChange;
            $portfolioStatisticsObj->marketvalue_percent_change = ($portfolioValue) ? (($marketvalueChange / $portfolioValue) * 100) : 0;
            $portfolioStatisticsObj->save();

            //Currency Distribution : For each used currency in the portfolio (securities and bank accounts) multiply with currency rate relative to base currency and add together for each currency.
            $totalShareOfPortfolio = 0;

            //Generate bank_balance_base & share_of_portfolio using market_value_base

            foreach ($currencyDistributionSet as $key => $currencyDistributionRow)
            {
                $currencyDistributionSet[$key]['bank_balance_base'] = isset($currencyWiseBalance[$currencyDistributionRow['currency_symbol']]) ? $currencyWiseBalance[$currencyDistributionRow['currency_symbol']] : 0;

                $currencyDistributionSet[$key]['share_of_portfolio'] = $currencyDistributionSet[$key]['bank_balance_base'] + $currencyDistributionSet[$key]['market_value_base'];

                $totalShareOfPortfolio += $currencyDistributionSet[$key]['bank_balance_base'] + $currencyDistributionSet[$key]['market_value_base'];
            }

            //Save Each currencyDistributionRow To portfolio_daily_currency_distribution table

            foreach ($currencyDistributionSet as $key => $currencyDistributionRow)
            {
                if ($this->updateStatistics)
                {
                    $currencyDistributionObj = PortfolioStatsCurrencyDistribution::where('portfolio_statistics_id', $portfolioStatisticsObj->id)->first();

                    if (!$currencyDistributionObj)
                    {
                        $currencyDistributionObj = new PortfolioStatsCurrencyDistribution();
                        $currencyDistributionObj->portfolio_statistics_id = $portfolioStatisticsObj->id;
                    }
                }
                else
                {
                    $currencyDistributionObj = new CurrencyDistribution();
                    $currencyDistributionObj->portfolio_daily_values_id = $portfolioStatisticsObj->id;
                }

                $currencyDistributionObj->bank_balance_base = $currencyDistributionRow['bank_balance_base'];
                $currencyDistributionObj->market_value_base = $currencyDistributionRow['market_value_base'];
                $currencyDistributionObj->currency_symbol   = $currencyDistributionRow['currency_symbol'];

                //Generate percentage_share using total_bank_balance_base & total_market_value_base
                $currencyDistributionSet[$key]['percentage_share'] = ($totalShareOfPortfolio) ? (($currencyDistributionRow['share_of_portfolio'] / $totalShareOfPortfolio) * 100) : 0;

                $currencyDistributionObj->percentage_share  = isset($currencyDistributionRow['percentage_share']) ? $currencyDistributionRow['percentage_share'] : 0;
                $currencyDistributionObj->save();
            }

            return $portfolioStatisticsObj;

        } catch (\Exception $e) {
            dump($e->getMessage() . ' -->'. $e->getLine());
        }
    }

}
