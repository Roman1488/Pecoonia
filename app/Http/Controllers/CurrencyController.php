<?php

namespace App\Http\Controllers;

use App\Currency;
use App\CurrenciesPair;
use App\Transaction;
use App\Services\PortfolioHoldingsService;
use App\Libraries\Utils;
use App\Exceptions\HttpException;


class CurrencyController extends Controller
{
    /**
     * @return array
     */
    public function allCurrencies()
    {
        try {
            $currencies = Currency::all();
            return $this->success_item("Currencies found", $currencies);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    public function yahooApiCurrenciesPairs($symbol, $portfolioId)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user)
                throw Utils::throwError('not_logged_in');

            $portfolioHoldingsService = new PortfolioHoldingsService($portfolioId);

            $holdingsCurr = $portfolioHoldingsService->getPortfolioHoldings()
                                    ->pluck('security.currency.symbol')
                                    ->transform(function ($item, $key) use($symbol) {
                                        return $item.$symbol;
                                    });

            //Currencies used for securities with inventory above zero.
            $holdingsCurrencyPairs = CurrenciesPair::whereIn('name', $holdingsCurr)->orderBy('name')->get();

            $preDefinedCurr = collect(['USD', 'EUR', 'JPY', 'GBP', 'CHF'])
                                ->transform(function ($item, $key) use($symbol) {
                                    return $item.$symbol;
                                });

            //Currencies used for securities with USD, EUR, JPY, GBP Or CHF.
            $preDefinedCurrencyPairs = CurrenciesPair::whereIn('name', $preDefinedCurr)
                            ->whereNotIn('name', $holdingsCurr)
                            ->get();


            $preDefinedCurrencyPairs = $preDefinedCurr->transform(function ($item, $key)
                                        use ($preDefinedCurrencyPairs) {
                                            return $preDefinedCurrencyPairs->where('name', $item)->first();
                                        })->reject(function ($currPairCollection) {
                                            return empty($currPairCollection);
                                        })->unique()->values();

            //Other rest of all currencies pair.
            $otherCurrencyPairs = CurrenciesPair::where('name', 'like', '%' . $symbol)
                                        ->whereNotIn('name', $preDefinedCurrencyPairs->pluck('name')->all())
                                        ->whereNotIn('name', $holdingsCurrencyPairs->pluck('name')->all())
                                        ->orderBy('name')
                                        ->get();

            $currencies = $holdingsCurrencyPairs->merge($preDefinedCurrencyPairs)->merge($otherCurrencyPairs);

            $currencyNames = Currency::pluck('name', 'symbol');

            $currencyAlias = Currency::get()->pluck('currAlias','symbol');
            /*dd($currencyAlias);*/

            return $this->success_item("Currencies pairs found",
                [
                    'currencies'    => $currencies,
                    'currencyNames' => $currencyNames,
                    'currencyAlias' => $currencyAlias
                ]
            );
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }

    }
}
