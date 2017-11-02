<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSecuritiesTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('securities', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->integer('currency_id')->unsigned()->index('securities_currency_id_foreign');
			$table->string('symbol', 100);
			$table->string('name');
			$table->string('exchange', 100);
			$table->string('security_type', 100)->nullable();
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
		Schema::drop('securities');
	}

}
