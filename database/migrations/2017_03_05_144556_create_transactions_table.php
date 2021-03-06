<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateTransactionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('transactions', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->integer('portfolio_id')->unsigned()->nullable()->index('transactions_portfolio_id_foreign');
			$table->integer('bank_id')->unsigned()->nullable()->index('transactions_bank_id_foreign');
			$table->bigInteger('security_id')->unsigned()->nullable()->index('transactions_security_id_foreign');
			$table->enum('transaction_type', array('buy','sell','dividend','bookvalue','cash_deposit','cash_withdraw'))->nullable();
			$table->decimal('trade_value', 15, 5)->nullable()->default(0.00000);
			$table->decimal('book_value', 15, 5)->nullable()->default(0.00000);
			$table->decimal('commision', 15, 5)->nullable()->default(0.00000);
			$table->boolean('is_commision')->default(0);
			$table->boolean('is_tax_included')->default(0);
			$table->boolean('is_same_currency')->default(0);
			$table->decimal('local_currency_rate', 15, 5)->nullable()->default(0.00000);
			$table->decimal('local_currency_rate_book_value', 15, 5)->nullable()->default(0.00000);
			$table->bigInteger('quantity');
			$table->integer('inventory');
			$table->dateTime('date');
			$table->decimal('dividend', 15, 5)->nullable()->default(0.00000);
			$table->decimal('tax', 15, 5)->nullable()->default(0.00000);
			$table->text('text');
			$table->decimal('c_commision_local', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_commision_base', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_trade_value_local', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_trade_value_base', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_trade_quote_local', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_trade_quote_base', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_trade_quote_commision_local', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_trade_quote_commision_base', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_purchase_value_local', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_purchase_value_base', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_book_value_local', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_book_value_base', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_purchase_price_local', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_purchase_price_base', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_book_price_local', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_book_price_base', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_purchase_currency_rate', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_book_currency_rate', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_profit_loss_book_value_local', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_profit_loss_book_value_base', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_profit_loss_purchase_value_local', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_profit_loss_purchase_value_base', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_profit_loss_purchase_value_lc_account', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_return_purchase_value_local', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_return_purchase_value_base', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_return_book_value_local', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_return_book_value_base', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_currency_profit_loss_book_value', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_currency_profit_loss_purchase_price', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_profit_loss_book_value_excurrency', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_profit_loss_purchase_price_excurrency', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_average_purchase_price', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_average_profit_loss_purchase_price', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_average_days_owned', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_daily_profit_loss_purchase_value_local', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_daily_profit_loss_purchase_value_base', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_daily_return_purchase_value_local', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_daily_return_purchase_value_base', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_annualized_return_purchase_value_local', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_annualized_return_purchase_value_base', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_dividend_local', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_dividend_base', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_net_dividend_local', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_net_dividend_base', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_tax_local', 15, 5)->nullable()->default(0.00000);
			$table->decimal('c_tax_base', 15, 5)->nullable()->default(0.00000);
			$table->timestamps();
			$table->enum('action', array('withdraw','deposit'));
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('transactions');
	}

}
