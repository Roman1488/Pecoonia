<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class PortfolioHoldings extends Model
{
    protected $table = 'portfolio_holdings';
    protected $guarded = [];

    public function portfolio()
    {
        return $this->belongsTo('App\Portfolio');
    }

}
