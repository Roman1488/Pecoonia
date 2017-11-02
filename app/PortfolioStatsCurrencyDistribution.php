<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PortfolioStatsCurrencyDistribution extends Model
{
    protected $table = 'portfolio_stats_currency_distribution';
    protected $guarded = [];


    public function portfolioStatistics()
    {
        return $this->belongsTo('App\PortfolioStatistics');
    }

}
