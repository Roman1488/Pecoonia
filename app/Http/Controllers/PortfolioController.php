<?php

namespace App\Http\Controllers;

use App\Currency;
use App\Exceptions\HttpException;
use App\Libraries\Utils;
use App\Portfolio;
use Carbon\Carbon;
use DB;
use App\PortfolioDailyValue;
use App\PortfolioStatistics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\CurrencyDistribution;

use App\Http\Requests;

class PortfolioController extends Controller
{
    /**
     * Returns the currently logged-in user Active portfolios.
     *
     * @return array
     * * @throws
     */
    public function index()
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }

            // Make sure that each portfolio includes a real Currency relation
            $portfolios = Portfolio::query()
                ->where('user_id', $user->id)
                ->where('status', 1)
                ->get();

            return $this->success_item("Portfolios of user with ID $user->id", $portfolios);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Returns the portfolio statistics of current portfolio of logged-in user.
     *
     * @return array
     * * @throws
     */
    public function getStatistics($id)
    {
        try {
            if (!$this->isValidPortfolioUser($id))
            {
                throw Utils::throwError('invalid_value', 'portfolio id');
            }

            $portfolioStatistics = PortfolioStatistics::with('portfolio_stats_currency_distribution')
                                        ->select('portfolio_statistics.*')
                                        ->where('portfolio_id', $id)
                                        ->orderBy('portfolio_statistics.created_at', 'DESC')
                                        ->get()->first();

            return $this->success_item("Portfolio statistics", $portfolioStatistics);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }


    /**
     * Returns the currently logged-in user ALL portfolios.
     *
     * @return array
     * * @throws
     */
    public function getAllPortfolios()
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }

            // Make sure that each portfolio includes a real Currency relation
            $portfolios = Portfolio::query()
                ->where('user_id', $user->id)
                ->get();

            return $this->success_item("Portfolios of user with ID $user->id", $portfolios);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Returns the portfolio with ID if belongs to user logged in.
     *
     * @param  portfolio_id
     *
     * @return array
     * @throws
     */
    public function getPortfolio($id)
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

            return $this->success_item("Portfolio with ID $id found", $portfolio);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Updates portfolio if belongs to user logged in
     *
     * @param Request $request
     * @param         portfolio_id
     *
     * @return array
     * * @throws
     */
    public function updatePortfolio(Request $request, $id)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }
            $portfolio = Portfolio::query()
                ->where('id', $id)
                ->where('user_id', $user->id)
                ->first();

            if (!$portfolio)
                throw Utils::throwError('not_found', 'Portfolio');

            if(!$request->all())
                throw Utils::throwError('invalid_value', 'Nothing to update');

            $portfolio->fill($request->except(['currency_id', 'is_company']));
            $portfolio->save();

            return $this->success_item("Portfolio updated with ID $portfolio->id", $portfolio);

        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Deletes the portfolio with ID if belongs to user logged in and the password is correct.
     *
     * @param  portfolio_id
     *
     * @return array
     * * @throws
     */
    public function deletePortfolio(Request $request, $id)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user)
                throw Utils::throwError('not_logged_in');

            $portfolio = Portfolio::find($id);

            if (!$portfolio)
                throw Utils::throwError('not_found', 'Portfolio');

            if ($portfolio->user_id != $user->id)
                throw Utils::throwError('not_found', 'Portfolio');

            if(!$user->signup_source)
            {
                // Check password

                if (!$request->password)
                    throw Utils::throwError('not_found', 'Password');

                $password = $request->password;

                if (!Hash::check($password, $user->password))
                    throw Utils::throwError('invalid_value', 'Password');
            }

            $portfolio->forceDelete();
            return $this->success_state("Portfolio with ID $id is deleted");

        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
     * Creates a new portfolio.
     *
     * @param Request $request
     *
     * @return array
     * * @throws
     */
    public function createPortfolio(Request $request)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user)
                throw Utils::throwError('not_logged_in');

            if (!$request->has(['name', 'currency_id', 'is_company']))
                throw Utils::throwError('invalid_values', 'Name, Currency or Is Company');

            if (!Currency::find($request->get('currency_id')))
                throw Utils::throwError('not_found', 'Currency');

            // Check if portfolio already exists for the given user
            $userPortfolio = Portfolio::where('name', $request->input('name'))->where('user_id', $user->id)->first();

            if( $userPortfolio )
            {
                throw Utils::throwError('custom', 'Portfolio name already in use');
            }

            $portfolio = new Portfolio($request->all());
            $portfolio->user_id = $user->id;
            $portfolio->save();

            return $this->success_item("Portfolio created with ID $portfolio->id", $portfolio->complete());
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    /**
    *   Gets Daily Values of Portfolio
    *
    *   @param $portfolio_id
    *
    *   @return mixed
    *   @throws \Exception
    */

    public function getDailyValues($portfolio_id, $lastDays = null)
    {
        try {

            if (!$this->isValidPortfolioUser($portfolio_id))
            {
                throw Utils::throwError('invalid_value', 'portfolio id');
            }

            // Give data of current day (from portfolio statistics) + ($lastDays - 1) days

            $portfolioDailyValuesQuery = PortfolioDailyValue::with('daily_currency_distributions')
                                        ->select('portfolio_daily_values.*')
                                        ->where('portfolio_id', $portfolio_id)
                                        ->orderBy('portfolio_daily_values.created_at', 'DESC');

            if ($lastDays)
            {
                $date = Carbon::today()->subWeekdays($lastDays - 1);

                $portfolioDailyValuesQuery->where('portfolio_daily_values.created_at', '>=', $date);
            }
            $portfolioDailyValues = $portfolioDailyValuesQuery->get();

            $portfolioDailyValues->map(function($portDailyValue) {
                if ($portDailyValue->created_at)
                {
                    $portDailyValue->stats_on_date = Carbon::parse($portDailyValue->created_at)->subDay()->toDateTimeString();
                }
                return $portDailyValue;
            });

            // Get current day data from portfolio statistics
            $portfolioDailyValues->prepend(PortfolioStatistics::with('portfolio_stats_currency_distribution')
                                        ->select(DB::raw('portfolio_statistics.*'), DB::raw('"'.Carbon::now()->toDateTimeString().'" as stats_on_date'))
                                        ->where('portfolio_id', $portfolio_id)
                                        ->orderBy('portfolio_statistics.created_at', 'DESC')
                                        ->get()->first());


            return $this->success_item("Portfolio Daily Values", $portfolioDailyValues);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
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
