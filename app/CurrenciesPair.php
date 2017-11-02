<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CurrenciesPair extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'name', 'value'
    ];

    public $timestamps = false;
}
