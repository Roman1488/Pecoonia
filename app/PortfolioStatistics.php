<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PortfolioStatistics extends Model
{
    protected $table = 'portfolio_statistics';
    protected $guarded = [];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function portfolio()
    {
        return $this->belongsTo('App\Portfolio');
    }

    public function portfolio_stats_currency_distribution()
    {
        return $this->hasMany('App\PortfolioStatsCurrencyDistribution', 'portfolio_statistics_id');
    }
}