<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Exceptions\HttpException;
use App\Libraries\Utils;
use Carbon\Carbon;
use App\Http\Requests;
use DB;

use App\PortfolioGuidelines;
use App\Portfolio;
use App\Currency;
use App\Security;
use App\GuidelineAttributes;
use App\Bank;
use App\Transaction;
use App\CurrenciesPair;
use App\Services\PortfolioHoldingsService;

use App\Helpers\DecimalFormatter;

class PortfolioGuidelineController extends Controller
{
    protected $totalPortfolioValue;
    public $currentPortfolioObj;

    // GET /portfolio_guidelines/portfolio_id
    // Retrieves only Active portfolio guidelines from the current user logged in
    public function index($portfolio_id)
    {
        try
        {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }

            if (!$this->currentPortfolioObj = Portfolio::where('id', $portfolio_id)->where('user_id', $user->id)->first())
                throw Utils::throwError('not_found', "Portfolio");

            $portfolioGuidelines = PortfolioGuidelines::where('portfolio_id', $portfolio_id)->get();

            if (!$portfolioGuidelines)
                throw Utils::throwError('not_found', 'portfolio_guidelines');

            $resultSet = [];
            $idsArr    = [];

            foreach ($portfolioGuidelines as $guideline)
            {
                $guidelineAttributes = $guideline->guideline_attributes;

                $gAttrArray = $this->_getGuidelineAttrArray($guidelineAttributes->toArray());

                $resultArr = $this->_calculateAndStoreGuideline($guideline->guideline, $guideline->min, $guideline->max, $gAttrArray);

                if (isset($resultArr['portfolioGuidelines']['id']))
                {
                    if (!in_array($resultArr['portfolioGuidelines']['id'], $idsArr))
                    {
                        $resultSet[] = $resultArr['portfolioGuidelines'];
                    }
                    $idsArr[] = $resultArr['portfolioGuidelines']['id'];
                }
                else
                {
                    foreach ($resultArr['portfolioGuidelines'] as $rsv)
                    {
                        if (!in_array($rsv['id'], $idsArr))
                        {
                            $resultSet[] = $rsv;
                        }
                        $idsArr[] = $rsv['id'];
                    }
                }
            }

            return $this->success_item("Saved guidelines are re-calculated & fetched successfully", $resultSet);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    // GET /portfolio/portfolio_id/currencies
    // Retrives currencies from the portfolio with ID from the current user logged in
    public function getCurrencies($portfolio_id)
    {
        try
        {
            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user)
                throw Utils::throwError('not_logged_in');

            if (!$portfolio = Portfolio::where('id', $portfolio_id)->where('user_id', $user->id)->first())
                throw Utils::throwError('not_found', "Portfolio");

            $currencies = Currency::query()
                ->select(DB::raw('DISTINCT(currencies.symbol) AS attrName'), DB::raw('"currency" AS attribute'), DB::raw('currencies.name AS subAttrName'))
                ->join('securities', 'securities.currency_id', '=', 'currencies.id')
                ->join('transactions','transactions.security_id', '=', 'securities.id')
                ->where('transactions.inventory', '>', 0)
                ->where('transactions.portfolio_id', $portfolio_id)
                ->get()
                ->toArray();

            return $this->success_item("currencies found", $currencies);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    // GET /portfolio/portfolio_id/security_types
    // Retrives security_types from the portfolio with ID from the current user logged in
    public function getSecurityTypes($portfolio_id)
    {
        try
        {
            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user)
                throw Utils::throwError('not_logged_in');

            if (!$portfolio = Portfolio::where('id', $portfolio_id)->where('user_id', $user->id)->first())
                throw Utils::throwError('not_found', "Portfolio");

            $securityTypes = Security::query()
                ->select(DB::raw('DISTINCT(securities.security_type) AS attrName'), DB::raw('"security_type" AS attribute'))
                ->join('transactions', 'transactions.security_id', '=', 'securities.id')
                ->where('transactions.inventory', '>', 0)
                ->where('transactions.portfolio_id', $portfolio_id)
                ->get()
                ->toArray();

            foreach ($securityTypes as $key => $securityType)
            {
                unset($securityTypes[$key]['currency']);
                unset($securityTypes[$key]['data']);
            }

            return $this->success_item("security types found", $securityTypes);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    private function _getVarianceWarningMsg($guideline, $currentValue, $variance, $attrVal)
    {
        if ($variance == 0)
        {
            return "";
        }

        $varianceVal = abs($variance);
        $currentValRounded = $currentValue;

        if ($guideline != 'securities_in_portfolio')
        {
            $varianceVal = DecimalFormatter::format($this->currentPortfolioObj->comma_separator, abs($variance));
            $currentValRounded = DecimalFormatter::format($this->currentPortfolioObj->comma_separator, $currentValue);
        }

        switch ($guideline)
        {
            case 'cash_allocation':
                $message = "Cash allocation is ".$currentValRounded."% of Total Portfolio Value which is ". $varianceVal ." pct. points ";
                $message .=  ($variance > 0) ? "more" : "less";
                $message .= " than the set guideline.";
                break;

            case 'securities_in_portfolio':
                $message = "Number of securities in portfolio is ".$currentValRounded." which is ".$varianceVal." ";
                $message .=  ($variance > 0) ? "more" : "less";
                $message .= " than the set guideline.";
                break;

            case 'risc_per_position':
                $message = "Loss for position is ".abs($currentValRounded)."% of Total Portfolio Value which is ".$varianceVal." pct. points ";
                $message .=  ($variance > 0) ? "more" : "less";
                $message .= " than the set guideline.";
                break;

            case 'currency_allocation':
                $message = $attrVal . " positions are ".$currentValRounded."% of Total Portfolio Value which is ".$varianceVal." pct. points ";
                $message .=  ($variance > 0) ? "more" : "less";
                $message .= " than the set guideline.";
                break;

            case 'security_type_allocation':
                $message = $attrVal . " positions are ".$currentValRounded."% of Total Portfolio Value which is ".$varianceVal." pct. points ";
                $message .=  ($variance > 0) ? "more" : "less";
                $message .= " than the set guideline.";
                break;

            case 'tag_allocation':
                $message = $attrVal . " positions are ".$currentValRounded."% of Total Portfolio Value which is ".$varianceVal." pct. points ";
                $message .=  ($variance > 0) ? "more" : "less";
                $message .= " than the set guideline.";
                break;

            case 'weight_per_position':
                $message = "Weight of position is ".$currentValRounded."% of Total Portfolio Value which is ".$varianceVal." pct. points ";
                $message .=  ($variance > 0) ? "more" : "less";
                $message .= " than the set guideline.";
                break;
        }
        return $message;
    }


    private function _calculateAndStoreGuideline($guideline, $min, $max, $gAttrArray)
    {
        $portfolio_id = $this->currentPortfolioObj->id;

        //Calcualte totalPortfolioValue For All Future Calculations

        $portfolioHoldingsService = \App::make(PortfolioHoldingsService::class, ['portfolio_id' => $this->currentPortfolioObj->id]);

        $this->totalPortfolioValue = $portfolioHoldingsService->getTotalPortfolioValue();

        // Calculate Current Value, and Variance
        switch ($guideline)
        {
            case 'cash_allocation':
                $calculationResult = $this->cashAllocation($min, $max);
                break;

            case 'securities_in_portfolio':
                $calculationResult = $this->securitiesInPortfolio($min, $max);
                break;

            case 'risc_per_position':
                $calculationResult = $this->riscPerPosition($min, $max);
                break;

            case 'currency_allocation':
                if (empty($gAttrArray['currencyFilter']))
                {
                    throw Utils::throwError('custom', "Invalid Params, please provide currency.");
                }
                $calculationResult = $this->currencyAllocation($min, $max, $gAttrArray['currencyFilter']);
                break;

            case 'security_type_allocation':
                if (empty($gAttrArray['securityTypeFilter']))
                {
                    throw Utils::throwError('custom', "Invalid Params, please provide security type.");
                }
                $calculationResult = $this->securityTypeAllocation($min, $max, $gAttrArray['securityTypeFilter']);
                break;

            case 'tag_allocation':
                if (empty($gAttrArray['tagFilter']))
                {
                    throw Utils::throwError('custom', "Invalid Params, please provide tag.");
                }
                $calculationResult = $this->tagAllocation($min, $max, $gAttrArray['tagFilter']);
                break;

            case 'weight_per_position':
                $calculationResult = $this->weightPerPosition($min, $max, $gAttrArray);
                break;
        }

        //Delete existing records for this guideline of current portfolio & existing guideline attributes.
        /*
        $deleteStatus = PortfolioGuidelines::where('portfolio_id', $portfolio_id)
                            ->where('guideline', $guideline)
                            ->delete();
        */

        // For security positions specific guidelines

        if (in_array($guideline, ['risc_per_position', 'weight_per_position']))
        {
            $rPPCalcHolder = [];

            //Set warning message for each portfolio guideline.
            foreach ($calculationResult as $guidelineCalculation)
            {
                $guidelineCalcHolder = [];

                //Save Calculated Guideline
                $guidelineCalcHolder = $this->_saveCalculatedGuideline($guideline, $min, $max, $guidelineCalculation['current_value'], $guidelineCalculation['variance'], $gAttrArray, [$guidelineCalculation['attribute']]);

                $rPPCalcHolder[] = $guidelineCalcHolder;
            }

            return ['message' => "Successfully Added To Portfolio Guidelines.", 'portfolioGuidelines' => $rPPCalcHolder];
        }
        else
        {
            $currentValue = $calculationResult['current_value'];
            $variance     = $calculationResult['variance'];

            //Save Calculated Guideline
            $portfolioGuidelines = $this->_saveCalculatedGuideline($guideline, $min, $max, $currentValue, $variance, $gAttrArray);

            return ['message' => 'Successfully Added To Portfolio Guidelines.', 'portfolioGuidelines' => $portfolioGuidelines];
        }
    }


    private function _saveCalculatedGuideline($guideline, $min, $max, $currentValue, $variance, $gAttrArray = [], $securitySymbolFilter = [])
    {
        $portfolio_id = $this->currentPortfolioObj->id;

        // if guideline is not attribute specific, update existing or save new
        // else, if guideline-attribute pair exists, update it, or save new

        if (in_array($guideline, ['cash_allocation', 'securities_in_portfolio']))
        {
            $portfolioGuidelinesId = $this->_saveGuidelineAndAttr($guideline, $min, $max, $currentValue, $variance, null, null);
        }

        //Save attributes of currencyFilter
        foreach ($gAttrArray['currencyFilter'] as $currencyAttribute)
        {
            $portfolioGuidelinesId = $this->_saveGuidelineAndAttr($guideline, $min, $max, $currentValue, $variance, 'currency', $currencyAttribute);
        }

        //Save attributes of securityTypeFilter
        foreach ($gAttrArray['securityTypeFilter'] as $securityTypeAttribute)
        {
            $portfolioGuidelinesId = $this->_saveGuidelineAndAttr($guideline, $min, $max, $currentValue, $variance, 'security_type', $securityTypeAttribute);
        }

        //Save attributes of tagFilter
        foreach ($gAttrArray['tagFilter'] as $tagAttribute)
        {
            $portfolioGuidelinesId = $this->_saveGuidelineAndAttr($guideline, $min, $max, $currentValue, $variance, 'tag', $tagAttribute);
        }

        //Save attributes of securitySymbolFilter
        foreach ($securitySymbolFilter as $securitySymbolAttribute)
        {
            $portfolioGuidelinesId = $this->_saveGuidelineAndAttr($guideline, $min, $max, $currentValue, $variance, 'security_symbol', $securitySymbolAttribute);
        }

        return PortfolioGuidelines::findOrFail($portfolioGuidelinesId)->toArray();
    }

    private function _saveGuidelineAndAttr($guideline, $min, $max, $currentValue, $variance, $attrType, $attrVal)
    {
        $portfolio_id = $this->currentPortfolioObj->id;

        $guidelineAttributes = null;

        $portfolioGuidelines = PortfolioGuidelines::where('guideline', $guideline)
                                        ->where('portfolio_id', $portfolio_id)
                                        ->first();

        if (!$portfolioGuidelines)
        {
            $portfolioGuidelines = new PortfolioGuidelines();
            $guidelineAttributes = new GuidelineAttributes();
        }
        else
        {
            $guidelineAttributes = GuidelineAttributes::join('portfolio_guidelines', 'portfolio_guidelines.id', '=', 'guideline_attributes.guideline_id')
                                    ->where('guideline_attributes.portfolio_id', $portfolio_id)
                                    ->where('portfolio_guidelines.guideline', $guideline)
                                    ->where('attribute', $attrVal)
                                    ->where('attribute_type', $attrType)
                                    ->first();

            if ($guidelineAttributes)
            {
                $portfolioGuidelines = PortfolioGuidelines::find($guidelineAttributes->guideline_id);
            }
            else
            {
                // for security positions specific guidelines, use common guideline record.
                // for others, new guideline record.

                if (!in_array($guideline, ['risc_per_position', 'weight_per_position']))
                {
                    $portfolioGuidelines = new PortfolioGuidelines();
                }

                $guidelineAttributes = new GuidelineAttributes();
            }
        }

        $portfolioGuidelines->portfolio_id  = $portfolio_id;
        $portfolioGuidelines->guideline     = $guideline;
        $portfolioGuidelines->min           = $min;
        $portfolioGuidelines->max           = $max;
        $portfolioGuidelines->save();

        $guidelineAttributes->portfolio_id   = $portfolio_id;
        $guidelineAttributes->guideline_id   = $portfolioGuidelines->id;
        $guidelineAttributes->attribute      = $attrVal;
        $guidelineAttributes->attribute_type = $attrType;
        $guidelineAttributes->current_value  = $currentValue;
        $guidelineAttributes->variance       = $variance;
        $guidelineAttributes->warning_msg    = $this->_getVarianceWarningMsg($guideline, $currentValue, $variance, $attrVal);
        $guidelineAttributes->save();

        return $portfolioGuidelines->id;
    }


    // POST /portfolio/{portfolio_id}/add_guideline
    // Add Guidelines for the given portfolio id of the current user logged in.
    public function addGuideline(Request $request, $portfolio_id)
    {
        try
        {
            if (!$request->isMethod('post'))
                throw Utils::throwError('custom', "Invalid Request");

            $user = app('Dingo\Api\Auth\Auth')->user();

            if (!$user)
                throw Utils::throwError('not_logged_in');

            if (!$this->currentPortfolioObj = Portfolio::where('id', $portfolio_id)->where('user_id', $user->id)->first())
                throw Utils::throwError('not_found', "Portfolio");

            if ($request->get('guideline') == NULL)
                throw Utils::throwError('custom', "Invalid Request");

            // In case of comma as decimal separator, change to dot for storing

            $request->merge(['min' => str_replace(',', '.', $request->min)]);
            $request->merge(['max' => str_replace(',', '.', $request->max)]);

            $guideline = $request->get('guideline');
            $min        = ($request->min) ? $request->min : 0;
            $max        = ($request->max) ? $request->max : 0;
            $attributes = ($request->get('attributes')) ? $request->get('attributes') : [];

            if ($min > $max)
                throw Utils::throwError('custom', "Invalid min, max params");

            $gAttrArray = $this->_getGuidelineAttrArray($attributes);

            $resultSet = $this->_calculateAndStoreGuideline($guideline, $min, $max, $gAttrArray);
            return $this->success_item($resultSet['message'], $resultSet['portfolioGuidelines']);
        }
        catch (HttpException $e)
        {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    private function calculateVariance($currentValue, $min, $max)
    {
        if ($currentValue < $min)
        {
            $variance = $currentValue - $min;
        }
        elseif ($currentValue > $max)
        {
            $variance = $currentValue - $max;
        }
        else
        {
            $variance = 0;
        }

        return $variance;
    }

    private function cashAllocation($min, $max)
    {
        $portfolioHoldingsService = \App::make(PortfolioHoldingsService::class, ['portfolio_id' => $this->currentPortfolioObj->id]);
        $totalBankBalance    = $portfolioHoldingsService->getTotalAllBankBalance();

        $currentValue        = ($totalBankBalance['totalAllBankBalance'] / $this->totalPortfolioValue) * 100;
        $variance            = $this->calculateVariance($currentValue, $min, $max);
        return ['current_value' => $currentValue, 'variance' => $variance];
    }

    private function securitiesInPortfolio($min, $max)
    {
        //Count number of security positions in portfolio with inventory above 0

        //fetch current portfolio holdings to process

        $portfolioHoldingsService = \App::make(PortfolioHoldingsService::class, ['portfolio_id' => $this->currentPortfolioObj->id]);
        $currentValue = $portfolioHoldingsService->getPortfolioHoldingsCount();
        $variance = $this->calculateVariance($currentValue, $min, $max);
        return ['current_value' => $currentValue, 'variance' => $variance];
    }

    private function riscPerPosition($min, $max)
    {
        $portfolioHoldingsService = \App::make(PortfolioHoldingsService::class, ['portfolio_id' => $this->currentPortfolioObj->id]);

        $securityHoldings = $portfolioHoldingsService->getPortfolioHoldings();

        $resultSet = [];

        foreach ($securityHoldings as $secHolding)
        {
            $currentValue = ($secHolding->gain_loss_in_base / $this->totalPortfolioValue) * 100;

            if ($secHolding->gain_loss_in_base < 0)
            {
                $variance = $this->calculateVariance(abs($currentValue), $min, $max);
            }
            else
            {
                $variance = 0;
            }

            $resultSet [] = [
                                'current_value' => $currentValue,
                                'variance'      => $variance,
                                'attribute'     => $secHolding->security->symbol,
                                'attribute_type'=> "security_symbol",
                            ];
        }

        return $resultSet;
    }

    private function currencyAllocation($min, $max, $currencyFilter)
    {
        $portfolioHoldingsService = \App::make(PortfolioHoldingsService::class, ['portfolio_id' => $this->currentPortfolioObj->id]);

        $totalMarketValueBase = $portfolioHoldingsService->getTotalMarketValueBase($currencyFilter);

        $currentValue            = ($totalMarketValueBase / $this->totalPortfolioValue) * 100;
        $variance                = $this->calculateVariance($currentValue, $min, $max);
        return ['current_value' => $currentValue, 'variance' => $variance];
    }

    private function securityTypeAllocation($min, $max, $securityTypeFilter)
    {
        $portfolioHoldingsService = \App::make(PortfolioHoldingsService::class, ['portfolio_id' => $this->currentPortfolioObj->id]);

        $totalMarketValueBase = $portfolioHoldingsService->getTotalMarketValueBase([], $securityTypeFilter);

        $currentValue            = ($totalMarketValueBase / $this->totalPortfolioValue) * 100;
        $variance                = $this->calculateVariance($currentValue, $min, $max);
        return ['current_value' => $currentValue, 'variance' => $variance];
    }

    private function tagAllocation($min, $max, $tagFilter)
    {
        $portfolioHoldingsService = \App::make(PortfolioHoldingsService::class, ['portfolio_id' => $this->currentPortfolioObj->id]);

        $totalMarketValueBase = $portfolioHoldingsService->getTotalMarketValueBase([], [], $tagFilter);

        $currentValue            = ($totalMarketValueBase / $this->totalPortfolioValue) * 100;
        $variance                = $this->calculateVariance($currentValue, $min, $max);
        return ['current_value' => $currentValue, 'variance' => $variance];
    }

    private function weightPerPosition($min, $max, $gAttrArray)
    {
        $portfolioHoldingsService = \App::make(PortfolioHoldingsService::class, ['portfolio_id' => $this->currentPortfolioObj->id]);

        $securityHoldings = $portfolioHoldingsService->getPortfolioHoldings();

        $resultSet = [];

        foreach ($securityHoldings as $secHolding)
        {
            $currentValue = $secHolding->weight;
            $variance     = $this->calculateVariance($currentValue, $min, $max);

            $resultSet [] = [
                                'current_value' => $currentValue,
                                'variance'      => $variance,
                                'attribute'     => $secHolding->security->symbol,
                                'attribute_type'=> "security_symbol",
                            ];
        }

        return $resultSet;
    }

    // DELETE /portfolio/remove_guidelines/{portfolio_id}/{guideline_id}
    // Remove Guideline from the given portfolio of the current user logged in.
    public function removeGuideline($portfolio_id, $guideline_id)
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }

            if (!$portfolio = Portfolio::where('id', $portfolio_id)->where('user_id', $user->id)->first())
                throw Utils::throwError('not_found', "Portfolio");

            $deleteStatus = PortfolioGuidelines::where('id', $guideline_id)
                                                ->where('portfolio_id', $portfolio_id)
                                                ->delete();

            return $this->success_state("Portfolio Guideline deleted successfully");
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }


    /**
     * Returns portfolio guidelines list
     *
     * @return array
     * @throws \Exception
     */
    public function getGuidelinesList()
    {
        try {
            $user = app('Dingo\Api\Auth\Auth')->user();
            if (!$user) {
                throw Utils::throwError('not_logged_in');
            }

            return $this->success_item("Guidelines", ['guideline_fields' => config('pecoonia.portfolio_guideline_types')]);
        }
        catch (HttpException $e) {
            return $this->error($e->getMessage(), $e->getCode());
        }
    }

    private function _getGuidelineAttrArray($guidelineAttributes)
    {
        $gAttrArray = [
            'securityTypeFilter' => [],
            'currencyFilter'     => [],
            'tagFilter'          => []
        ];

        foreach ($guidelineAttributes as $guidelineAttribute)
        {
            if ($guidelineAttribute['attribute_type'] == "security_type")
            {
                $gAttrArray['securityTypeFilter'][] = $guidelineAttribute['attribute'];
            }
            elseif ($guidelineAttribute['attribute_type'] == "currency")
            {
                $gAttrArray['currencyFilter'][] = $guidelineAttribute['attribute'];
            }
            elseif ($guidelineAttribute['attribute_type'] == "tag")
            {
                $gAttrArray['tagFilter'][] = $guidelineAttribute['attribute'];
            }
        }

        return $gAttrArray;
    }
}
