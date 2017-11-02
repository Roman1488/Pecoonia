<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Transaction extends Model
{
    //
    Use ModelTrait;

    protected $table = "transactions";
    protected $guarded = [ 'c_commision_local',
                'c_commision_base' ,
                'c_trade_value_local' ,
                'c_trade_value_base' ,
                'c_trade_quote_local' ,
                'c_trade_quote_base' ,
                'c_trade_quote_commision_local' ,
                'c_trade_quote_commision_base' ,
                'c_purchase_value_local' ,
                'c_purchase_value_base' ,
                'c_book_value_local' ,
                'c_book_value_base' ,
                'c_purchase_price_local' ,
                'c_purchase_price_base' ,
                'c_book_price_local' ,
                'c_book_price_base' ,
                'c_purchase_currency_rate' ,
                'c_book_currency_rate' ,
                'c_profit_loss_book_value_local' ,
                'c_profit_loss_book_value_base' ,
                'c_profit_loss_purchase_value_local' ,
                'c_profit_loss_purchase_value_base' ,
                'c_profit_loss_purchase_value_lc_account' ,
                'c_return_purchase_value_local' ,
                'c_return_purchase_value_base',
                'c_return_book_value_local' ,
                'c_return_book_value_base' ,
                'c_currency_profit_loss_book_value' ,
                'c_currency_profit_loss_purchase_price' ,
                'c_profit_loss_book_value_excurrency' ,
                'c_profit_loss_purchase_price_excurrency' ,
                'c_average_purchase_price' ,
                'c_average_profit_loss_purchase_price' ,
                'c_average_days_owned' ,
                'c_daily_profit_loss_purchase_value_local' ,
                'c_daily_profit_loss_purchase_value_base' ,
                'c_daily_return_purchase_value_local' ,
                'c_daily_return_purchase_value_base' ,
                'c_annualized_return_purchase_value_local',
                'c_annualized_return_purchase_value_base' ,
                'c_dividend_local' ,
                'c_dividend_base' ,
                'c_net_dividend_local' ,
                'c_net_dividend_base' ,
                'c_tax_local',
                'c_tax_base' ];
    protected $hidden = ['portfolio_id', 'bank_id', 'security_id'];
    protected $with = ['portfolio', 'bank', 'security'];

    static $fields_to_exclude = ['portfolio_id', 'bank_id', 'security_id'];
    static $export_associations = ['portfolio', 'bank', 'security'];

    public function portfolio()
    {
        return $this->belongsTo('App\Portfolio');
    }

    public function bank()
    {
        return $this->belongsTo('App\Bank')->withTrashed();
    }

    public function security()
    {
        return $this->belongsTo('App\Security');
    }

    public function transaction_tags()
    {
        return $this->hasMany('App\TransactionTag');
    }
}
