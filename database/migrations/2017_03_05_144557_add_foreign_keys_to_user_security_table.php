<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class AddForeignKeysToUserSecurityTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('user_security', function(Blueprint $table)
		{
			$table->foreign('portfolio_id')->references('id')->on('portfolios')->onUpdate('RESTRICT')->onDelete('CASCADE');
			$table->foreign('security_id')->references('id')->on('securities')->onUpdate('RESTRICT')->onDelete('RESTRICT');
			$table->foreign('user_id')->references('id')->on('users')->onUpdate('RESTRICT')->onDelete('CASCADE');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('user_security', function(Blueprint $table)
		{
			$table->dropForeign('user_security_portfolio_id_foreign');
			$table->dropForeign('user_security_security_id_foreign');
			$table->dropForeign('user_security_user_id_foreign');
		});
	}

}
