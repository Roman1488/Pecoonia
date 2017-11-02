<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSecuritySplitsDividendsTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('security_splits_dividends', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('security_id')->unsigned()->index('security_splits_divs_security_id_foreign');
			$table->string('type', 50)->comment('dividend, split');
			$table->dateTime('date')->nullable();
			$table->string('value', 20)->nullable();
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
		Schema::drop('security_splits_dividends');
	}

}
