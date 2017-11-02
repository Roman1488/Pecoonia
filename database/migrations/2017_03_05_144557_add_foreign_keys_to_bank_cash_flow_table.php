<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToBankCashFlowTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('bank_cash_flow', function(Blueprint $table)
		{
			$table->foreign('bank_id')->references('id')->on('banks')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('bank_cash_flow', function(Blueprint $table)
		{
			$table->dropForeign('bank_cash_flow_bank_id_foreign');
		});
	}

}
