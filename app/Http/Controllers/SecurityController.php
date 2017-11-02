<?php

namespace App\Http\Controllers;

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
use App\Services\PortfolioHoldingsService;
use App\Services\GainLossAnalysisService;
use DirkOlbrich\YahooFinanceQuery\YahooFinanceQuery;


class SecurityController extends Controller
{
    public $securityTypesToAvoid = ["Index", "Futures", "Options"];
    public $requiredSecurityFields = ["LastTradePriceOnly", "Name", "Currency", "StockExchange"];

    /**
     * Return the security if exists with the ID
     *
     * @param $id
     *
     * @return array
     * @throws
     */
    public function getSecurity($id)
    {
        try {
            $security = Security::find($id);
            if (!$security)
                throw Utils::throwError('not_found', 'Security');

            return $this->success_item("Security found with symbol [$security->symbol]", $security->complete());
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Return the securities: if the keyword is in the database return the existent security, if not search the keyword
     * in yahoo finance and create the securities
     *
     * @param $keyword
     *
     * @return array
     * @throws
     */
    public function getSecuritiesByKeyword($keyword)
    {
        $securities      = [];
        $securities_data = [];
        //string convert into upper case
        $keyword = mb_strtoupper($keyword);
        try {
            $security = Security::query()
                ->where('symbol', '=', $keyword)
                //->orWhere('name', 'LIKE', "%{$keyword}%")
                ->first();

            // First case, keyword is in the database
            if ($security && !in_array(strtolower($security->security_type), array_map("strtolower", $this->securityTypesToAvoid)))
                return $this->success_item("Security found with symbol [$security->symbol]", $security);

            // Second case, search yahoo Finance by keyword.

            $results = $this->_checkYahooFinanceByKeyword($keyword);

            if (empty($results))
                throw Utils::throwError('not_found', "Security by keyword [$keyword]");

            //Remove Securities Where security_type Are In securityTypesToAvoid
            foreach ($results as $key => $value)
            {
                if (in_array(strtolower($value['type']), array_map("strtolower", $this->securityTypesToAvoid)))
                {
                    unset($results[$key]); // remove item at index $key
                }
            }

            $results = array_values($results); // 'reindex' array

            $required_keys = ['symbol', 'Name'];

            // Filter results
            foreach ($results as $rVal)
            {
                // Make sure these important fields are in the response
                foreach ($required_keys as $key)
                {
                    if (!array_key_exists($key, $rVal) || !$rVal[$key])
                    {
                        continue 2;
                    }
                }
                // Default to USD if the currency is not set for a Security
                if (!array_key_exists('Currency', $rVal) || !$rVal['Currency'])
                {
                    $rVal['Currency'] = 'USD';
                }

                // Check if currency exists, if not then create a new Currency

                $currency = Currency::firstOrCreate(['symbol' => trim($rVal['Currency'])]);

                $securities[]      = [
                    'currency'      => $currency,
                    'currency_id'   => $currency->id,
                    'symbol'        => strtoupper($rVal['symbol']),
                    'name'          => $rVal['Name'],
                    'exchange'      => $rVal['exchange'],
                    'security_type' => $rVal['type'],
                    'security_data' => $rVal
                ];
            }

            return $this->success_item("Security found by keyword [$keyword]", $securities);
        } catch (HttpException $e) {

            return $this->error($e->getMessage(), $e->getCode());
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

    /**
    *   Search Yahoo Finance by keyword.
    */
    private function _checkYahooFinanceByKeyword($keyword)
    {
        $query = new YahooFinanceQuery();
        $setOfPrimaryInfo = $query->symbolSuggest($keyword)->get();
        $results = [];

        if ($setOfPrimaryInfo)
        {
            $symbols = array_pluck($setOfPrimaryInfo, 'symbol');

            $quotesData = $this->_checkYahooFinance($symbols);

            foreach ($quotesData as $rKey => $rVal)
            {
                $currKey = key($rVal);

                // Check for values in required fields
                if (in_array($currKey, $this->requiredSecurityFields)
                    && ($rVal[$currKey] == "N/A"))
                {
                    continue;
                }

                $rVal['Name'] = mb_convert_encoding($rVal['Name'], "UTF-8", "ASCII");

                $symbol = $rVal['Symbol'];

                unset($rVal['Symbol']);

                $rVal['symbol'] = $symbol; // because we need "symbol" key with lower case

                $results[$rKey] = $rVal;

                foreach ($setOfPrimaryInfo as $key => $value)
                {
                    if ($value['symbol'] == $results[$rKey]['symbol'])
                    {
                        $results[$rKey]['type']     = $value['typeDisp'];
                        $results[$rKey]['exchange'] = $value['exchDisp'];
                        break;
                    }
                }
            }
        }

        return $results;
    }

    /**
     * Return securities of the user logged in
     *
     * @return array
     * @throws
     */
    public function getUserSecurities()
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user)
                throw Utils::throwError('not_logged_in');

            $securities = Security::query()
                ->leftJoin('user_security', 'user_security.security_id', '=', 'securities.id')
                ->where('user_security.user_id', $user->id)
                ->get();

            return $this->success_item("Securities found", $securities);

        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Return all securities from database
     *
     * @return array
     * @throws
     */
    public function getAllSecurities(Request $request)
    {
        try {
            $securities = [];
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user)
                throw Utils::throwError('not_logged_in');

            if ($request->isMethod('post'))
            {
                $page        = $request->get('page');
                $portfolioId = $request->get('portfolio_id');

                if ($page && $portfolioId)
                {
                    if ($page === 'dividend')
                    {
                        $allLast365SecurityIds = [];
                        $zeroInvSecurityIds    = [];
                        $filteredSecurityIds   = [];

                        // Filter securities whose inventory never fell to 0 in last 365 days

                        $allLast365BuyTrans = Transaction::select('id', 'security_id', 'inventory')
                                            ->where('portfolio_id', '=', $portfolioId)
                                            ->where('transaction_type', '=', 'buy')
                                            ->where('date', '>=', Carbon::now()->subDays(365)->toDateTimeString())
                                            ->get();

                        if ($allLast365BuyTrans)
                        {
                            foreach ($allLast365BuyTrans as $security)
                            {
                                $allLast365SecurityIds[$security->security_id] = $security->security_id;

                                if (!$security->inventory || $security->inventory <= 0)
                                {
                                    $zeroInvSecurityIds[$security->security_id] = $security->security_id;
                                }
                            }

                            $filteredSecurityIds = array_diff($allLast365SecurityIds, $zeroInvSecurityIds);
                        }

                        // Get Latest transaction of each security after 365days, OTHER THAN ones occuring in last 365 days

                        $after365Query = Transaction::select('id', 'security_id', 'inventory')
                                        ->where('portfolio_id', '=', $portfolioId)
                                        ->where('transaction_type', '=', 'buy')
                                        ->where('date', '<', Carbon::now()->subDays(365)->toDateTimeString())
                                        ->orderBy('date', 'desc');

                        if ($allLast365SecurityIds)
                        {
                            $after365Query->whereNotIn('security_id', $allLast365SecurityIds);
                        }

                        $after365BuyTrans = $after365Query->get()->unique('security_id');

                        if ($after365BuyTrans)
                        {
                            foreach ($after365BuyTrans as $security)
                            {
                                if ($security->inventory > 0)
                                {
                                    $filteredSecurityIds[$security->security_id] = $security->security_id;
                                }
                            }
                        }
                    }
                    else
                    {
                        $filteredSecurityIds = Transaction::select('id', 'security_id')
                                            ->where('portfolio_id', '=', $portfolioId)
                                            ->where('transaction_type', '=', 'buy')
                                            ->groupBy('security_id')
                                            ->havingRaw('SUM(inventory) > 0')
                                            ->get()
                                            ->pluck('security_id');
                    }

                    $securities = Security::whereIn('id', $filteredSecurityIds)->get();
                }
            }
            else
            {
                $securities = Security::whereNotIn('security_type', array_map("strtolower", $this->securityTypesToAvoid))->get();
            }

            return $this->success_item("Securities found", $securities);

        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Return securities of the user logged in and match with the portfolio ID
     *
     * @param $id
     *
     * @return array
     * @throws
     */
    public function getPortfolioSecurities($id)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user)
                throw Utils::throwError('not_logged_in');

            $portfolio = Portfolio::query()
                ->with(['securities'])
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$portfolio)
                throw Utils::throwError('not_found', 'Portfolio');

            return $this->success_item("Securities found", $portfolio);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /*
        To Get Transactions Of Individual Security For Individual User
    */

    public function getTransactionsBySecurity($portfolio_id, $security_id)
    {
        try
        {
            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user)
                throw Utils::throwError('not_logged_in');

            if (!$security = Security::find($security_id))
                throw Utils::throwError('not_found', "Security");

            if (!$portfolio = Portfolio::where('id', $portfolio_id)->where('user_id', $user->id)->first())
                throw Utils::throwError('not_found', "Portfolio");

            $transactions = Transaction::select('date', 'quantity', 'c_trade_value_local', 'c_trade_quote_local')
                                            ->where('security_id', $security_id)
                                            ->where('portfolio_id', $portfolio_id)
                                            ->where('transaction_type', 'buy')
                                            ->where('inventory', '>', 0)
                                            ->get();

            if (!$transactions)
                throw Utils::throwError('not_found', "Transaction");

            $securitydata = SecurityData::select('last_trade_price_only')->where('security_id', $security_id)->first();

            // If security currency = portfolio currency, calculate c_trade_quote_local = c_trade_value_local / quantity

            $secCurrencySameAsPortfolio = false;

            if ($portfolio->currency_id == $security->currency_id)
            {
                $secCurrencySameAsPortfolio = true;
            }

            foreach ($transactions as $transaction)
            {
                //This will calculate the owned days : differance between now date and date of transaction set by user

                $tDate = Carbon::parse($transaction->date);
                $now = Carbon::now();

                $transaction->days_owned = $tDate->diffInDays($now);

                $transaction->gain_loss = (($securitydata->last_trade_price_only * $transaction->quantity) - ($transaction->c_trade_value_local));

                //This will calculate the return %

                $transaction->return_percentage = ($transaction->c_trade_value_local > 0) ? round((($transaction->gain_loss / $transaction->c_trade_value_local) * 100), 2) : 0;

                if ($secCurrencySameAsPortfolio && ($transaction->quantity > 0))
                {
                    $transaction->c_trade_quote_local = $transaction->c_trade_value_local / $transaction->quantity;
                }
            }

            return $this->success_item("Transactions found", $transactions);
        }

        catch (HttpException $e)
        {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }


    /**
     * Return key figures for specified from ~ to date
     *
     * @param POST Request : @from, @to
     *
     * @return mixed
     * @throws exception if not_logged_in
     */

    public function getKeyFigures($portfolio_id, Request $request)
    {
        if (!$request->isMethod('post'))
            throw Utils::throwError('custom', "Invalid Request");

        // If from and to dates are not passed, default is Last 365 days

        //get 'from' date from POST Request
        if($request->get('from') == NULL)
        {
            $from = (new Carbon('last year'))->toDateString();
        }
        else
        {
            $from = $request->get('from');
        }

        //get 'to' date from POST Request
        if($request->get('to') == NULL)
        {
            $to = (new Carbon())->toDateString();
        }
        else
        {
            $to = $request->get('to');
        }

        try
        {
            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user)
                throw Utils::throwError('not_logged_in');

            if (!$portfolio = Portfolio::where('id', $portfolio_id)->where('user_id', $user->id)->first())
                throw Utils::throwError('not_found', "Portfolio");

            $set1Query = Transaction::select(
                DB::raw('SUM(c_trade_value_base) AS turnover'),
                DB::raw('SUM(CASE WHEN `transaction_type` = "buy" THEN 1 ELSE 0 END) AS buy_trades'),
                DB::raw('SUM(CASE WHEN `transaction_type` = "sell" THEN 1 ELSE 0 END) AS sell_trades')
            )
            ->where('portfolio_id', $portfolio_id);

            $dateRangeSet1Query = clone $set1Query;

            $dateRangeSet1 = $dateRangeSet1Query->where('date', '>=', $from)
                                                ->where('date', '<=', $to)
                                                ->first();

            if (!$dateRangeSet1)
                throw Utils::throwError('not_found', "Transaction");

            $set2Query = Transaction::select(
                DB::raw('SUM(CASE WHEN `c_profit_loss_purchase_value_base` < 0 THEN `c_profit_loss_purchase_value_base` ELSE 0 END) AS loss_realized'),
                DB::raw('SUM(CASE WHEN `transaction_type` = "sell" THEN `c_trade_value_base` ELSE 0 END) AS realized_turnover'),
                DB::raw('SUM(CASE WHEN `c_profit_loss_purchase_value_base` > 0 THEN `c_profit_loss_purchase_value_base` ELSE 0 END) AS profit_realized'),
                DB::raw('SUM(`c_currency_profit_loss_purchase_price`) AS currency_profit_loss'),
                DB::raw('SUM(`c_net_dividend_base`) AS net_dividend'),
                DB::raw('SUM(`c_commision_base`) AS commision')
            )
            ->where('portfolio_id', $portfolio_id);

            $dateRangeSet2Query = clone $set2Query;

            $dateRangeSet2 = $dateRangeSet2Query->where('date','>=',$from)
                                                ->where('date','<=',$to)
                                                ->first();

            if (!$dateRangeSet2)
                throw Utils::throwError('not_found', "Transaction");

            $resultSet['past_year']['Total_Turnover']             = $dateRangeSet1->turnover;
            $resultSet['past_year']['Total_no._of_Trades']        = $dateRangeSet1->sell_trades + $dateRangeSet1->buy_trades;
            $resultSet['past_year']['Realized_Turnover']          = $dateRangeSet2->realized_turnover;
            $resultSet['past_year']['Realized_Trades']            = $dateRangeSet1->sell_trades;
            $resultSet['past_year']['Profit_Realized']            = $dateRangeSet2->profit_realized;
            $resultSet['past_year']['Loss_Realized']              = $dateRangeSet2->loss_realized;
            $resultSet['past_year']['Income_from_Dividends']      = $dateRangeSet2->net_dividend;

            $resultSet['past_year']['Net_Result']                 = $dateRangeSet2->profit_realized + $dateRangeSet2->loss_realized + $dateRangeSet2->net_dividend;

            $resultSet['past_year']['Realized_Return_%']          = ($dateRangeSet2->realized_turnover != 0) ? (($resultSet['past_year']['Net_Result'] / $dateRangeSet2->realized_turnover) * 100) : 0;

            $resultSet['past_year']['Currency_Profit/Loss']       = $dateRangeSet2->currency_profit_loss;

            $resultSet['past_year']['Currency_Adjusted_Result']   = ($resultSet['past_year']['Net_Result'] - $dateRangeSet2->currency_profit_loss);

            $resultSet['past_year']['Currency_Adjusted_Return_%'] = ($dateRangeSet2->realized_turnover != 0) ? (($resultSet['past_year']['Currency_Adjusted_Result'] / $dateRangeSet2->realized_turnover) * 100) : 0;

            $resultSet['past_year']['Commision_Paid']             = $dateRangeSet2->commision;

            //calculate commision_percentage using commision as sum of c_commision_base and turnover as sum of c_trade_value_base
            $resultSet['past_year']['Commision_%'] = ($resultSet['past_year']['Total_Turnover'] != 0) ? (($resultSet['past_year']['Commision_Paid'] / $resultSet['past_year']['Total_Turnover']) * 100) : 0;

            //Calculate since_portfolio_start

            $sincePortfolioSet1Query = clone $set1Query;

            $sincePortfolioSet1 = $sincePortfolioSet1Query->first();

            if (!$sincePortfolioSet1)
                throw Utils::throwError('not_found', "Transaction");

            $sincePortfolioSet2Query = clone $set2Query;

            $sincePortfolioSet2 = $sincePortfolioSet2Query->first();

            if (!$sincePortfolioSet2)
                throw Utils::throwError('not_found', "Transaction");

            //Append since_portfolio_start in the resultant array
            $resultSet['since_portfolio_start']['Total_Turnover']             = $sincePortfolioSet1->turnover;
            $resultSet['since_portfolio_start']['Total_no._of_Trades']        = $sincePortfolioSet1->sell_trades + $sincePortfolioSet1->buy_trades;
            $resultSet['since_portfolio_start']['Realized_Turnover']          = $sincePortfolioSet2->realized_turnover;
            $resultSet['since_portfolio_start']['Realized_Trades']            = $sincePortfolioSet1->sell_trades;
            $resultSet['since_portfolio_start']['Profit_Realized']            = $sincePortfolioSet2->profit_realized;
            $resultSet['since_portfolio_start']['Loss_Realized']              = $sincePortfolioSet2->loss_realized;
            $resultSet['since_portfolio_start']['Income_from_Dividends']      = $sincePortfolioSet2->net_dividend;

            $resultSet['since_portfolio_start']['Net_Result']                 = $sincePortfolioSet2->profit_realized + $sincePortfolioSet2->loss_realized + $sincePortfolioSet2->net_dividend;

            $resultSet['since_portfolio_start']['Realized_Return_%']          = ($sincePortfolioSet2->realized_turnover != 0) ? (($resultSet['since_portfolio_start']['Net_Result'] / $sincePortfolioSet2->realized_turnover) * 100) : 0;

            $resultSet['since_portfolio_start']['Currency_Profit/Loss']       = $sincePortfolioSet2->currency_profit_loss;

            $resultSet['since_portfolio_start']['Currency_Adjusted_Result']   = ($resultSet['since_portfolio_start']['Net_Result'] - $sincePortfolioSet2->currency_profit_loss);

            $resultSet['since_portfolio_start']['Currency_Adjusted_Return_%'] = ($sincePortfolioSet2->realized_turnover != 0) ? (($resultSet['since_portfolio_start']['Currency_Adjusted_Result']  / $sincePortfolioSet2->realized_turnover) * 100) : 0;

            $resultSet['since_portfolio_start']['Commision_Paid']             = $sincePortfolioSet2->commision;

            //calculate commision_percentage using commision as sum of c_commision_base and turnover as sum of c_trade_value_base
            $resultSet['since_portfolio_start']['Commision_%'] = ($resultSet['since_portfolio_start']['Total_Turnover'] != 0) ? (($resultSet['since_portfolio_start']['Commision_Paid'] / $resultSet['since_portfolio_start']['Total_Turnover']) * 100) : 0;

            return $this->success_item("key figures found", $resultSet);
        }
        catch (HttpException $e)
        {
            return $this->error($e->getMessage(), $e->getCode());
        }

    }


    private function _calcIncomeAnaBySubAttributes($portfolio_id, $sub_attributes, $from, $to)
    {
        $fieldsList = [
            'transactions.id',
            'transactions.security_id',
            'transactions.portfolio_id',
            'transactions.bank_id',
            DB::raw('SUM(c_net_dividend_base) as net_dividend_base'),
            DB::raw('SUM(CASE WHEN transactions.c_profit_loss_purchase_value_base > 0 THEN c_trade_value_base ELSE 0 END) AS trade_value_base_profit'),
            DB::raw('SUM(CASE WHEN transactions.c_profit_loss_purchase_value_base < 0 THEN c_trade_value_base ELSE 0 END) AS trade_value_base_loss'),

            DB::raw('SUM(CASE WHEN transactions.c_profit_loss_purchase_value_base > 0 THEN c_profit_loss_purchase_value_base ELSE 0 END) AS profits_base'),

            DB::raw('SUM(CASE WHEN transactions.c_profit_loss_purchase_value_base < 0 THEN c_profit_loss_purchase_value_base ELSE 0 END) AS losses_base')
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
                                    ->whereIn('transactions.transaction_type', ['sell', 'dividend'])
                                    ->groupBy('transactions.security_id')
                                    ->whereBetween('date', [$from, $to])
                                    ->get();
        return $transactions;
    }



    /**
     * Return Income Analysis on Holdings
     *
     * @param POST Request : @security_types, @currencies, @tags
     * @param Type : Array
     * @return mixed
     * @throws exception if not_logged_in
     */

    public function getIncomeAnalysis($portfolio_id, Request $request)
    {
        try
        {
            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user)
                throw Utils::throwError('not_logged_in');

            if (!$portfolio = Portfolio::where('id', $portfolio_id)->where('user_id', $user->id)->first())
                throw Utils::throwError('not_found', "Portfolio");

            if (!$request->isMethod('post'))
                throw Utils::throwError('custom', "Invalid Request");

            $subAttributes = [];

            if ($request->get('Security_Types'))
            {
                $subAttributes['attribute'] = 'security_type';
                $subAttributes['sub_attributes'] = $request->get('Security_Types');
            }
            else if ($request->get('Tags'))
            {
                $subAttributes['attribute'] = 'tag';
                $subAttributes['sub_attributes'] = $request->get('Tags');
            }
            else if ($request->get('Currencies'))
            {
                $subAttributes['attribute'] = 'currency';
                $subAttributes['sub_attributes'] = $request->get('Currencies');
            }
            else
            {
                throw Utils::throwError('custom', "Invalid Request");
            }

            // If from and to dates are not passed, default is Last 365 days

            //get 'from' date from POST Request
            if($request->get('from') == NULL)
            {
                $from = (new Carbon('last year'))->toDateString();
            }
            else
            {
                $from = $request->get('from');
            }

            //get 'to' date from POST Request
            if($request->get('to') == NULL)
            {
                $to = (new Carbon())->toDateString();
            }
            else
            {
                $to = $request->get('to');
            }


            $transactions = $this->_calcIncomeAnaBySubAttributes($portfolio_id, $subAttributes, $from, $to);

            //All Rows Of Profit Loss Analysis For Holdings
            $profitByAttribute = [];
            $lossByAttribute   = [];

            //Last Row Of Profit Analysis
            $totalsOfProfits                                  = [];
            $totalsOfProfits['total_profits_base']            = 0;
            $totalsOfProfits['total_trade_value_base_profit'] = 0;
            $totalsOfProfits['total_net_dividend_base']       = 0;

            //Last Row Of Loss Analysis
            $totalsOfLoss                                = [];
            $totalsOfLoss['total_losses_base']           = 0;
            $totalsOfLoss['total_trade_value_base_loss'] = 0;

            //total of all weight % coloumns
            $totalsOfProfits['total_weight_for_profits_base']            = 0;
            $totalsOfProfits['total_weight_for_trade_value_base_profit'] = 0;
            $totalsOfProfits['total_weight_for_net_dividend_base']       = 0;
            $totalsOfLoss['total_weight_for_losses_base']                = 0;
            $totalsOfLoss['total_weight_for_trade_value_base_loss']      = 0;

            //now we got transactions grouped by security with inventory > 0

            foreach ($subAttributes['sub_attributes'] as $subAttribute)
            {
                //get sum of market_value_in_base, gain_loss_in_base & dividend for this currency : currencyId
                foreach ($transactions as $transaction)
                {
                    if ($subAttribute == $transaction[$subAttributes['attribute']])
                    {
                        if (!isset($profitByAttribute[$subAttribute]))
                        {
                            $profitByAttribute[$subAttribute]                            = [];
                            $profitByAttribute[$subAttribute]['profits_base']            = 0;
                            $profitByAttribute[$subAttribute]['trade_value_base_profit'] = 0;
                            $profitByAttribute[$subAttribute]['net_dividend_base']       = 0;
                        }

                        if (!isset($lossByAttribute[$subAttribute]))
                        {
                            $lossByAttribute[$subAttribute]                          = [];
                            $lossByAttribute[$subAttribute]['losses_base']           = 0;
                            $lossByAttribute[$subAttribute]['trade_value_base_loss'] = 0;
                        }

                        $profitByAttribute[$subAttribute]['profits_base']            += $transaction['profits_base'];
                        $profitByAttribute[$subAttribute]['trade_value_base_profit'] += $transaction['trade_value_base_profit'];
                        $lossByAttribute[$subAttribute]['losses_base']               += $transaction['losses_base'];
                        $lossByAttribute[$subAttribute]['trade_value_base_loss']     += $transaction['trade_value_base_loss'];
                        $profitByAttribute[$subAttribute]['net_dividend_base']       += $transaction['net_dividend_base'];
                    }
                }

                if($profitByAttribute && isset($profitByAttribute[$subAttribute]))
                {
                    //Add Current Profit / Loss Row To The Last Row As Totals To Generate Total Of Coloumn
                    $totalsOfProfits['total_profits_base']            += $profitByAttribute[$subAttribute]['profits_base'];
                    $totalsOfProfits['total_trade_value_base_profit'] += $profitByAttribute[$subAttribute]['trade_value_base_profit'];
                    $totalsOfProfits['total_net_dividend_base']       += $profitByAttribute[$subAttribute]['net_dividend_base'];
                    $totalsOfLoss['total_losses_base']                += $lossByAttribute[$subAttribute]['losses_base'];
                    $totalsOfLoss['total_trade_value_base_loss']      += $lossByAttribute[$subAttribute]['trade_value_base_loss'];
                }

            }

            //Now Generate weight % for each coloumn of profit table
            foreach ($profitByAttribute as $attr => $value)
            {
                $profitByAttribute[$attr]['weight_for_profits_base'] = ($totalsOfProfits['total_profits_base'] != 0) ?
                    ($value['profits_base'] / $totalsOfProfits['total_profits_base']) * 100
                    : 0;

                $totalsOfProfits['total_weight_for_profits_base'] += $profitByAttribute[$attr]['weight_for_profits_base'];

                $profitByAttribute[$attr]['weight_for_trade_value_base_profit'] = ($totalsOfProfits['total_trade_value_base_profit'] > 0) ?
                    ($value['trade_value_base_profit'] / $totalsOfProfits['total_trade_value_base_profit']) * 100
                    : 0;

                $totalsOfProfits['total_weight_for_trade_value_base_profit'] += $profitByAttribute[$attr]['weight_for_trade_value_base_profit'];

                $profitByAttribute[$attr]['weight_for_net_dividend_base'] = ($totalsOfProfits['total_net_dividend_base'] != 0) ?
                        ($value['net_dividend_base'] / $totalsOfProfits['total_net_dividend_base']) * 100
                        : 0;

                $totalsOfProfits['total_weight_for_net_dividend_base'] += round($profitByAttribute[$attr]['weight_for_net_dividend_base']);
            }

            //Now Generate weight % for each coloumn of loss table
            foreach ($lossByAttribute as $attr => $value)
            {
                $lossByAttribute[$attr]['weight_for_losses_base'] = ($totalsOfLoss['total_losses_base'] != 0) ?
                        ($value['losses_base'] / $totalsOfLoss['total_losses_base']) * 100
                        : 0;

                $totalsOfLoss['total_weight_for_losses_base'] += $lossByAttribute[$attr]['weight_for_losses_base'];

                $lossByAttribute[$attr]['weight_for_trade_value_base_loss'] = ($totalsOfLoss['total_trade_value_base_loss'] != 0) ?
                        ($value['trade_value_base_loss'] / $totalsOfLoss['total_trade_value_base_loss']) * 100
                        : 0;

                $totalsOfLoss['total_weight_for_trade_value_base_loss'] += $lossByAttribute[$attr]['weight_for_trade_value_base_loss'];
            }

            //prepare final result set to send
            $finalSet['profit']['profitBy']        = $profitByAttribute;
            $finalSet['profit']['totalsOfProfits'] = $totalsOfProfits;
            $finalSet['loss']['lossBy']            = $lossByAttribute;
            $finalSet['loss']['totalsOfLoss']      = $totalsOfLoss;

            return $this->success_item("Income analysis found", $finalSet);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Return Gain Loss Analysis on Holdings
     *
     * @param POST Request : @security_types, @currencies, @tags
     * @param Type : Array
     * @return mixed
     * @throws exception if not_logged_in
     */

    public function getHoldingsAnalysis($portfolio_id, Request $request)
    {
        try
        {
            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user)
                throw Utils::throwError('not_logged_in');

            if (!$portfolio = Portfolio::where('id', $portfolio_id)->where('user_id', $user->id)->first())
                throw Utils::throwError('not_found', "Portfolio");

            if (!$request->isMethod('post'))
                throw Utils::throwError('custom', "Invalid Request");

            $subAttributes = [];

            if ($request->get('Security_Types'))
            {
                $subAttributes['attribute'] = 'security_type';
                $subAttributes['sub_attributes'] = $request->get('Security_Types');
            }
            else if ($request->get('Tags'))
            {
                $subAttributes['attribute'] = 'tag';
                $subAttributes['sub_attributes'] = $request->get('Tags');
            }
            else if ($request->get('Currencies'))
            {
                $subAttributes['attribute'] = 'currency';
                $subAttributes['sub_attributes'] = $request->get('Currencies');
            }
            else
            {
                throw Utils::throwError('custom', "Invalid Request");
            }

            $gainLossAnalysisService = new GainLossAnalysisService();

            $transactions = $gainLossAnalysisService->calcAnalysisBySubAttributes($portfolio_id, $subAttributes);

            //All Rows Of Gain Loss Analysis For Holdings
            $gainByAttribute = [];
            $lossByAttribute = [];

            //Last Row Of Gain Analysis
            $totalsOfGain                               = [];
            $totalsOfGain['total_market_value_in_base'] = 0;
            $totalsOfGain['total_gain_loss_in_base']    = 0;
            $totalsOfGain['total_dividend']             = 0;

            //Last Row Of Loss Analysis
            $totalsOfLoss                               = [];
            $totalsOfLoss['total_market_value_in_base'] = 0;
            $totalsOfLoss['total_gain_loss_in_base']    = 0;

            //total of all weight % coloumns
            $totalsOfGain['total_weight_for_market_value_in_base'] = 0;
            $totalsOfGain['total_weight_for_gain_loss_in_base']    = 0;
            $totalsOfGain['total_weight_for_dividend']             = 0;
            $totalsOfLoss['total_weight_for_market_value_in_base'] = 0;
            $totalsOfLoss['total_weight_for_gain_loss_in_base']    = 0;

            //now we got holdings grouped by security with inventory > 0

            foreach ($subAttributes['sub_attributes'] as $subAttribute)
            {
                //get sum of market_value_in_base, gain_loss_in_base & dividend for this currency : currencyId
                foreach ($transactions as $transaction)
                {
                    if ($subAttribute == $transaction[$subAttributes['attribute']])
                    {
                        if (!isset($gainByAttribute[$subAttribute]))
                        {
                            $gainByAttribute[$subAttribute] = [];
                            $gainByAttribute[$subAttribute]['gain_loss_in_base'] = 0;
                            $gainByAttribute[$subAttribute]['market_value_in_base'] = 0;
                            $gainByAttribute[$subAttribute]['dividend'] = 0;
                        }

                        if (!isset($lossByAttribute[$subAttribute]))
                        {
                            $lossByAttribute[$subAttribute] = [];
                            $lossByAttribute[$subAttribute]['gain_loss_in_base'] = 0;
                            $lossByAttribute[$subAttribute]['market_value_in_base'] = 0;
                        }

                        //store addition in gain / loss table set
                        if($transaction['gain_loss_in_base'] > 0)
                        {
                            $gainByAttribute[$subAttribute]['gain_loss_in_base'] += $transaction['gain_loss_in_base'];
                            $gainByAttribute[$subAttribute]['market_value_in_base'] += $transaction['market_value_in_base'];
                        }
                        else if($transaction['gain_loss_in_base'] < 0)
                        {
                            $lossByAttribute[$subAttribute]['gain_loss_in_base'] += $transaction['gain_loss_in_base'];
                            $lossByAttribute[$subAttribute]['market_value_in_base'] += $transaction['market_value_in_base'];
                        }

                        $gainByAttribute[$subAttribute]['dividend'] += $transaction['total_dividend_base'];
                    }
                }

                //Add Current Gain / Loss Row To The Last Row As Totals To Generate Total Of Coloumn
                $totalsOfGain['total_gain_loss_in_base'] += $gainByAttribute[$subAttribute]['gain_loss_in_base'];
                $totalsOfGain['total_market_value_in_base'] += $gainByAttribute[$subAttribute]['market_value_in_base'];
                $totalsOfGain['total_dividend'] += $gainByAttribute[$subAttribute]['dividend'];

                $totalsOfLoss['total_market_value_in_base'] += $lossByAttribute[$subAttribute]['market_value_in_base'];
                $totalsOfLoss['total_gain_loss_in_base'] += $lossByAttribute[$subAttribute]['gain_loss_in_base'];
            }

            //Now Generate weight % for each coloumn of gain table
            foreach ($gainByAttribute as $attr => $value)
            {
                $gainByAttribute[$attr]['weight_for_market_value_in_base'] = ($totalsOfGain['total_market_value_in_base'] > 0) ?
                    ($value['market_value_in_base'] / $totalsOfGain['total_market_value_in_base']) * 100
                    : 0;

                $totalsOfGain['total_weight_for_market_value_in_base'] += $gainByAttribute[$attr]['weight_for_market_value_in_base'];

                $gainByAttribute[$attr]['weight_for_gain_loss_in_base'] = ($totalsOfGain['total_gain_loss_in_base'] > 0) ?
                    ($value['gain_loss_in_base'] / $totalsOfGain['total_gain_loss_in_base']) * 100
                    : 0;

                $totalsOfGain['total_weight_for_gain_loss_in_base'] += $gainByAttribute[$attr]['weight_for_gain_loss_in_base'];

                $gainByAttribute[$attr]['weight_for_dividend'] = ($totalsOfGain['total_dividend'] > 0) ?
                        ($value['dividend'] / $totalsOfGain['total_dividend']) * 100
                        : 0;

                $totalsOfGain['total_weight_for_dividend'] += ceil($gainByAttribute[$attr]['weight_for_dividend']);
            }

            //Now Generate weight % for each coloumn of loss table
            foreach ($lossByAttribute as $attr => $value)
            {
                $lossByAttribute[$attr]['weight_for_market_value_in_base'] = ($totalsOfLoss['total_market_value_in_base'] > 0) ?
                        ($value['market_value_in_base'] / $totalsOfLoss['total_market_value_in_base']) * 100
                        : 0;

                $totalsOfLoss['total_weight_for_market_value_in_base'] += $lossByAttribute[$attr]['weight_for_market_value_in_base'];

                $lossByAttribute[$attr]['weight_for_gain_loss_in_base'] = ($totalsOfLoss['total_gain_loss_in_base'] != 0) ?
                        ($value['gain_loss_in_base'] / $totalsOfLoss['total_gain_loss_in_base']) * 100
                        : 0;

                $totalsOfLoss['total_weight_for_gain_loss_in_base'] += $lossByAttribute[$attr]['weight_for_gain_loss_in_base'];
            }

            //prepare final result set to send
            $finalSet['gain']['gainBy'] = $gainByAttribute;
            $finalSet['gain']['totalsOfGain'] = $totalsOfGain;
            $finalSet['loss']['lossBy'] = $lossByAttribute;
            $finalSet['loss']['totalsOfLoss'] = $totalsOfLoss;

            return $this->success_item("Holdings analysis found", $finalSet);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function getHoldingsAnalysisAttributes($portfolio_id)
    {
        try
        {
            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user)
                throw Utils::throwError('not_logged_in');

            if (!$portfolio = Portfolio::where('id', $portfolio_id)->where('user_id', $user->id)->first())
                throw Utils::throwError('not_found', "Portfolio");

            $transactionTags = TransactionTag::query()
                ->select(DB::raw('DISTINCT(transaction_tags.tag) AS attrName'), DB::raw('"Tags" AS attribute'))
                ->join('transactions', 'transactions.id', '=', 'transaction_tags.transaction_id')
                ->where('transactions.inventory', '>', 0)
                ->where('transactions.portfolio_id', $portfolio_id)
                ->get()
                ->toArray();

            $securityTypes = Security::query()
                ->select(DB::raw('DISTINCT(securities.security_type) AS attrName'), DB::raw('"Security Types" AS attribute'))
                ->join('transactions', 'transactions.security_id', '=', 'securities.id')
                ->where('transactions.inventory', '>', 0)
                ->where('transactions.portfolio_id', $portfolio_id)
                ->get()
                ->toArray();

            $currencies = Currency::query()
                ->select(DB::raw('DISTINCT(currencies.symbol) AS attrName'), DB::raw('"Currencies" AS attribute'), DB::raw('currencies.name AS subAttrName'))
                ->join('securities', 'securities.currency_id', '=', 'currencies.id')
                ->join('transactions','transactions.security_id', '=', 'securities.id')
                ->where('transactions.inventory', '>', 0)
                ->where('transactions.portfolio_id', $portfolio_id)
                ->get()
                ->toArray();

            $final_set = [];
            $final_set = array_merge($currencies, $securityTypes, $transactionTags);

            return $this->success_item("Holdings analysis attributes found", $final_set);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Return portfolio holdings for given portfolio
     *
     * @param @id : portfolio_id
     *
     * @return mixed
     * @throws exception if not_logged_in OR portfolio not found
     */

    public function getPortfolioHoldings(Request $request, $id)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user)
                throw Utils::throwError('not_logged_in');

            if (!$portfolio = Portfolio::where('id', $id)->where('user_id', $user->id)->first())
                    throw Utils::throwError('not_found', "Portfolio");

            $portfolioHoldingsService = new PortfolioHoldingsService($id);

            $transactions = $portfolioHoldingsService->getPortfolioHoldings();

            return $this->success_item("Securities found", $transactions);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }



    /**
     * Return the current security watchlist for specified portfolio by param
     *
     * @param $portfolio_id
     *
     * @return mixed
     * @throws exception if not_logged_in OR wrong portfolio_id
     */

    public function getSecurityWatchlist($portfolio_id)
    {
        try
        {
            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user)
                throw Utils::throwError('not_logged_in');

            if (!$portfolio = Portfolio::where('id', $portfolio_id)->where('user_id', $user->id)->first())
                throw Utils::throwError('not_found', "Portfolio");

            $securities = Security::query()
                ->select('securities.*', 'security_watchlist.created_at AS date_added',
                    'security_watchlist.price_added_watchlist', 'security_data.last_trade_price_only')
                ->join('security_watchlist', 'security_watchlist.security_id', '=', 'securities.id')
                ->join('security_data', 'security_data.security_id', '=', 'securities.id')
                ->where('security_watchlist.portfolio_id', $portfolio_id)
                ->get();

                foreach ($securities as $key => $value)
                {
                    $lastTradePriceOnly = $value['last_trade_price_only'];
                    $priceAddedWatchlist = $value['price_added_watchlist'];

                    $securities[$key]['ltp_change_since_added'] = $lastTradePriceOnly - $priceAddedWatchlist;
                    $securities[$key]['ltp_change_percent'] = (( $lastTradePriceOnly - $priceAddedWatchlist) /
                        $lastTradePriceOnly ) * 100 ;
                }

            return $this->success_item("securities found for watchlist", $securities);
        }
        catch (HttpException $e)
        {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Return the unwatched securities which will be holded by choose list to add to watchlist for given portfolio
     *
     * @param $portfolio_id
     *
     * @return mixed
     * @throws exception if not_logged_in OR wrong portfolio_id
     */

    public function getUnwatchedSecurities($portfolio_id)
    {
        try
        {
            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user)
                throw Utils::throwError('not_logged_in');

            if (!$portfolio = Portfolio::where('id', $portfolio_id)->where('user_id', $user->id)->first())
                throw Utils::throwError('not_found', "Portfolio");

            $currSecuritySet = SecurityWatchlist::where('portfolio_id',$portfolio_id)->get()->pluck('security_id');

            $securities = Security::whereNotIn('id', $currSecuritySet)->get();
            return $this->success_item("securities found for watchlist", $securities);
        }
        catch (HttpException $e)
        {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }


    /**
     * Add provided securities to watchlist & assign auto incremental watchlist_id
     *
     * @param POST Request :  @addToWatchlist : portfolio_id, array of securities
     *
     * @return mixed
     * @throws exception if not_logged_in OR wrong portfolio_id
     */

    public function addSecuritiesToWatchlist(Request $request)
    {
        try
        {
            if (!$request->isMethod('post'))
                throw Utils::throwError('custom', "Invalid Request");

            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user)
                throw Utils::throwError('not_logged_in');

            if($request->get('addToWatchlist') == NULL || !is_array($request->get('addToWatchlist')))
                throw Utils::throwError('custom', "Invalid Request");

            $addToWatchlist = $request->get('addToWatchlist');

            if (!$portfolio = Portfolio::where('id', $addToWatchlist['portfolio_id'])->where('user_id', $user->id)->first())
                throw Utils::throwError('not_found', "Portfolio");

            $entriesAddedToWatchlist = [];

            foreach ($addToWatchlist['security_ids'] as $key => $watch)
            {
                if ($watch === 0 || !$security_data = Security::where('id', $watch)->first()->toArray())
                    throw Utils::throwError('not_found', "Security");

                $addToSecurityWatch = new SecurityWatchlist();
                $addToSecurityWatch->portfolio_id = $addToWatchlist['portfolio_id'];
                $addToSecurityWatch->security_id  = $watch;

                $lastTradePriceDataset = SecurityData::select('last_trade_price_only')
                                                        ->where('security_id', $watch)
                                                        ->first();
                $lastTradePriceOnly = $lastTradePriceDataset['last_trade_price_only'];

                $addToSecurityWatch->price_added_watchlist = $lastTradePriceOnly;

                $addToSecurityWatch->save();

                //Generate Result Set For FrontEnd

                $entriesAddedToWatchlist[$key] = $security_data;
                $entriesAddedToWatchlist[$key]['date_added'] = Carbon::now()->toDateTimeString();
                $entriesAddedToWatchlist[$key]['price_added_watchlist'] =  $addToSecurityWatch->price_added_watchlist;

                $entriesAddedToWatchlist[$key]['ltp_change_since_added'] = $lastTradePriceOnly - $addToSecurityWatch->price_added_watchlist;

                $entriesAddedToWatchlist[$key]['ltp_change_percent'] = (( $lastTradePriceOnly - $addToSecurityWatch->price_added_watchlist ) /  $lastTradePriceOnly) * 100 ;
            }

            return $this->success_item("Successfully Added To Watchlist", $entriesAddedToWatchlist);
        }
        catch (HttpException $e)
        {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Remove securities from watchlist
     *
     * @param @portfolio_id, @security_id
     *
     * @return mixed
     * @throws exception if not_logged_in OR required params not found
     */

    public function removeSecurityFromWatchlist($portfolio_id, $security_id)
    {
        try
        {
            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user)
                throw Utils::throwError('not_logged_in');

            if (!$portfolio = Portfolio::where('id', $portfolio_id)->where('user_id', $user->id)->first())
                throw Utils::throwError('not_found', "Portfolio");

            $deletedRows = SecurityWatchlist::where('portfolio_id', $portfolio_id)
                                            ->where('security_id', $security_id)
                                            ->delete();

            return $this->success_item("Removed From Watchlist", $deletedRows);
        }
        catch (HttpException $e)
        {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }


    /**
     * Store provided securities to database
     *
     * @param POST Request :  @securitiesToAdd : array of securities
     *
     * @return mixed
     * @throws exception if not_logged_in OR securitiesToAdd param not found
     */

    public function addNewSecurities(Request $request)
    {
        try
        {
            if (!$request->isMethod('post'))
                throw Utils::throwError('custom', "Invalid Request");

            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user)
                throw Utils::throwError('not_logged_in');

            if($request->get('securitiesToAdd') == NULL || !is_array($request->get('securitiesToAdd')))
                throw Utils::throwError('custom', "Invalid Request");

            $securitiesToAdd = $request->get('securitiesToAdd');

            $secAddedSuccessfully = [];

            foreach ($securitiesToAdd as $k => $eachSecurity)
            {
                $securityModelObj = new Security();
                $security = $securityModelObj->saveSecurityData($eachSecurity['security_data'], $eachSecurity['currency']);

                $secAddedSuccessfully[$k] = $security;
                $secAddedSuccessfully[$k]['data'] = $security->data;
            }

            return $this->success_item("Securities Added Successfully", $secAddedSuccessfully);
        }
        catch (HttpException $e)
        {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }


    public function getPreviousPortfolioHoldings($portfolioId, Request $request)
    {
        try
        {
            if (!$request->isMethod('post'))
            throw Utils::throwError('custom', "Invalid Request");

            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user)
            throw Utils::throwError('not_logged_in');

            if($request->get('date') == NULL)
            throw Utils::throwError('custom', "Invalid Request");

            $date = $request->date;

            $portfolioHoldingsService = new PortfolioHoldingsService($portfolioId);
            $transactions = $portfolioHoldingsService->getPreviousPortfolioHoldings($date);

            return $this->success_item("Portfolio Holdings For Given Date Found! ", $transactions);
        }
        catch (HttpException $e)
        {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }
}