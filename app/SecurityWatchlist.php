<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SecurityWatchlist extends Model
{
    protected $table = 'security_watchlist';
    // protected $guarded = [];
    // protected $hidden = ['portfolio_id', 'security_id'];
    // protected $with = ['portfolio', 'security'];

    // static $fields_to_exclude = ['portfolio_id', 'security_id'];
    // static $export_associations = ['portfolio', 'security'];

    // public function portfolio()
    // {
    //     return $this->belongsTo('App\Portfolio');
    // }

    // public function security()
    // {
    //     return $this->belongsTo('App\Security');
    // }


}
