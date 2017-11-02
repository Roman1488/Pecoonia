<?php

namespace App\Http\Controllers;

use App\Bank;
use App\BankCashFlow;
use App\Exceptions\HttpException;
use App\Libraries\Utils;
use App\Pecoonia\Calculator\TransactionCalculator;
use App\Portfolio;
use App\PortfolioDailyValue;
use App\CurrenciesPair;
use App\Security;
use App\SecurityData;
use App\Transaction;
use App\TransactionHistory;
use App\TransactionTag;
use App\UserSecurity;
use App\SecuritySplitDividend;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Database\QueryException;
use App\Jobs\SavePortfolioDailyValuesJob;
use DB;

use App\Services\StockSplitService;

class LogicController extends Controller
{

    public function test()
    {
        try {
            return ["hello" => "world"];
        }
        catch (\Exception $e) {

        }
    }

    /**
     * @param Request $request
     *
     * @return array
     * @throws \Exception
     */
    public function buyTransaction(Request $request)
    {
        //dd($request->all());
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }

            if (!$request->has(["portfolio_id", "date", "quantity", "trade_value", "local_currency_rate", "is_commision"]))
                throw Utils::throwError('invalid_values', "portfolio_id, date, quantity, trade_value, local_currency_rate, is_commision");

            if (!$portfolio = Portfolio::where('id', $request->get('portfolio_id'))->where('user_id', $user->id)->first())
                throw Utils::throwError('not_found', "Portfolio");

            //
            if ($request->get('security_id') && (!$security = Security::find($request->get('security_id'))))
            {
                throw Utils::throwError('not_found', "Security");
            }
            elseif (!$request->get('security_id') && !$request->get('security'))
            {
                throw Utils::throwError('not_found', "Security");
            }

            $is_commision_included = $request->get('is_commision');
            if ($is_commision_included > 1 || $is_commision_included < 0)
                throw Utils::throwError('invalid_value', 'Is Commission');

            $is_commision_included = (bool)$request->get('is_commision');

            // In case of comma as decimal separator, change to dot for storing

            $request->merge(['trade_value' => str_replace(',', '.', $request->trade_value)]);
            $request->merge(['commision' => str_replace(',', '.', $request->commision)]);
            $request->merge(['local_currency_rate' => str_replace(',', '.', $request->local_currency_rate)]);

            $postedSecurity = $request->get('security');

            // Save security to database
            if ($postedSecurity)
            {
                $securityModelObj = new Security();
                $security = $securityModelObj->saveSecurityData($postedSecurity['security_data'], $postedSecurity['currency']);
            }

            $transaction = new Transaction($request->except(['tags', 'security']));
            $transaction->inventory = $request->get('quantity');
            $transaction->transaction_type = 'buy';
            $transaction->security_id = $security->id;
            $transaction->book_value = $transaction->trade_value;
            $transaction->local_currency_rate_book_value = $transaction->local_currency_rate;
            $transaction->original_quantity = $transaction->quantity;

            // If bank is not in the request
            if (!$request->has('bank_id') || !$request->get('bank_id')) {
                $bank_ids = Bank::query()
                    ->where('portfolio_id', $request->get('portfolio_id'))
                    ->select(['id'])
                    ->distinct()
                    ->lists('id');

                // If the portfolio only have 1 bank associated
                if (count($bank_ids) == 1) {
                    $bank_id = $bank_ids[0];
                    $transaction->bank_id = $bank_id;
                }
                else if (count($bank_ids) > 1) {
                    // The portfolio contains more than 1 bank_id, so impossible to guess which one to use
                    // In this case, the user MUST supply a bank ID
                    throw Utils::throwError('value_not_found', 'bank_id');
                }
            }
            else {
                $bank_id = $request->get('bank_id');
            }

            $bank = null;

            if (!empty($bank_id)) {
                $bank = Bank::find($bank_id);
            }

            // Allow Buy Transaction even without a Bank
            //if (!$bank)
            //    throw Utils::throwError('not_found', 'Bank');

            $buyTransaction =
                [
                    "trade_value" => $transaction->trade_value,
                    "book_value" => $transaction->trade_value,
                    "commision" => $transaction->commision,
                    "is_commision_included" => ($transaction->is_commision == '1' ? true : false),
                    "is_same_currency" => $this->isSameCurrency($security, $bank ?: $portfolio),
                    "local_currency_rate" => $transaction->local_currency_rate,
                    "local_currency_rate_book_value" => $transaction->local_currency_rate,
                    "quantity" => $transaction->quantity,
                    "use_quantity" => $transaction->quantity,
                ];


            // Lets first create our transaction objects.
            $securityBuyTransaction = new \App\Pecoonia\Calculator\Transaction($buyTransaction, []);

            // Lets put the calculator to work.
            $calculator = new TransactionCalculator($securityBuyTransaction);

            // Check if bank_id is associated with portfolio_id
            if ($bank && $bank->portfolio_id == $portfolio->id) {
                $trade_value = $calculator->TradeValue->getLocal();
                if ($bank->cash_amount < $trade_value && !$bank->enable_overdraft)
                    throw Utils::throwError('custom', 'Overdraft not allowed');

                // If the buy transaction has "is_commision_included" = false, the bank_amount should be adjusted with
                // the "trade_value_base" so that the commision is included in the cash_flow.
                // NOTE: $is_commision == $is_commision_included
                if (!$is_commision_included)
                    $trade_value += $transaction->commision;

                $this->makeTransactionCashWithdraw($bank, $trade_value);
            }

            // Assign values from calculator
            $transaction->c_trade_value_local = $calculator->TradeValue->getLocal();
            $transaction->c_trade_value_base = $calculator->TradeValue->getBase();
            $transaction->c_commision_local = $calculator->Commision->getLocal();
            $transaction->c_commision_base = $calculator->Commision->getBase();
            $transaction->c_trade_quote_local = $calculator->TradeQuote->getLocal();
            $transaction->c_trade_quote_base = $calculator->TradeQuote->getBase();
            $transaction->c_trade_quote_commision_local = $calculator->TradeQuoteCommision->getLocal();
            $transaction->c_trade_quote_commision_base = $calculator->TradeQuoteCommision->getBase();

            // Save transaction
            $transaction->save();

            // Save Transaction Tags
            $tags      = $request->get('tags');
            $savedTags = $this->_saveTags($transaction->id, $tags);

            /*When a client uses the "transaction buy" type, and the purchase is successfull, the security should be
            added to the "user_security" table with the portfolio_id aswell, so the data can be fetched.
             * */

            $us = new UserSecurity();
            $us->security_id = $security->id;
            $us->portfolio_id = $portfolio->id;
            $us->user_id = $user->id;
            $us->save();

            // Update quantity / inventory if any existing stock split data found

            // Query stock split data from database for this security id

            $stockSplits = SecuritySplitDividend::where('security_id', $security->id)
                                                    ->where('type', 'split')
                                                    ->get()
                                                    ->toArray();

            $stockSplitServiceObj = new StockSplitService();
            $stockSplitServiceObj->updateTransactions($stockSplits, $security->id);

            // Update Portfolio Statistics

            $portfolioArr = [
                'id'              => $portfolio->id,
                'user_id'         => $user->id,
                'currency_id'     => $portfolio->currency->id,
                'currency_symbol' => $portfolio->currency->symbol
            ];

            $portfolioStatisticsObj = (new SavePortfolioDailyValuesJob($portfolioArr, TRUE))->handle();

            return $this->success_item("Transaction buy action complete", $transaction);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param Request $request
     *
     * @return mixed
     * @throws \Exception
     */
    public function sellTransaction(Request $request)
    {
        try {

            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }

            // Validate minimum required fields
            if (!$request->has(["portfolio_id", "date", "quantity", "trade_value", "local_currency_rate", "is_commision"]))
                throw Utils::throwError('invalid_values', "portfolio_id, date, quantity, trade_value, local_currency_rate, is_commision");

            if (!$portfolio = Portfolio::where('id', $request->get('portfolio_id'))->where('user_id', $user->id)->first())
                throw Utils::throwError('not_found', "Portfolio");

            $this->validateDate($request->get('date'));

            // In case of comma as decimal separator, change to dot for storing

            $request->merge(['trade_value' => str_replace(',', '.', $request->trade_value)]);
            $request->merge(['commision' => str_replace(',', '.', $request->commision)]);
            $request->merge(['local_currency_rate' => str_replace(',', '.', $request->local_currency_rate)]);

            $portfolio_id = $request->get('portfolio_id');
            $security_id = $request->get('security_id');
            $is_commision_request_value = $request->get('is_commision');

            if ($security_id && !$security = Security::where('id', $security_id)->first())
            {
                throw Utils::throwError('not_found', "Security");
            }
            elseif (!$security_id && !$request->get('security'))
            {
                throw Utils::throwError('not_found', "Security");
            }

            if ($is_commision_request_value < 0 || $is_commision_request_value > 1)
                throw Utils::throwError("invalid_value", "is_commision_included");

            $is_commision_included = (bool)$is_commision_request_value;

            $sell_tx = new Transaction($request->except(['tags', 'security']));

            $sell_tx->book_value = $sell_tx->trade_value;
            $sell_tx->local_currency_rate_book_value = $sell_tx->local_currency_rate;
            $sell_tx->transaction_type = 'sell';
            $sell_tx->security_id = $security->id;

            // A list of all previous "BUY" transactions, of the same security_id and where quantity is larger than 0.
            $query = Transaction::query()
                ->where('transaction_type', 'buy')
                ->where('security_id', $security->id)
                //->where('quantity', '>', 0)
                ->where('inventory', '>', 0)
                ->where('date', '<=', $request->get('date'))
                ->orderBy('created_at', 'ASC');


            // Checking for previous bookvalue transaction.


            $bv_query = Transaction::query()
                        ->where('transaction_type', 'bookvalue')
                        ->where('security_id', $security_id)
                        ->where('date', '<=', $request->get('date'))
                        ->orderBy('created_at', 'DESC')
                        ->first();

            // If bank is not in the request
            $bank = null;
            if (!$request->has('bank_id')) {
                $bank_ids = Bank::query()
                    ->where('portfolio_id', $request->get('portfolio_id'))
                    ->select(['id'])
                    ->distinct()
                    ->lists('id');

                // If the portfolio only have 1 bank associated
                if (count($bank_ids) == 1) {
                    $bank_id = $bank_ids[0];
                    $query->where('bank_id', $bank_ids[0]);
                    $bank = Bank::find($bank_id);
                }
                else {
                    // If no bank is specified, the query should only fetch the previous buy transactions matching the current portfolio_id.
                    $query->where('portfolio_id', $portfolio_id);
                }
            }
            else {
                $bank_id = $request->get('bank_id');
                // Associate the bank_id with the sell transaction
                $sell_tx->bank_id = $bank_id;

                // If a bank_id is specified as query param, then use it to seach for transactions
                $query->where('bank_id', $bank_id);

                $bank = Bank::find($bank_id);
            }

            $transactions = $query->get();


            // TODO: should this be an error?
            if (!$transactions->count())
                throw Utils::throwError('custom', 'Previous buy transactions not found with a quantity larger than 0, with that security_id, or in previous date than the specified.');

            $tx_fifo = [];
            $total_quantity = 0;
            $total_index = -1;

            $amountReached = 0;
            $remainder = 0;
            $transactionPersistList = [];

            foreach( $transactions as $index => &$buy_tx)
            {
                $newResult = ($amountReached + $buy_tx->inventory);

                if( $newResult > $sell_tx->quantity )
                {
                    // Yay. atlast
                    // get remainder.
                    $remainder = $newResult-$sell_tx->quantity;

                    // Set it
                    $amountReached += $buy_tx->inventory-$remainder;
                    $transactionPersistList[] = [ "new_inventory" => $remainder, "usage" => $buy_tx->inventory-$remainder,  "object" => $buy_tx];

                    break;

                } else {
                    // Not yet
                    $amountReached += $buy_tx->inventory;
                    $transactionPersistList[] = [ "new_inventory" => 0, "usage" => $buy_tx->inventory, "object" => $buy_tx];
                    continue;
                }
            }

            if( $amountReached < $sell_tx->quantity)
                throw Utils::throwError('custom', "You don't have the quantity you're selling.");

            /*
            // Create a FIFO list of transactions
            // Also sum all Buy quantities until we reach the Sell quantity
            foreach ($transactions as $index => &$buy_tx) {
                $calc_transaction = new \App\Pecoonia\Calculator\Transaction($buy_tx->toArray());

                // Sum until we reach the Sell quantity
                if ($total_index == -1) {
                    $total_quantity += $buy_tx->inventory;//$buy_tx->quantity;
                    if ($total_quantity >= $sell_tx->quantity) {
                        $total_index = $index;

                        break;
                    }
                }

                // Array of Calculator\Transaction objects
                array_push($tx_fifo, $calc_transaction);
            }

            if ($total_index == -1 || $total_quantity < $sell_tx->quantity)
                throw Utils::throwError('custom', "You don't have the quantity you're selling. Your current quantity is: " . $total_quantity);

            // TODO: Is this calculation ok?
            // Save remainder in last transaction
            $remainder = $total_quantity - $sell_tx->quantity;
            */
            $fifo = [];
            $now = Carbon::now();
            foreach( $transactionPersistList as $persist )
            {
                // Before update, copied to transaction_history
                $last_buy_tx = $persist["object"]['attributes'];
                $th = new TransactionHistory($last_buy_tx);
                $th->transaction_id = $last_buy_tx['id'];
                $th->save();

                $calc_transaction = new \App\Pecoonia\Calculator\Transaction($persist["object"]->toArray());
                $calc_transaction->setUseQuantity( $persist["usage"] );

                $d = Carbon::parse($persist["object"]->date);
                $calc_transaction->setDaysOwned($d->diff($now)->days);

                if( $bv_query )
                    $calc_transaction->setLocalCurrencyRateBookValue( $bv_query->local_currency_rate_book_value );

                $fifo[] = $calc_transaction;

                // Update inventory
                $persist["object"]->inventory = $persist["new_inventory"];
                $persist["object"]->save();




            }

            $sameCurrency = $this->isSameCurrency($security, $bank ?: $portfolio);

            $sellTransaction = [
                "trade_value" => $sell_tx->trade_value,
                "book_value" => $sell_tx->trade_value,
                "commision" => $sell_tx->commision,
                "is_commision_included" => $is_commision_included,
                "is_same_currency" => $sameCurrency,
                "local_currency_rate" => $sell_tx->local_currency_rate,
                "local_currency_rate_book_value" => $sell_tx->local_currency_rate,
                "use_quantity" => $sell_tx->quantity,
                "quantity" => $sell_tx->quantity,
            ];

            // Lets first create our transaction objects.
            $securityBuyTransaction = new \App\Pecoonia\Calculator\Transaction($sellTransaction, []);

            // TODO: Should we use the fifo quey with all BUY transactions, or just the transactions required to reach SELL->quantity?
            //$fifo_queue = array_splice($tx_fifo, 0, $total_index);

            // Create the calculator
            $calculator = new TransactionCalculator($securityBuyTransaction, $fifo);//$fifo_queue);

            if ($bank && $bank->portfolio_id == $portfolio->id) {
                $trade_value = $calculator->TradeValue->getLocal();
                if ($bank->cash_amount < $trade_value && !$bank->enable_overdraft)
                    throw Utils::throwError('custom', 'Overdraft not allowed');

                $this->makeTransactionCashDeposit($bank, $trade_value);
            }

            // Persist sell transaction
            $sell_tx->c_commision_local = $calculator->Commision->getLocal();
            $sell_tx->c_commision_base = $calculator->Commision->getBase();
            $sell_tx->c_trade_value_local = $calculator->TradeValue->getLocal();
            $sell_tx->c_trade_value_base = $calculator->TradeValue->getBase();
            $sell_tx->c_trade_quote_local = $calculator->TradeQuote->getLocal();
            $sell_tx->c_trade_quote_base = $calculator->TradeQuote->getBase();
            $sell_tx->c_trade_quote_commision_local = $calculator->TradeQuoteCommision->getLocal();
            $sell_tx->c_trade_quote_commision_base = $calculator->TradeQuoteCommision->getBase();
            $sell_tx->c_purchase_value_local = $calculator->PurchaseValue->getLocal();
            $sell_tx->c_purchase_value_base = $calculator->PurchaseValue->getBase();

            if( $bv_query )
            {

                $sell_tx->c_book_value_local = ( $bv_query->book_value / $bv_query->quantity ) * $sell_tx->quantity;

                if( $sameCurrency )
                {
                    $sell_tx->c_book_value_base = $sell_tx->c_book_value_local;
                } else {
                    $sell_tx->c_book_value_base = $sell_tx->c_book_value_local * $bv_query->local_currency_rate_book_value;
                }

            } else {
                $sell_tx->c_book_value_local = $calculator->BookValue->getLocal();
                $sell_tx->c_book_value_base = $calculator->BookValue->getBase();
            }

            $sell_tx->c_purchase_price_local = $calculator->PurchasePrice->getLocal();
            $sell_tx->c_purchase_price_base = $calculator->PurchasePrice->getBase();
            $sell_tx->c_book_price_local = $calculator->BookPrice->getLocal();
            $sell_tx->c_book_price_base = $calculator->BookPrice->getBase();
            $sell_tx->c_purchase_currency_rate = $calculator->PurchaseCurrencyRate->getValue();
            $sell_tx->c_book_currency_rate = $calculator->BookCurrencyRate->getValue();
            $sell_tx->c_profit_loss_book_value_local = $calculator->ProfitLossBookValue->getLocal();
            $sell_tx->c_profit_loss_book_value_base = $calculator->ProfitLossBookValue->getBase();
            $sell_tx->c_profit_loss_purchase_value_local = $calculator->ProfitLossPurchaseValue->getLocal();
            $sell_tx->c_profit_loss_purchase_value_base = $calculator->ProfitLossPurchaseValue->getBase();
            $sell_tx->c_profit_loss_purchase_value_lc_account = $calculator->ProfitLossPurchaseValueLCAccount->getValue();
            $sell_tx->c_return_purchase_value_local = $calculator->ReturnPurchaseValue->getLocal();
            $sell_tx->c_return_purchase_value_base = $calculator->ReturnPurchaseValue->getBase();
            $sell_tx->c_return_book_value_local = $calculator->ReturnBookValue->getLocal();
            $sell_tx->c_return_book_value_base = $calculator->ReturnBookValue->getBase();
            $sell_tx->c_currency_profit_loss_book_value = $calculator->CurrencyProfitLossBookValue->getValue();
            $sell_tx->c_currency_profit_loss_purchase_price = $calculator->CurrencyProfitLossPurchasePrice->getValue();
            $sell_tx->c_profit_loss_book_value_excurrency = $calculator->ProfitLossBookValueExcurrency->getValue();
            $sell_tx->c_profit_loss_purchase_price_excurrency = $calculator->ProfitLossPurchasePriceExcurrency->getValue();
            $sell_tx->c_average_purchase_price = $calculator->AveragePurchasePrice->getValue();
            $sell_tx->c_average_profit_loss_purchase_price = $calculator->AverageProfitLossPurchasePrice->getValue();
            $sell_tx->c_average_days_owned = $calculator->AverageDaysOwned->getValue();
            $sell_tx->c_daily_profit_loss_purchase_value_local = $calculator->DailyProfitLossPurchaseValue->getLocal();
            $sell_tx->c_daily_profit_loss_purchase_value_base = $calculator->DailyProfitLossPurchaseValue->getBase();
            $sell_tx->c_daily_return_purchase_value_local = $calculator->DailyReturnPurchaseValue->getLocal();
            $sell_tx->c_daily_return_purchase_value_base = $calculator->DailyReturnPurchaseValue->getBase();
            $sell_tx->c_annualized_return_purchase_value_local = $calculator->AnnualizedReturnPurchaseValue->getLocal();
            $sell_tx->c_annualized_return_purchase_value_base = $calculator->AnnualizedReturnPurchaseValue->getBase();

            $sell_tx->save();

            // Save Transaction Tags
            $tags      = $request->get('tags');
            $savedTags = $this->_saveTags($sell_tx->id, $tags);

            // Update Portfolio Statistics

            $portfolioArr = [
                'id'              => $portfolio->id,
                'user_id'         => $user->id,
                'currency_id'     => $portfolio->currency->id,
                'currency_symbol' => $portfolio->currency->symbol
            ];

            $portfolioStatisticsObj = (new SavePortfolioDailyValuesJob($portfolioArr, TRUE))->handle();

            return $this->success_item("Transaction sell action complete", $sell_tx);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    private function _saveTags($transaction_id, $tags = [])
    {
        $savedTags = false;

        if ($tags)
        {
            $transactionTags = [];
            foreach ($tags as $tag)
            {
                $transactionTags[] = [
                    'transaction_id' => $transaction_id,
                    'tag'            => strtoupper($tag),
                    'created_at'     => Carbon::now()->toDateTimeString(),
                    'updated_at'     => Carbon::now()->toDateTimeString()
                ];
            }

            $savedTags = TransactionTag::insert($transactionTags);
        }

        return $savedTags;
    }

    /**
     * @param Request $request
     *
     * @return mixed
     * @throws \Exception
     */
    public function getTransactions(Request $request)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }

            $page_size = 50;
            $page_index = $request->get('page') ?: 1;

            $query = Transaction::query()
                ->join('portfolios', 'portfolios.id', '=', 'transactions.portfolio_id')
                ->where('portfolios.user_id', $user->id)
                ->select('transactions.*');

            $total_count = $query->count();
            $total_pages = ceil($total_count / $page_size);

            $transactions = $query->skip(max(0, $page_index - 1) * $page_size)->take($page_size)->get();
            $next_page = -1;
            if ($page_index < $total_pages)
                $next_page = $page_index + 1;

            return $this->success_item("Transactions found", $transactions, ['page' => $page_index, 'next_page' => $next_page]);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param $id
     *
     * @return mixed
     * @throws \Exception
     */
    public function getTransaction($id)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user)
                throw Utils::throwError('not_logged_in');

            $transaction = Transaction::query()
                ->join('portfolios', 'portfolios.id', '=', 'transactions.portfolio_id')
                ->where('portfolios.user_id', $user->id)
                ->where('transactions.id', $id)
                ->select('transactions.*')
                ->first();

            if (!$transaction)
                throw Utils::throwError('not_found', 'Transaction');

            return $this->success_item("Transaction found", $transaction);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Displays the transaction with the given ID, and all transaction history, if the
     * transaction belongs to the currently logged in user.
     *
     * @param $id
     *
     * @return mixed
     * @throws \Exception
     */
    public function getTransactionAll($id)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user)
                throw Utils::throwError('not_logged_in');

            $transaction = Transaction::query()
                ->join('portfolios', 'portfolios.id', '=', 'transactions.portfolio_id')
                ->where('portfolios.user_id', $user->id)
                ->where('transactions.id', $id)
                ->select('transactions.*')
                ->first();

            if (!$transaction)
                throw Utils::throwError('not_found', 'Transaction');

            $th = TransactionHistory::query()
                ->where('transaction_id', $transaction->id)
                ->get();

            return $this->success_item("Transaction found", $transaction, ['history' => $th]);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Returns the last 20 transactions belonging to the given portfolio, if the portfolio is owned by the currently
     * logged in user. Paginated so more can be displayed,
     *
     * @param Request $request
     * @param         $id
     *
     * @return mixed
     * @throws \Exception
     */
    public function getPortfolioTransactions(Request $request, $id)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }

            $page_size = 20;
            $page_index = $request->get('page') ?: 1;

            $searchTerms = $request->get('search') ?: "";
            $searchTerms = explode(",", $searchTerms);

            $query = Transaction::query()
                ->join('portfolios', 'portfolios.id', '=', 'transactions.portfolio_id')
                ->leftJoin('banks', 'banks.id', '=', 'transactions.bank_id')
                ->leftJoin('securities', 'securities.id', '=', 'transactions.security_id')
                ->leftJoin('currencies', 'currencies.id', '=', 'securities.currency_id')
                ->leftJoin('transaction_tags', 'transaction_tags.transaction_id', '=', 'transactions.id')
                ->where('portfolios.id', '=', $id)
                ->where('portfolios.user_id', $user->id)
                ->select('transactions.*',
                        'banks.name as bank_name',
                        'securities.name as security_name',
                        'securities.symbol as security_symbol',
                        'securities.exchange as security_exchange',
                        'securities.security_type as security_type',
                        'currencies.symbol as security_currency',
                        DB::raw('GROUP_CONCAT(transaction_tags.tag) as tags')
                );

            foreach ($searchTerms as $searchTerm)
            {
                $query->where(function($q) use ($searchTerm) {
                    $q->where('banks.name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('securities.name', 'like', '%' . $searchTerm . '%')
                        ->orWhere('securities.symbol', 'like', '%' . $searchTerm . '%')
                        ->orWhere('securities.exchange', 'like', '%' . $searchTerm . '%')
                        ->orWhere('securities.security_type', 'like', '%' . $searchTerm . '%')
                        ->orWhere('currencies.symbol', 'like', '%' . $searchTerm . '%')
                        ->orWhere(DB::raw('
                            CASE transactions.transaction_type
                                WHEN "buy" THEN "Security"
                                WHEN "sell" THEN "Security"
                                WHEN "bookvalue" THEN "Book Value"
                            ELSE transactions.transaction_type
                            END
                        '), 'like', '%' . $searchTerm . '%')
                        ->orWhereIn('transactions.id', function($sq) use ($searchTerm) {
                            $sq->select('transaction_tags.transaction_id')
                                 ->from('transaction_tags')
                                 ->where('transaction_tags.tag', 'like', '%' . $searchTerm . '%')
                                 ->groupBy('transaction_tags.transaction_id');
                        });
                });
            }

            $query->groupBy('transactions.id')->orderBy('transactions.date', 'DESC')->orderBy('transactions.id', 'DESC');

            $total_count = $query->get()->count();
            $total_pages = ceil($total_count / $page_size);

            // $page_index = -2 should return all transactions
            $transactions = ($page_index == -2) ? $query->get()->toArray() : $query->skip(max(0, $page_index - 1) * $page_size)->take($page_size)->get()->toArray();

            $next_page = -1;
            if ($page_index < $total_pages)
                $next_page = $page_index + 1;

            // Add additional fields
            $latestSecurityTransaction  = [];
            $latestDividendTransaction  = [];
            $latestCashTransaction      = [];
            $latestBookValueTransaction = [];

            foreach ($transactions as $tky => &$tvl)
            {
                // Add "show Delete Transaction Link?" field
                if (in_array($tvl['transaction_type'], ['buy', 'sell']))
                {
                    $securityId = $tvl['security']['id'];

                    if (!isset($latestSecurityTransaction[$securityId]))
                    {
                        $latestSecurityTransaction[$securityId] = $securityId;
                        $tvl['del_transaction_link'] = true;
                    }

                    // Add Buy/Sell field
                    $tvl['buy_or_sell'] = ucfirst($tvl['transaction_type']);
                }
                elseif ($tvl['transaction_type'] === 'dividend')
                {
                    $securityId = $tvl['security']['id'];

                    if (!isset($latestDividendTransaction[$securityId]))
                    {
                        $latestDividendTransaction[$securityId] = $securityId;
                        $tvl['del_transaction_link'] = true;
                    }
                }
                elseif (in_array($tvl['transaction_type'], ['cash_deposit', 'cash_withdraw']))
                {
                    $bankId = $tvl['bank']['id'];

                    if (!isset($latestCashTransaction[$bankId]))
                    {
                        $latestCashTransaction[$bankId] = $bankId;
                        $tvl['del_transaction_link'] = true;
                    }
                }
                elseif ($tvl['transaction_type'] === 'bookvalue')
                {
                     $securityId = $tvl['security']['id'];

                    if (!isset($latestBookValueTransaction[$securityId]))
                    {
                        $latestBookValueTransaction[$securityId] = $securityId;
                        $tvl['del_transaction_link'] = true;
                    }
                }
            }

            //end

            // Set transaction fields ordering
            $transactionFields  = config('pecoonia.transaction_fields');
            $sortedTransactions = [];
            foreach ($transactions as $tkey => $tvalue)
            {
                $sortedFields = array();
                foreach ($tvalue as $key => $value)
                {
                    $sortOrder = isset($transactionFields[$key]['sort_order']) ? $transactionFields[$key]['sort_order'] : 1000;

                    $sortedFields[$sortOrder][] = [$key => $value];
                }

                ksort($sortedFields);

                $sortedFields         = array_reduce($sortedFields, 'array_merge', array());
                $sortedTransactions[] = array_reduce($sortedFields, 'array_merge', array());
            }

            unset($transactions);

            return $this->success_item("Transactions found", $sortedTransactions,
                ['page'              => $page_index,
                'next_page'          => $next_page,
                'total'              => $total_count,
                'transaction_fields' => config('pecoonia.transaction_fields'),
                'transaction_types'  => config('pecoonia.transaction_types'),
                ]);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Returns the last 50 transactions belonging to the given bank, if the bank is owned
     * by the currently logged in user.
     *
     * @param Request $request
     * @param         $id
     *
     * @return mixed
     * @throws \Exception
     */
    public function getBankTransactions(Request $request, $id)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }

            $page_size = 50;
            $page_index = $request->get('page') ?: 1;

            $query = Transaction::query()
                ->join('portfolios', 'portfolios.id', '=', 'transactions.portfolio_id')
                //->join('banks', 'banks.portfolio_id', '=', 'portfolios.id')
                ->where('transactions.bank_id', '=', $id)
                ->where('portfolios.user_id', $user->id)
                ->select('transactions.*')
                ->orderBy('transactions.created_at', 'desc');

            $total_count = $query->count();
            $total_pages = ceil($total_count / $page_size);

            $transactions = ($page_index == -2) ? $query->get() : $query->skip(max(0, $page_index - 1) * $page_size)->take($page_size)->get();

            $next_page = -1;
            if ($page_index < $total_pages)
                $next_page = $page_index + 1;

            return $this->success_item("Transactions found", $transactions, [
                    'page' => $page_index,
                    'next_page' => $next_page,
                    'total' => $total_count,

            ]);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * @param Request $request
     *
     * @return mixed
     * @throws \Exception
     */
    public function findTransactions(Request $request)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user)
                throw Utils::throwError('not_logged_in');

            $query = Transaction::query()
                ->leftJoin('portfolios', 'portfolios.id', '=', 'transactions.portfolio_id')
                ->leftJoin('securities', 'securities.id', '=', 'transactions.security_id')
                ->where('portfolios.user_id', $user->id);

            $filter_names = [
                'security',
                'currency',
                'date_start',
                'date_end',
                'type',
            ];

            $filter_criteria = $request->all($filter_names);

            if (!$filter_criteria)
                throw Utils::throwError('invalid_values', join(', ', $filter_names));

            $filter_criteria = array_map(function ($item) {
                return trim(mb_strtolower($item));
            }, $filter_criteria);

            $num_criteria_applied = 0;

            $query->where(function ($q) use ($filter_criteria, &$num_criteria_applied, $request) {
                foreach ($filter_criteria as $name => $value) {
                    if (!strlen($value))
                        continue;

                    $num_criteria_applied += 1;

                    switch ($name) {
                        case 'security':
                            $q->orWhere('securities.id', $value)->orWhere('securities.symbol', $value);
                            break;

                        case 'currency':
                            $q->leftJoin('currencies', 'currencies.id', '=', 'securities.currency_id')
                                ->orWhere('currencies.id', $value)->orWhere('currencies.symbol', $value);
                            break;

                        case 'type':
                            $q->orWhere('transactions.transaction_type', $value);
                            break;

                        case 'date_start':
                            if ($request->has('date_end')) {
                                $q->orWhere(function ($q2) use ($value, $filter_criteria) {
                                    $q2->where('transactions.date', '>=', $value)
                                        ->where('transactions.date', '<=', $filter_criteria['date_end']);
                                });
                            }
                            break;

                        case 'date_end':
                            // This value is only used in conjunction with 'date_start'
                            break;

                        default:
                            Log::info("Skipping unknown value: $name = '$value'");
                    }
                }
            });

            if (!$num_criteria_applied)
                throw Utils::throwError('invalid_values', join(', ', $filter_names));

            $transactions = $query->get();
            $message = 'Your criteria found ' . $transactions->count() . ' transactions';

            return $this->success_item($message, $transactions, ['criteria' => $filter_criteria]);
        }
        catch (HttpException $error) {
            return $this->error($error->getMessage(), $error->getCode());
        }
    }

    /**
     * Check whether a security has same currency as portolio or bank
     *
     * @param $security
     * @param $portfolio_or_bank
     *
     * @return bool
     * @throws HttpException
     */
    private function isSameCurrency($security, $portfolio_or_bank)
    {
        $currency = $portfolio_or_bank->currency()->first();
        if (!$currency)
            throw new HttpException("Currency not associated with " . get_class($portfolio_or_bank));

        return $security->currency_id == $currency->id;
    }

    /**
     * @param $bank
     * @param $amount
     */
    public function makeTransactionCashDeposit($bank, $amount)
    {
        $cf = new BankCashFlow();
        $cf->bank_id = $bank->id;
        $cf->type = 'add';
        $cf->amount = $amount;
        $cf->save();

        $bank->cash_amount = $bank->cash_amount + $amount;
        $bank->save();

    }

    /**
     * @param $bank
     * @param $amount
     */
    public function makeTransactionCashWithdraw($bank, $amount)
    {

        $cf = new BankCashFlow();
        $cf->bank_id = $bank->id;
        $cf->type = 'minus';
        $cf->amount = $amount;
        $cf->save();

        $bank->cash_amount = ($bank->cash_amount ?: 0) - $amount;
        $bank->save();

    }

    public function getBookValueAmount(Request $request)
    {
        try {
            if (!$request->has(["portfolio_id","security_id","date"]))
                throw Utils::throwError('invalid_values', "portfolio_id, security_id, date");

            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }

            if (!$portfolio = Portfolio::where('id', $request->get('portfolio_id'))->where('user_id', $user->id)->first())
                throw Utils::throwError('not_found', "Portfolio");

            if (!$security = Security::find($request->get('security_id')))
                throw Utils::throwError('not_found', "Security");


            $transactions = Transaction::query()
                ->where('transaction_type', 'buy')
                ->where('security_id', $security->id)
                ->where('portfolio_id', $portfolio->id)
                //->where('quantity', '>', 0)
                ->where('inventory', '>', 0)
                ->orderBy('created_at', 'DESC')
                ->get();

            $total_quantity_from_buy_tx = 0;

            foreach ($transactions as $transaction) {
                $total_quantity_from_buy_tx += $transaction->inventory;//$buy_tx['quantity'];
            }

            return $this->success_item("Quantity to book value", ["amount" => $total_quantity_from_buy_tx]);

        } catch (HttpException $error) {
            return $this->error($error->getMessage(), $error->getCode());
        }

    }

    /**
     * @param Request $request
     *
     * @return array
     * @throws \Exception
     */
    public function bookValueTransaction(Request $request)
    {
        try {

            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }

            if (!$request->has(["security_id", "portfolio_id", "date", "book_value", "local_currency_rate"]))
                throw Utils::throwError('invalid_values', "portfolio_id, security_id, date, book_value, local_currency_rate");

            if (!$portfolio = Portfolio::where('id', $request->get('portfolio_id'))->where('user_id', $user->id)->first())
                throw Utils::throwError('not_found', "Portfolio");

            // If the portfolio is not a company, then throw a custom error
            if (!$portfolio->is_company)
                throw Utils::throwError('custom', 'Portfolio must be a company for this transaction type');

            if (!$security = Security::find($request->get('security_id')))
                throw Utils::throwError('not_found', "Security");


            // All previous buy transactions of the same security type, where the quantity > 0.
            $transactions = Transaction::query()
                ->where('transaction_type', 'buy')
                ->where('security_id', $security->id)
                ->where('portfolio_id', $portfolio->id)
                //->where('quantity', '>', 0)
                ->where('inventory', '>', 0)
                ->where('date', '<=', $request->get('date'))
                ->orderBy('created_at', 'DESC')
                ->get();


            // TODO: should this be an error?
            if (!$transactions->count())
                throw Utils::throwError('not_found', 'Previous BUY transactions');

            // In case of comma as decimal separator, change to dot for storing

            $request->merge(['book_value' => str_replace(',', '.', $request->book_value)]);
            $request->merge(['local_currency_rate' => str_replace(',', '.', $request->local_currency_rate)]);

            $local_currency_rate = $request->local_currency_rate;

            $total_quantity_from_buy_tx = 0;

            // Persist these transactions to the history table, before manipulating their values.
            foreach ($transactions as $transaction) {
                $buy_tx = $transaction['attributes'];
                $th = new TransactionHistory($buy_tx);
                $th->transaction_id = $buy_tx['id'];
                $th->save();

                // Sum up the total quantity of all the previous buy transactions.
                $total_quantity_from_buy_tx += $buy_tx['inventory'];//$buy_tx['quantity'];
            }

            // Divide the "book_value" with the total quantity of all previous buy transactions.
            $book_value = $request->book_value;

            $calculated_value = $book_value / $total_quantity_from_buy_tx;

            // Now, the book_value of the previous buy transactions should be set to ( transaction quantity * calculated value )
            foreach ($transactions as $transaction) {
                $transaction->book_value = $transaction->inventory * $calculated_value;//$transaction->quantity * $calculated_value;
                $transaction->save();
            }

            $bv_transaction = new Transaction($request->all());
            $bv_transaction->local_currency_rate_book_value = $local_currency_rate;
            $bv_transaction->book_value = $book_value;
            $bv_transaction->quantity = $total_quantity_from_buy_tx;

            $bv_transaction->c_book_value_local = $book_value;
            $bv_transaction->transaction_type = "bookvalue";

            if( !$this->isSameCurrency($security, $portfolio) )
            {
                $bv_transaction->c_book_value_base = $book_value * $local_currency_rate;
            } else {
                $bv_transaction->c_book_value_base = $book_value;
            }

            $bv_transaction->save();

            // Update Portfolio Statistics

            $portfolioArr = [
                'id'              => $portfolio->id,
                'user_id'         => $user->id,
                'currency_id'     => $portfolio->currency->id,
                'currency_symbol' => $portfolio->currency->symbol
            ];

            $portfolioStatisticsObj = (new SavePortfolioDailyValuesJob($portfolioArr, TRUE))->handle();

            return $this->success_item("Transaction book value complete", $bv_transaction);
        }
        catch (HttpException $error) {
            return $this->error($error->getMessage(), $error->getCode());
        }
    }

    /**
     * @param Request $request
     *
     * @return mixed
     * @throws \Exception
     */
    public function dividendTransaction(Request $request)
    {
        try {

            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }

            // Allow Dividend transaction even without bank
            $bank = null;

            if (!$request->has(["security_id", "portfolio_id", "date", "dividend", "is_tax_included", "tax", "local_currency_rate"]))
                throw Utils::throwError('invalid_values', "portfolio_id, security_id, date, dividend, local_currency_rate, is_tax_included, tax");

            if (!$portfolio = Portfolio::where('id', $request->get('portfolio_id'))->where('user_id', $user->id)->first())
                throw Utils::throwError('not_found', "Portfolio");

            if (!$security = Security::find($request->get('security_id')))
                throw Utils::throwError('not_found', "Security");

            // Check that the bank_id belongs to the portfolio
            if ($request->get('bank_id') &&
                !$bank = Bank::where('id', $request->get('bank_id'))->where('portfolio_id', $portfolio->id)->first()
            ) {
                throw Utils::throwError('not_found', "Bank");
            }

            // In case of comma as decimal separator, change to dot for storing

            $request->merge(['dividend' => str_replace(',', '.', $request->dividend)]);
            $request->merge(['tax' => str_replace(',', '.', $request->tax)]);
            $request->merge(['local_currency_rate' => str_replace(',', '.', $request->local_currency_rate)]);

            $is_tax_included = $request->get('is_tax_included');

            if ($is_tax_included > 1 || $is_tax_included < 0)
                throw Utils::throwError('invalid_value', "is_tax_included");

            $dividend = $request->dividend;

            $tax = $request->tax;
            $div_transaction =
                [
                    "date" => $request->get('date'),
                    "dividend" => $dividend,
                    "is_tax_included" => $is_tax_included,
                    "tax" => $tax,
                    "local_currency_rate" => $request->local_currency_rate,
                    "is_same_currency" => $this->isSameCurrency($security, $bank ?: $portfolio),
                ];


            // Lets first create our transaction objects.
            $dividendTransaction = new \App\Pecoonia\Calculator\Transaction($div_transaction, []);

            // Lets put the calculator to work.
            $calculator = new TransactionCalculator($dividendTransaction);


            $d_transaction = new Transaction($request->all());

            $d_transaction->c_dividend_local = $calculator->Dividend->getLocal();
            $d_transaction->c_dividend_base = $calculator->Dividend->getBase();
            $d_transaction->c_net_dividend_local = $calculator->NetDividend->getLocal();
            $d_transaction->c_net_dividend_base = $calculator->NetDividend->getBase();
            $d_transaction->c_tax_local = $calculator->Tax->getLocal();
            $d_transaction->c_tax_base = $calculator->Tax->getBase();

            $d_transaction->transaction_type = "dividend";

            // If tax is included, just add the dividend to the bank_amount
            // If tax is not included, the tax should be substracted from the dividend, and the bank_amount should have this value added.
            if (!$is_tax_included) {
                $dividend = $dividend - $tax;
            }

            if ($bank)
            {
                $this->makeTransactionCashDeposit($bank, $dividend);
            }

            $d_transaction->dividend = $dividend;
            $d_transaction->save();

            // Update Portfolio Statistics

            $portfolioArr = [
                'id'              => $portfolio->id,
                'user_id'         => $user->id,
                'currency_id'     => $portfolio->currency->id,
                'currency_symbol' => $portfolio->currency->symbol
            ];

            $portfolioStatisticsObj = (new SavePortfolioDailyValuesJob($portfolioArr, TRUE))->handle();

            return $this->success_item("Transaction dividend complete", $d_transaction);
        }
        catch (HttpException $error) {
            return $this->error($error->getMessage(), $error->getCode());
        }
    }

    /**
     * @param Request $request
     *
     * @return array
     * @throws \Exception
     */
    public function cashTransaction(Request $request)
    {
        try {

            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }

            if (!$request->has(['portfolio_id', 'bank_id', 'amount', 'date', 'action']))
                throw Utils::throwError('invalid_values', 'portfolio_id', 'bank_id, amount, date, action, text');

            if (!$portfolio = Portfolio::where('id', $request->get('portfolio_id'))->where('user_id', $user->id)->first())
                throw Utils::throwError('not_found', "Portfolio");

            if (!$bank = Bank::find($request->get('bank_id')))
                throw Utils::throwError('not_found', "Bank");

            // In case of comma as decimal separator, change to dot for storing

            $request->merge(['amount' => str_replace(',', '.', $request->amount)]);

            $action = trim(mb_strtolower($request->get('action')));
            $amount = $request->amount;

            $cash_tx = new Transaction($request->except('amount'));
            $cash_tx->trade_value = $amount;

            if ($action == 'withdraw')
                $this->makeTransactionCashWithdraw($bank, $amount);
            elseif ($action == 'deposit')
                $this->makeTransactionCashDeposit($bank, $amount);
            else
                throw Utils::throwError('invalid_value', 'Action');

            $cash_tx->portfolio_id = $request->get('portfolio_id');
            $cash_tx->transaction_type = 'cash_' . $action;
            $cash_tx->action = $action;
            $cash_tx->save();

            // Update Portfolio Statistics

            $portfolioArr = [
                'id'              => $portfolio->id,
                'user_id'         => $user->id,
                'currency_id'     => $portfolio->currency->id,
                'currency_symbol' => $portfolio->currency->symbol
            ];

            $portfolioStatisticsObj = (new SavePortfolioDailyValuesJob($portfolioArr, TRUE))->handle();

            return $this->success_item("Transaction cash $action complete", $cash_tx);
        }
        catch (HttpException $error) {
            return $this->error($error->getMessage(), $error->getCode());
        }
    }

    /**
     * @param $date
     *
     * @return bool
     * @throws \Exception
     */
    function validateDate($date)
    {
        try {
            $d = Carbon::parse($date);
            return true;
        }
        catch (\Exception $error) {
            throw Utils::throwError('invalid_value', 'Date');
        }
    }

    /**
     * Returns all the tags belonging to the given portfolio, if the portfolio is owned by the currently
     * logged in user.
     *
     * @param Request $request
     * @param         $id
     *
     * @return mixed
     * @throws \Exception
     */

    public function getPortfolioTags(Request $request, $id)
    {
        try {
            if (!$this->isValidPortfolioUser($id))
            {
                throw Utils::throwError('invalid_value', 'portfolio id');
            }

            $tags = TransactionTag::select(['tag'])
                        ->join('transactions', 'transaction_tags.transaction_id', '=', 'transactions.id')
                        ->where('transactions.portfolio_id', '=', $id)
                        ->distinct()
                        ->lists('tag');

            return $this->success_item("Tags found", $tags);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Returns all the tags belonging to the current user
     *
     * @return mixed
     * @throws \Exception
     */

    public function getAllTags()
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }

            $tags = TransactionTag::query()
                        ->join('transactions', 'transaction_tags.transaction_id', '=', 'transactions.id')
                        ->join('portfolios', 'portfolios.id', '=', 'transactions.portfolio_id')
                        ->where('portfolios.user_id', '=', $user->id)
                        ->distinct()
                        ->pluck('transaction_tags.tag');

            return $this->success_item("Tags found", $tags);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Delete the tag belonging to the current user
     *
     * @param $id
     *
     * @return mixed
     * @throws \Exception
     */

    public function deleteTag($tag)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }

            $deleteTags = TransactionTag::query()
                                ->join('transactions', 'transaction_tags.transaction_id', '=', 'transactions.id')
                                ->join('portfolios', 'portfolios.id', '=', 'transactions.portfolio_id')
                                ->where('portfolios.user_id', '=', $user->id)
                                ->where('tag', $tag)
                                ->delete();

            return $this->success_state("Tag deleted successfully.");
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Delete the transaction
     *
     * @param $id
     *
     * @return mixed
     * @throws \Exception
     */
    public function deleteTransaction($id)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }

            /**
            Make sure the transaction is a valid transaction, and indeed belongs to the currently
            logged in user.
            Make sure the transaction id is not compromised - check conditions of deletable transaction
            Delete the transaction after:
            - Adjusting Security Inventory for Sell delete. No need to adjust inventory for Buy delete.
            - Adjusting Bank balance.
            - Adjusting book_value field of Buy transactions for Book Value delete.
            **/

            $transaction = Transaction::find($id);

            if (!$transaction || ($transaction->portfolio->user->id != $user->id))
                throw Utils::throwError('not_found', 'Transaction');

            $portfolio = $transaction->portfolio;

            $transactionType = $transaction->transaction_type;

            if (in_array($transactionType, ['cash_deposit', 'cash_withdraw']))
            {
                $checkField = 'bank_id';
            }
            else
            {
                $checkField = 'security_id';
            }

            $latestTransactionCheck = Transaction::select('id')
                                        ->where('portfolio_id', $transaction->portfolio_id)
                                        ->where('transaction_type', $transactionType)
                                        ->where($checkField, $transaction->$checkField)
                                        ->where('date', '>', $transaction->date)
                                        ->first();

            if ($latestTransactionCheck)
            {
                // The received transaction id is not the latest transaction.
                throw Utils::throwError('custom', 'This transaction can not be deleted.');
            }

            // Undo the process that was done for adjusting inventory while adding Sell trans.
            if (!$this->_adjustSecurityInvOnSellDelete($transaction))
            {
                throw Utils::throwError('custom', 'The security inventory could not be adjusted.');
            }

            // Adjust bank balance

            if (!$this->_adjustBankBalanceOnDelete($transaction))
            {
                throw Utils::throwError('custom', 'The bank balance could not be adjusted.');
            }

            // Adjust book_value
            if (!$this->_adjustBuyTransOnBookValueDelete($transaction))
            {
                throw Utils::throwError('custom', 'The book value could not be adjusted.');
            }

            // Adjust related Daily Values records
            $this->_adjustDailyValuesOnDelete($transaction);

            // Finally, delete the transaction
            $transaction->delete();

            // Update Portfolio Statistics

            $portfolioArr = [
                'id'              => $portfolio->id,
                'user_id'         => $user->id,
                'currency_id'     => $portfolio->currency->id,
                'currency_symbol' => $portfolio->currency->symbol
            ];

            $portfolioStatisticsObj = (new SavePortfolioDailyValuesJob($portfolioArr, TRUE))->handle();

            return $this->success_state("Transaction deleted successfully.");
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
    *   Adjust the inventory of security on Sell add/delete transaction
    */

    private function _adjustSecurityInvOnSellDelete($transaction)
    {
        if ($transaction->transaction_type != 'sell')
        {
            return true;
        }

        // Add inventory to the previous BUY transactions, starting with oldest ones, setting max inventory for each equal to their quantity.

        $qtyToAdjust = $transaction->quantity;

        if ($qtyToAdjust <= 0)
        {
            return false;
        }

        $query = Transaction::query()
                    ->where('transaction_type', 'buy')
                    ->where('security_id', $transaction->security_id)
                    ->where('portfolio_id', $transaction->portfolio_id)
                    ->where('date', '<=', $transaction->date)
                    ->orderBy('created_at', 'ASC');

        if ($transaction->bank_id)
        {
            $query->where('bank_id', $transaction->bank_id);
        }

        $prevBuyTransactions = $query->get();

        if ($prevBuyTransactions->count())
        {
            foreach ($prevBuyTransactions as &$buyTx)
            {
                if ($qtyToAdjust > 0)
                {
                    $adjustInv = ($buyTx->quantity > $qtyToAdjust) ? $qtyToAdjust : $buyTx->quantity;

                    $buyTx->inventory += $adjustInv;
                    $buyTx->save();

                    $qtyToAdjust -= $adjustInv;
                }
                else
                {
                    break;
                }
            }
        }

        return ($qtyToAdjust == 0);
    }

    /**
    *   Adjust Bank Balance
    */

    private function _adjustBankBalanceOnDelete($transaction)
    {
        if ($transaction->transaction_type == 'bookvalue')
        {
            return true;
        }

        if ($transaction->bank && ($transaction->bank->portfolio_id == $transaction->portfolio_id))
        {
            // If Buy / Cash Withdraw Transaction is being deleted, deposit the amount
            // If Sell / Dividend / Cash Deposit Transaction is being deleted, withdraw (deduct) the amount

            $adjustAmount = abs($this->_getBalanceAdjustmentAmount($transaction));

            if ($transaction->transaction_type == 'cash_withdraw')
            {
                $this->makeTransactionCashDeposit($transaction->bank, $adjustAmount);
            }
            else if ($transaction->transaction_type == 'buy')
            {
                $this->makeTransactionCashDeposit($transaction->bank, $adjustAmount);
            }
            else if (in_array($transaction->transaction_type, ['sell', 'dividend', 'cash_deposit']))
            {
                $this->makeTransactionCashWithdraw($transaction->bank, $adjustAmount);
            }
        }

        return true;
    }

    private function _getBalanceAdjustmentAmount($transaction)
    {
        $adjustAmount = 0;

        switch ($transaction->transaction_type)
        {
            case 'buy':
            case 'sell':
                $amountField = 'c_trade_value_local';
                break;
            case 'cash_withdraw':
            case 'cash_deposit':
                $amountField = 'trade_value';
                break;
            case 'dividend':
                $amountField = 'dividend';
                break;
            default:
                break;
        }

        if ($transaction->transaction_type == 'buy')
        {
            $adjustAmount = ($transaction->is_commision != '1') ? ($transaction->$amountField + $transaction->commision) : $transaction->$amountField;
        }

        if (in_array($transaction->transaction_type, ['sell', 'dividend', 'cash_deposit', 'cash_withdraw']))
        {
            $adjustAmount = $transaction->$amountField;
        }

        if (in_array($transaction->transaction_type, ['sell', 'dividend', 'cash_deposit']))
        {
            $adjustAmount = -1 * abs($adjustAmount);
        }

        return $adjustAmount;
    }

    /*
    *   Adjust Book Value amount of Buy Transactions on Book Value Delete
    */

    private function _adjustBuyTransOnBookValueDelete($transaction)
    {
        if ($transaction->transaction_type != 'bookvalue')
        {
            return true;
        }

        /** First logic, just kept for future reference
        // Get latest TransactionHistory record for each Buy transaction of the security and portfolio related to this Book Value transaction, BEFORE this Book Value was added.
        // And set book_value field of these transactions from their respective history record.

        $historyTransactions = DB::table('transaction_history AS th1')
                                    ->leftJoin('transaction_history AS th2', function($join) {
                                        $join->on('th1.transaction_id', '=', 'th2.transaction_id');
                                        $join->on('th1.id', '<', 'th2.id');
                                    })
                                    ->join('transactions AS t', 't.id', '=', 'th1.transaction_id')
                                    ->whereNull('th2.id')
                                    ->where('t.transaction_type', 'buy')
                                    ->where('t.security_id', $transaction->security_id)
                                    ->where('t.portfolio_id', $transaction->portfolio_id)
                                    ->where('t.date', '<=', $transaction->date)
                                    ->where('th1.date', '<=', $transaction->date)
                                    ->pluck('th1.book_value', 'th1.transaction_id');

        if ($historyTransactions)
        {
            foreach ($historyTransactions as $hKey => $hValue)
            {
                Transaction::where('id', $hKey)->update(['book_value' => $hValue]);
            }
        }
        **/

        // Get the second latest Book Value transaction, and use it's value to calculate and update book_value of all related buy transactions with 'date' <= that of Book Value transaction being deleted.
        // If no such book value transaction found, set book_value = trade_value for all those buy transactions

        $transactions = Transaction::query()
                                    ->where('transaction_type', 'buy')
                                    ->where('security_id', $transaction->security_id)
                                    ->where('portfolio_id', $transaction->portfolio_id)
                                    ->where('inventory', '>', 0)
                                    ->where('date', '<=', $transaction->date)
                                    ->get();

        if (!$transactions) // No previous Buy transactions found
        {
            return true;
        }

        $totalInventory = 0;

        foreach ($transactions as $tKey => $tVal)
        {
            $totalInventory += $tVal->inventory;
        }

        if ($totalInventory <= 0)
        {
            return true;
        }

        $bookValueResult = Transaction::select('book_value')
                                    ->where('transaction_type', 'bookvalue')
                                    ->where('created_at', '<', $transaction->created_at)
                                    ->where('security_id', $transaction->security_id)
                                    ->where('portfolio_id', $transaction->portfolio_id)
                                    ->orderBy('created_at', 'DESC')
                                    ->first();

        if ($bookValueResult && $bookValueResult->book_value)
        {
            $calculatedValue = $bookValueResult->book_value / $totalInventory;

            foreach ($transactions as $prevTransaction)
            {
                $prevTransaction->book_value = $prevTransaction->inventory * $calculatedValue;
                $prevTransaction->save();
            }
        }
        else
        {
            foreach ($transactions as $prevTransaction)
            {
                $prevTransaction->book_value = $prevTransaction->trade_value;
                $prevTransaction->save();
            }
        }

        return true;
    }

    /*
    *   Adjust daily values records
    */

    private function _adjustDailyValuesOnDelete($transaction)
    {
        // Deduct from market value base and bank balance values in Daily Value records since the transaction's "date"
        // Reverse the logic applied in save portfolio daily values job.

        $dailyValues = PortfolioDailyValue::where('created_at', '>', $transaction->date)
                                        ->where('portfolio_id', $transaction->portfolio_id)
                                        ->get();

        if ($dailyValues)
        {
            $transactionMarketValInBase = 0;
            $balanceAdjustAmount = 0;

            if ($transaction->transaction_type == 'buy')
            {
                $transInventory = (int) $transaction->inventory;

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
                $transactionMarketValInBase = (($transInventory * $transactionPrice) * $currentCurrencyRate);
            }

            if (($transaction->transaction_type != 'bookvalue') &&
                $transaction->bank && ($transaction->bank->portfolio_id == $transaction->portfolio_id)
            )
            {
                $balanceAdjustAmount = $this->_getBalanceAdjustmentAmount($transaction);
            }

            foreach ($dailyValues as $dvkey => $dailyVal)
            {
                $dailyVal->total_marketvalue_base -= $transactionMarketValInBase;
                $dailyVal->total_all_bank_balance += $balanceAdjustAmount;
                $dailyVal->save();
            }
        }
    }

    /**
    * Checks whether the portfolio belongs to currently logged in user
    *
    * @param $portfolio_id
    *
    * @return boolean
    */

    public function isValidPortfolioUser($portfolio_id)
    {
        $user = app('Dingo\Api\Auth\Auth')->user();

        if (!$user) {
            throw Utils::throwError('not_logged_in');
        }

        return Portfolio::where('id', $portfolio_id)
                  ->where('user_id', $user->id)
                  ->first();
    }

}
