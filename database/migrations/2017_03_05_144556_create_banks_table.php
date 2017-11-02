<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBanksTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('banks', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('portfolio_id')->unsigned()->index('banks_portfolio_id_foreign');
			$table->integer('currency_id')->unsigned()->index('banks_currency_id_foreign');
			$table->string('name');
			$table->boolean('enable_overdraft')->default(0);
			$table->timestamps();
			$table->softDeletes();
			$table->decimal('cash_amount', 15, 5)->nullable()->default(0.00000);
			$table->boolean('status')->default(1)->comment('0: De-active, 1: Active');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('banks');
	}

}
