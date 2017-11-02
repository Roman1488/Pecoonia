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
use App\Services\PortfolioHoldingsService;

class GainLossAnalysisService
{
    /**
     * Return Holdings data after applying filters
     *
     * @param sub_attributes
     * @return mixed
     */

    public function calcAnalysisBySubAttributes($portfolio_id, $sub_attributes)
    {
        $fieldsList = [
            'transactions.id',
            'transactions.security_id',
            'transactions.portfolio_id',
            'transactions.bank_id',
            DB::raw('SUM(transactions.inventory) as total_inventory'),
            DB::raw('SUM(c_net_dividend_base) as total_dividend_base'),

            DB::raw('SUM(CASE WHEN transactions.inventory > 0 THEN c_trade_value_local ELSE 0 END) AS total_trade_value_local'),
            DB::raw('SUM(CASE WHEN transactions.inventory > 0 THEN c_trade_value_base ELSE 0 END) AS total_trade_value_base')
        ];

        $transQuery = null;

        if ($sub_attributes['attribute'] == 'security_type')
        {
            $fieldsList[] = 'securities.security_type AS security_type';
            $transQuery = Transaction::select(
                            $fieldsList
                        )
                        ->join('securities', 'securities.id', '=', 'transactions.security_id')
                        ->whereIn('securities.security_type', $sub_attributes['sub_attributes']);
        }
        else if ($sub_attributes['attribute'] == 'currency')
        {
            $fieldsList[] = 'currencies.symbol AS currency';
            $transQuery = Transaction::select(
                            $fieldsList
                        )
                        ->join('securities', 'securities.id', '=', 'transactions.security_id')
                        ->join('currencies', 'currencies.id', '=', 'securities.currency_id')
                        ->whereIn('currencies.symbol', $sub_attributes['sub_attributes']);
        }
        else if ($sub_attributes['attribute'] == 'tag')
        {
            $fieldsList[] = 'transaction_tags.tag AS tag';
            $transQuery = Transaction::select(
                            $fieldsList
                        )
                        ->join('transaction_tags', 'transaction_tags.transaction_id', '=', 'transactions.id')
                        ->whereIn('transaction_tags.tag', $sub_attributes['sub_attributes']);
        }

        $transactions = $transQuery->where('transactions.portfolio_id', '=', $portfolio_id)
                                    ->whereIn('transactions.transaction_type', ['buy', 'dividend'])
                                    ->groupBy('transactions.security_id')
                                    ->havingRaw('SUM(transactions.inventory) > 0')
                                    ->get();

        $portfolioHoldingsService = new PortfolioHoldingsService($portfolio_id);
        $resultSet = $portfolioHoldingsService->calculateAnalysisValues($transactions);

        return $resultSet;
    }
}