<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class TransactionTag extends Model
{

    protected $table = 'transaction_tags';
    protected $guarded = [];

    public function transaction()
    {
        return $this->belongsTo('App\Transaction');
    }
}
