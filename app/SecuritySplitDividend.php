<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class SecuritySplitDividend extends Model
{
    protected $table = 'security_splits_dividends';
    protected $guarded = [];

    public function security()
    {
        return $this->belongsTo('App\Security');
    }
}