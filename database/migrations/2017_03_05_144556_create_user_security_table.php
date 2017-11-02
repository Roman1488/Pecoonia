<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUserSecurityTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('user_security', function(Blueprint $table)
		{
			$table->bigInteger('security_id')->unsigned()->index('user_security_security_id_foreign');
			$table->integer('portfolio_id')->unsigned()->index('user_security_portfolio_id_foreign');
			$table->integer('user_id')->unsigned()->index('user_security_user_id_foreign');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('user_security');
	}

}
