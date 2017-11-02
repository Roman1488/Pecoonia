<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Currency extends Model
{

    protected $table = 'currencies';
    protected $guarded = [];
    protected $appends = ['currAlias'];

    public function portfolio()
    {
        return $this->belongsTo('App\Portfolio');
    }

    public function bank()
    {
        return $this->hasOne('App\Bank');
    }

    public function securities()
    {
        return $this->hasMany('App\Security');
    }

    public function getCurrAliasAttribute(){
    
        if (!$this->name) return '';

        return strtolower(preg_replace('/[^A-Z-]/' ,'', $this->name));

    }
}
