<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateBankCashFlowTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('bank_cash_flow', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('bank_id')->unsigned()->index('bank_cash_flow_bank_id_foreign');
			$table->enum('type', array('add','minus'));
			$table->decimal('amount', 15, 5)->nullable()->default(0.00000);
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
		Schema::drop('bank_cash_flow');
	}

}
