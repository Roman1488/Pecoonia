<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePortfolioDailyValuesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('portfolio_daily_values', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->integer('portfolio_id')->unsigned()->index('portfolio_dv_portfolio_id_foreign');
			$table->integer('user_id')->unsigned()->index('portfolio_dv_user_id_foreign');
			$table->decimal('total_marketvalue_base', 15, 5)->nullable()->default(0.00000);
			$table->decimal('total_all_bank_balance', 15, 5)->nullable()->default(0.00000);
			$table->timestamps();
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('portfolio_daily_values');
	}

}
