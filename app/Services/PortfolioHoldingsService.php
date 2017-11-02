<?php

namespace App\Services;
use App;
use App\Jobs\Job;
use App\Currency;
use App\CurrenciesPair;
use App\Libraries\Utils;
use App\Portfolio;
use App\Security;
use App\Transaction;
use App\TransactionTag;
use App\Http\Requests;
use App\SecurityData;
use App\UserSecurity;
use App\SecurityWatchlist;
use App\Exceptions\HttpException;
use Illuminate\Database\QueryException;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use App\User;
use App\PortfolioHoldings;
use App\Services\PortfolioStatisticsService;

class PortfolioHoldingsService
{
    protected $portfolio_id;

    public function __construct($portfolio_id)
    {
        $this->portfolio_id = $portfolio_id;
    }

    public function getPortfolioTimezone()
    {
        $portfolio = Portfolio::find($this->portfolio_id);

        if ($portfolio)
        {
            return $portfolio->user->timezone_code;
        }

        return false;
    }

    public function getPreviousPortfolioHoldings($date)
    {
        $date = Carbon::createFromFormat('Y-m-d', $date);
        $portfolioHoldings = PortfolioHoldings::where('portfolio_id', $this->portfolio_id)
                                            ->whereDate('date',  '=', $date->toDateString())
                                            ->get()->toArray();

        foreach ($portfolioHoldings as $key => $value)
        {
            $portfolioHoldings[$key]['security'] = [];
            $portfolioHoldings[$key]['security']['currency'] = [];
            $portfolioHoldings[$key]['security']['name'] = $value['security_name'];
            $portfolioHoldings[$key]['security']['currency']['symbol'] = $value['currency_symbol'];
            $portfolioHoldings[$key]['security']['security_type'] = $value['security_type'];
        }

        return $portfolioHoldings;
    }

    public function getPortfolioHoldingsCount()
    {
        try
        {
            return $this->getPortfolioHoldingsQuery($this->portfolio_id)->get()->count();
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function getPortfolioHoldings()
    {
        try
        {
            $portfolioHoldingsQuery = $this->getPortfolioHoldingsQuery();
            $transactions = $portfolioHoldingsQuery->select(
                                'id',
                                'security_id',
                                'portfolio_id',
                                'bank_id',
                                DB::raw('SUM(inventory) as total_inventory'),
                                DB::raw('SUM(c_trade_value_local) as total_trade_value_local'),
                                DB::raw('SUM(c_trade_value_base) as total_trade_value_base')
                            )
                            ->get();

            $transactions = $this->calculateAnalysisValues($transactions);

            return $transactions;
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function getPortfolioHoldingsQuery($securitySet = [])
    {
       $portfolioHoldingsQuery = Transaction::where('transactions.portfolio_id', '=', $this->portfolio_id)
                        ->where('transactions.transaction_type', '=', 'buy')
                        ->where('transactions.inventory', '>', 0);

        if ($securitySet)
        {
            if ($securitySet['id_set'])
            {
                $portfolioHoldingsQuery->whereIn('transactions.security_id', $securitySet['id_set']);
            }
            else if ($securitySet['symbol_set'])
            {
                $portfolioHoldingsQuery->join('securities','transactions.security_id', '=', 'securities.id')
                        ->whereIn('securities.symbol', $securitySet['symbol_set']);
            }
        }

        $portfolioHoldingsQuery->groupBy('transactions.security_id')
                        ->havingRaw('SUM(transactions.inventory) > 0');

        return $portfolioHoldingsQuery;
    }

    public function calculateAnalysisValues($transactions)
    {
        if (!$transactions)
        {
            return [];
        }

        $banksBalance = $this->getTotalAllBankBalance();

        $marketValueSum = 0;
        foreach ($transactions as $transaction)
        {
            $transaction->total_trade_value_local = (double) $transaction->total_trade_value_local;
            $transaction->total_trade_value_base  = (double) $transaction->total_trade_value_base;
            $transaction->total_inventory         = (int) $transaction->total_inventory;

            $purchaseValue = $transaction->total_trade_value_local;

            if ($transaction->security->currency->id === $transaction->portfolio->currency->id)
            {
                $currentCurrencyRate = 1;
                $purchaseValue       = $transaction->total_trade_value_base;
            }
            else
            {
                $currencyPair = $transaction->security->currency->symbol . $transaction->portfolio->currency->symbol;

                $currencyPair = CurrenciesPair::where('name', '=', $currencyPair)
                                                ->first();
                $currentCurrencyRate = ($currencyPair) ? $currencyPair->value : 0;
            }

            $transaction->purchase_value       = $purchaseValue;

            if($transaction->total_inventory > 0)
                $transaction->app                  = round(($transaction->purchase_value / $transaction->total_inventory),2);

            $transaction->price                = (double) $transaction->security->data->last_trade_price_only;
            $transaction->market_value         = ($transaction->total_inventory * $transaction->price);
            $transaction->gain_loss            = (($transaction->total_inventory * $transaction->price) - $transaction->purchase_value);
            $transaction->return               = round(($transaction->gain_loss / $transaction->purchase_value) * 100, 2);
            $transaction->market_value_in_base = (($transaction->total_inventory * $transaction->price) * $currentCurrencyRate);
            $transaction->gain_loss_in_base    = ($transaction->market_value_in_base - $transaction->total_trade_value_base);
            $transaction->current_currency_rate = $currentCurrencyRate;

            $marketValueSum                    += $transaction->market_value_in_base;
        }

        $totalAmountSum = $marketValueSum + $banksBalance['totalAllBankBalance'];

        foreach ($transactions as $transaction)
        {
            $transaction->weight = 0;
            if ($totalAmountSum)
            {
                $transaction->weight = round(((($transaction->total_inventory * $transaction->price) * $transaction->current_currency_rate) / $totalAmountSum) * 100);
            }

            // Get Buy Transactions Count
            $transaction->transaction_count = Transaction::where('security_id', $transaction->security_id)
                                        ->where('portfolio_id', $transaction->portfolio_id)
                                        ->where('transaction_type', 'buy')
                                        ->where('inventory', '>', 0)
                                        ->count();
        }

        return $transactions;
    }

    public function getTotalMarketValueBase($currencyFilter = [], $securityTypeFilter = [], $tagFilter = [], $securitySet = [])
    {
        // Calculate total_marketvalue_base

        $marketValueBaseSum = 0;

        $portfolioHoldingsQuery = $this->getPortfolioHoldingsQuery($securitySet);

        $transQuery = $portfolioHoldingsQuery->select(
                            'transactions.id',
                            'transactions.security_id',
                            'transactions.portfolio_id',
                            'transactions.date',
                            DB::raw('SUM(transactions.inventory) as total_inventory')
                        );

        if (!empty($currencyFilter) && !empty($securityTypeFilter) && !empty($tagFilter))
        {
            $transQuery = $transQuery
                            ->join('securities', 'securities.id', '=', 'transactions.security_id')
                            ->join('currencies', 'currencies.id', '=', 'securities.currency_id')
                            ->join('transaction_tags', 'transaction_tags.transaction_id', '=', 'transactions.id')
                            ->whereIn('currencies.symbol', $currencyFilter)
                            ->whereIn('securities.security_type', $securityTypeFilter)
                            ->whereIn('transaction_tags.tag', $tagFilter);
        }

        elseif (!empty($securityTypeFilter))
        {
            $transQuery = $transQuery
                            ->join('securities', 'securities.id', '=', 'transactions.security_id')
                            ->whereIn('securities.security_type', $securityTypeFilter);
        }

        elseif (!empty($tagFilter))
        {
            $transQuery = $transQuery
                            ->join('transaction_tags', 'transaction_tags.transaction_id', '=', 'transactions.id')
                            ->whereIn('transaction_tags.tag', $tagFilter);
        }

        elseif (!empty($currencyFilter))
        {
            $transQuery = $transQuery
                            ->join('securities', 'securities.id', '=', 'transactions.security_id')
                            ->join('currencies', 'currencies.id', '=', 'securities.currency_id')
                            ->whereIn('currencies.symbol', $currencyFilter);
        }

        $transactions = $transQuery->get();

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

        return $marketValueBaseSum;
    }

    public function getTotalAllBankBalance()
    {
        $portfolioStatisticsService = new PortfolioStatisticsService($this->portfolio_id);
        $banksBalance = $portfolioStatisticsService->allBanksBalanceTotal();

        return $banksBalance;
    }

    public function getTotalPortfolioValue()
    {
        $totalMarketValueBase = $this->getTotalMarketValueBase();
        $totalAllBankBalance = $this->getTotalAllBankBalance();
        return $totalMarketValueBase + $totalAllBankBalance['totalAllBankBalance'];
    }

}