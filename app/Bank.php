<?php

namespace App;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Bank extends Model
{
    use SoftDeletes;
    use ModelTrait;

    protected $table = 'banks';
    protected $guarded = ['deleted_at'];
    protected $hidden = ['portfolio_id', 'deleted_at', 'currency_id'];
    protected $with = ['portfolio', 'currency'];

    static $fields_to_exclude = ['portfolio_id', 'deleted_at', 'currency_id'];
    static $export_associations = ['portfolio', 'currency'];

    public function currency()
    {
        return $this->belongsTo('App\Currency');
    }

    public function portfolio()
    {
        return $this->belongsTo('App\Portfolio');
    }

    public function transactions()
    {
        return $this->hasMany('App\Transaction');
    }
}
