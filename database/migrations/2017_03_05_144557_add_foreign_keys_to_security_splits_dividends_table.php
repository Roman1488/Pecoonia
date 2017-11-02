<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSecuritySplitsDividendsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('security_splits_dividends', function(Blueprint $table)
		{
			$table->foreign('security_id', 'security_splits_divs_security_id_foreign')->references('id')->on('securities')->onUpdate('RESTRICT')->onDelete('RESTRICT');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('security_splits_dividends', function(Blueprint $table)
		{
			$table->dropForeign('security_splits_divs_security_id_foreign');
		});
	}

}
