<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;

use App\SecurityData;

class Security extends Model
{
    use ModelTrait;

    protected $table = "securities";
    protected $guarded = [];
    protected $hidden = ['currency_id'];
    protected $with = ['currency', 'data'];

    static $fields_to_exclude = ['currency_id'];
    static $export_associations = ['currency', 'data'];

    public function currency()
    {
        return $this->belongsTo('App\Currency');
    }

    public function data()
    {
        return $this->hasOne('App\SecurityData');
    }

    public function portfolios()
    {
        return $this->belongsToMany('App\Portfolio', 'user_security', 'security_id', 'portfolio_id');
    }

    public function transactions()
    {
        return $this->hasMany('App\Transaction');
    }

    public function saveSecurityData($response, $currency)
    {
        // Predeclared
        $security      = false;
        $security_data = false;

        try {
            // Check and Create new security

            if (!($security = Security::where('symbol', $response['symbol'])->first()))
            {
                $security = new Security();
                $security->currency_id   = $currency['id'];
                $security->symbol        = strtoupper($response['symbol']);
                $security->name          = $response['Name'];
                $security->exchange      = $response['exchange'];
                $security->security_type = $response['type'];
                $security->save();

                // Get change and PercentChange (always split by " - ")
                $change_percentChange = explode(" - ", $response['Change_PercentChange']);

                // Create security_data
                $security_data = new SecurityData();
                $security_data->security_id           = $security->id;
                $security_data->average_daily_volume  = $response['AverageDailyVolume'] ?: 0;
                $security_data->change                = $response['Change'] ?: 0;
                $security_data->percentage_change     = (isset($change_percentChange[1])) ? substr($change_percentChange[1], 0, -1) : 0;

                $lastTradeDate = '0000-00-00 00:00';
                if ($response['LastTradeDate'])
                {
                    list($m, $d, $y) = explode('/', $response['LastTradeDate']);
                    $lastTradeDate   = $y . '-' . $m . '-' . $d . ' 00:00';
                }

                $security_data->last_trade_date       = $lastTradeDate;
                $security_data->last_trade_price_only = $response['LastTradePriceOnly'] ?: 0;
                $security_data->days_low              = $response['DaysLow'] ?: 0;
                $security_data->days_high             = $response['DaysHigh'] ?: 0;
                $security_data->year_low              = $response['YearLow'] ?: 0;
                $security_data->year_high             = $response['YearHigh'] ?: 0;
                $security_data->volume                = $response['Volume'] ?: 0;
                $security_data->last_trade_time       = $response['LastTradeTime'] ?: '';
                $security_data->market_capitalization = $response['MarketCapitalization'] ?: '';
                $security_data->open                  = $response['Open'] ?: 0;
                $security_data->previous_close        = $response['PreviousClose'] ?: 0;
                $security_data->save();
            }

            return $security ? $security : false;

        } catch (QueryException $e) {

            // ACID test:
            // If an error occurs, make sure that neither Security nor SecurityData gets saved

            if ($security && $security->id)
                $security->delete();

            if ($security_data && $security_data->id)
                $security_data->delete();

            throw $e;
        }

        return false;
    }

}
