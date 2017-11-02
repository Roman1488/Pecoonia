<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTransactionHistoryTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('transaction_history', function(Blueprint $table)
		{
			$table->foreign('transaction_id')->references('id')->on('transactions')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('transaction_history', function(Blueprint $table)
		{
			$table->dropForeign('transaction_history_transaction_id_foreign');
		});
	}

}
