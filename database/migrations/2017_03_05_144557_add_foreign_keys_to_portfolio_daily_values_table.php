<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToPortfolioDailyValuesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('portfolio_daily_values', function(Blueprint $table)
		{
			$table->foreign('portfolio_id', 'portfolio_dv_portfolio_id_foreign')->references('id')->on('portfolios')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('user_id', 'portfolio_dv_user_id_foreign')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('portfolio_daily_values', function(Blueprint $table)
		{
			$table->dropForeign('portfolio_dv_portfolio_id_foreign');
			$table->dropForeign('portfolio_dv_user_id_foreign');
		});
	}

}
