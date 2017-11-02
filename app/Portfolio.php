<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Portfolio extends Model
{
    use SoftDeletes;
    use ModelTrait;

    protected $table = 'portfolios';
    protected $guarded = ['deleted_at'];
    protected $hidden = ['currency_id', 'deleted_at'];
    protected $with = ['currency'];

    static $fields_to_exclude = ['currency_id', 'deleted_at'];
    static $export_associations = ['currency'];

    public function user()
    {
        return $this->belongsTo('App\User');
    }

    public function currency()
    {
        return $this->belongsTo('App\Currency');
    }

    public function bank()
    {
        return $this->hasMany('App\Bank');
    }

    public function securities()
    {
        return $this->belongsToMany('App\Security', 'user_security', 'portfolio_id');
    }

    public function transactions()
    {
        return $this->hasMany('App\Transaction');
    }
}
