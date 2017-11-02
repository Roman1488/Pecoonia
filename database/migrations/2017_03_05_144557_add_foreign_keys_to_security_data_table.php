<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToSecurityDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('security_data', function(Blueprint $table)
		{
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
		Schema::table('security_data', function(Blueprint $table)
		{
			$table->dropForeign('security_data_security_id_foreign');
		});
	}

}
