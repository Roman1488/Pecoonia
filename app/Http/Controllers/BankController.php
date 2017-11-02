<?php

namespace App\Http\Controllers;

use App\Bank;
use App\BankCashFlow;
use App\Transaction;
use App\Currency;
use App\Exceptions\HttpException;
use App\Libraries\Utils;
use App\Portfolio;
use Illuminate\Http\Request;
use Carbon\Carbon;

use App\Http\Requests;

class BankController extends Controller
{
    /**
     * Returns the currently logged-in user banks.
     *
     * @return array
     * @throws
     */
    public function index()
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }
            // Join banks with portfolios to find on banks that belong to the user
            $banks = Bank::query()
                ->join('portfolios', 'portfolios.id', '=', 'banks.portfolio_id')
                ->join('users', 'users.id', '=', 'portfolios.user_id')
                ->selectRaw('banks.*')
                ->where('users.id', $user->id)
                ->get();

            return $this->success_item("Banks found", $banks);
        }
        catch (HttpException $e) {
            dd(get_class($e));
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Returns the currently logged-in user bank with the ID.
     *
     * @param $id
     *
     * @return array
     * @throws
     */
    public function getBank($id)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }
            // Join banks with portfolios to find on banks that belong to the user
            $bank = Bank::query()
                ->join('portfolios', 'portfolios.id', '=', 'banks.portfolio_id')
                ->join('users', 'users.id', '=', 'portfolios.user_id')
                ->selectRaw('banks.*')
                ->where('users.id', $user->id)
                ->where('banks.id', '=', $id)
                ->with(Bank::$export_associations)
                ->first();

            if (!$bank)
                throw Utils::throwError('not_found', 'Bank');

            return $this->success_item("Bank with ID $id found", $bank);
            //return $this->success_item("Bank of user with ID $user->id", $bank);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Update the name of the bank with ID if belongs to the user logged in
     *
     * @param Request $request
     * @param         $id
     *
     * @return mixed
     * @throws
     */
    public function updateBank(Request $request, $id)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }

            if (!$request->has('name'))
                throw Utils::throwError('invalid_value', 'Name');

            $bank = Bank::query()
                ->join('portfolios', 'portfolios.id', '=', 'banks.portfolio_id')
                ->join('users', 'users.id', '=', 'portfolios.user_id')
                ->selectRaw('banks.*')
                ->where('users.id', $user->id)
                ->where('banks.id', '=', $id)
                ->first();

            if (!$bank)
                throw Utils::throwError('not_found', 'Bank');

            $values = array_map('trim', $request->all());

            if (!strlen($values['name']))
                throw Utils::throwError('invalid_value', ['Name']);

            $bank->name             = $values['name'];

            if (isset($values['enable_overdraft']))
            {
                $bank->enable_overdraft = $values['enable_overdraft'];
            }

            if (isset($values['status']))
            {
                $bank->status = ($values['status']) ? 1 : 0;
            }

            $bank->save();

            return $this->success_item("Bank name updated", $bank);
        }
        catch (\HttpException $e) {

        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Delete the bank with the ID if belongs to the user logged in
     *
     * @param $id
     *
     * @return mixed
     * @throws
     */
    public function deleteBank($id)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }

            $bank = Bank::query()
                ->join('portfolios', 'portfolios.id', '=', 'banks.portfolio_id')
                ->join('users', 'users.id', '=', 'portfolios.user_id')
                ->selectRaw('banks.*')
                ->where('users.id', $user->id)
                ->where('banks.id', '=', $id)
                ->first();

            if (!$bank)
                throw Utils::throwError('not_found', 'Bank');

            $bank->delete();
            return $this->success_state("You have deleted the Bank Account Bank with ID {$bank->name} successfully");
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }


    /**
     * Create a new bank with the request fields, return the new bank created
     *
     * @param Request $request
     *
     * @return array
     * @throws
     */
    public function createBank(Request $request)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user)
                throw Utils::throwError('not_logged_in');

            // Must have a name, currency and a flag defining whether or not the bank account should allow overdrafts.
            // MUST also have a portfolio ID (HAS to be related to a portfolio)
            if (!$request->has(['name', 'currency_id', 'enable_overdraft', 'portfolio_id']))
                throw Utils::throwError('invalid_values', 'Name, Currency, Overdraft, or Portfolio');

            if (!Currency::find($request->get('currency_id')))
                throw Utils::throwError('not_found', 'Currency');

            $portfolio = Portfolio::query()
                ->where('id', $request->get('portfolio_id'))
                ->where('user_id', $user->id)
                ->first();

            if (!$portfolio)
                throw Utils::throwError('not_found', 'Portfolio');

            if (!strlen($request->get('name')))
                throw Utils::throwError('invalid_value', 'Bank name');

            $bank = new Bank($request->all());
            $bank->save();

            // Bank added first time
            if ($bank->cash_amount > 0)
            {
                $cf          = new BankCashFlow();
                $cf->bank_id = $bank->id;
                $cf->type    = 'add';
                $cf->amount  = $bank->cash_amount;
                $cf->save();

                // Cash deposit transaction
                $cashTx                   = new Transaction();
                $cashTx->action           = 'deposit';
                $cashTx->trade_value      = $bank->cash_amount;
                $cashTx->bank_id          = $bank->id;
                $cashTx->portfolio_id     = $portfolio->id;
                $cashTx->text             = 'First Deposit';
                $cashTx->date             = Carbon::now();
                $cashTx->transaction_type = 'cash_deposit';
                $cashTx->action           = 'deposit';

                $cashTx->save();
            }

            return $this->success_item("Bank created with ID $bank->id", $bank->complete());

        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Returns an array of banks associated with the portfolio id
     *
     * @param $id
     *
     * @return array
     * @throws
     */
    public function getBankByPortfolio($id)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user)
                throw Utils::throwError('not_logged_in');

            $portfolio = Portfolio::query()
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$portfolio)
                throw Utils::throwError('not_found', 'Portfolio');

            $banks = Bank::query()
                ->where('portfolio_id', $portfolio->id)
                ->where('status', 1)
                ->get();

            if (!$banks)
                throw Utils::throwError('not_found', 'Banks');

            return $this->success_item("Banks found using Portfolio ID $id", $banks);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }
}
