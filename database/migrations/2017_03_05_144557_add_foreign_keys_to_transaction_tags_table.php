<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToTransactionTagsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('transaction_tags', function(Blueprint $table)
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
		Schema::table('transaction_tags', function(Blueprint $table)
		{
			$table->dropForeign('transaction_tags_transaction_id_foreign');
		});
	}

}
