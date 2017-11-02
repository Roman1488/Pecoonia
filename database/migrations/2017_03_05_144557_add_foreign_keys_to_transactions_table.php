<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTransactionsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('transactions', function(Blueprint $table)
		{
			$table->foreign('bank_id')->references('id')->on('banks')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('portfolio_id')->references('id')->on('portfolios')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('security_id')->references('id')->on('securities')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('transactions', function(Blueprint $table)
		{
			$table->dropForeign('transactions_bank_id_foreign');
			$table->dropForeign('transactions_portfolio_id_foreign');
			$table->dropForeign('transactions_security_id_foreign');
		});
	}

}
