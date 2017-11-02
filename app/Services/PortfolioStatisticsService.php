<?php

namespace App\Services;
use App;
use App\Jobs\Job;
use App\Currency;
use App\CurrenciesPair;
use App\Libraries\Utils;
use App\Portfolio;
use App\Security;
use App\Bank;
use App\Transaction;
use App\TransactionTag;
use App\Http\Requests;
use App\SecurityData;
use App\UserSecurity;
use App\SecurityWatchlist;
use App\Exceptions\HttpException;
use Illuminate\Database\QueryException;
use App\Services\PortfolioHoldingsService;
use Illuminate\Http\Request;
use Carbon\Carbon;
use DB;
use App\User;
use App\PortfolioHoldings;

class PortfolioStatisticsService
{
    protected $portfolioHoldingsService;
    protected $portfolio_id;
    protected $holdings;

    public function __construct($portfolio_id)
    {
        $this->portfolio_id = $portfolio_id;
    }

    public function getPortfolioHoldings()
    {
        if (!$this->holdings) {
            //fetch current portfolio holdings to process Equity share & Fund share
            $this->portfolioHoldingsService = new PortfolioHoldingsService($this->portfolio_id);
            $this->holdings = $this->portfolioHoldingsService->getPortfolioHoldings();
        }
        return $this->holdings;
    }

    // Total of all banks balance -- If a bank account is in a currency different from base currency
    // then multiply the current balance with the currency rate relative to the base currency.
    public function allBanksBalanceTotal()
    {
        $portfolio = Portfolio::findOrFail($this->portfolio_id);

        $sameCurrencySum = Bank::select(DB::raw('SUM(cash_amount) as cash_sum'))
                            ->where('portfolio_id', $this->portfolio_id)
                            ->where('currency_id', $portfolio->currency_id)
                            ->first()
                            ->toArray();

        $diffCurrencyBanks = Bank::select('currency_id', DB::raw('SUM(cash_amount) as cash_sum'))
                                ->where('portfolio_id', $this->portfolio_id)
                                ->where('currency_id', '!=' , $portfolio->currency_id)
                                ->groupBy('currency_id')
                                ->get()
                                ->toArray();

        $diffCurrencySum = 0;
        $currencyWiseBalance = [];

        $currencyWiseBalance[$portfolio['currency_symbol']] = $sameCurrencySum['cash_sum'];

        foreach ($diffCurrencyBanks as $currObj)
        {
            $diffCurrencyPair = $currObj['currency']['symbol'] . $portfolio['currency_symbol'];
            $diffCurrPairsResult = CurrenciesPair::where('name', '=', $diffCurrencyPair)->first();

            $currBalInBase = $currObj['cash_sum'] * (($diffCurrPairsResult) ? $diffCurrPairsResult->value : 0);

            $currencyWiseBalance[$currObj['currency']['symbol']] = $currBalInBase;
            $diffCurrencySum += $currBalInBase;
        }

        $totalAllBankBalance = $sameCurrencySum['cash_sum'] + $diffCurrencySum;

        return ['currencyWiseBalance' => $currencyWiseBalance, 'totalAllBankBalance' => $totalAllBankBalance];

    }


    public function getEquityHoldings()
    {
        $holdings = $this->getPortfolioHoldings();
        $holdingsSet = $holdings->map(function ($item, $key) {
            $item->security->security_type = strtolower($item->security->security_type);
            return $item;
        });

        return $holdingsSet->where('security.security_type', "equity");
    }

    public function getFundHoldings()
    {
        $holdings = $this->getPortfolioHoldings();
        $holdingsSet = $holdings->map(function ($item, $key) {
            $item->security->security_type = strtolower($item->security->security_type);
            return $item;
        });

        return $holdingsSet->where('security.security_type', "fund");
    }


    public function getOtherHoldings()
    {
        $holdings = $this->getPortfolioHoldings();

        $holdingsSet = $holdings->map(function ($item, $key)
        {
            $item->security->security_type = strtolower($item->security->security_type);
            if ($item->security->security_type != "equity" && $item->security->security_type != "fund")
            {
                return $item;
            }
        });

        return $holdingsSet;
    }

    public function getUpSecuritiesCount()
    {
        $holdings = $this->getPortfolioHoldings();
        $filtered = $holdings->filter(function ($value, $key) {
            return $value->security->data->change > 0;
        });

        return $filtered->count();
    }

    public function getDownSecuritiesCount()
    {
        $holdings = $this->getPortfolioHoldings();
        $filtered = $holdings->filter(function ($value, $key) {
            return $value->security->data->change < 0;
        });

        return $filtered->count();
    }

    public function getUnchangedSecuritiesCount()
    {
        $holdings = $this->getPortfolioHoldings();
        return $holdings->where('security.data.change',0)->count();
    }
}