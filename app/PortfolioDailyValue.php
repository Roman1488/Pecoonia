<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PortfolioDailyValue extends Model
{
    protected $table = 'portfolio_daily_values';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function portfolio()
    {
        return $this->belongsTo('App\Portfolio');
    }

    public function daily_currency_distributions()
    {
        return $this->hasMany('App\CurrencyDistribution', 'portfolio_daily_values_id');
    }
}