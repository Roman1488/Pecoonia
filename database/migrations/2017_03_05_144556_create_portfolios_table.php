<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreatePortfoliosTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('portfolios', function(Blueprint $table)
		{
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index('portfolios_user_id_foreign');
			$table->integer('currency_id')->unsigned()->index('portfolios_currency_id_foreign');
			$table->enum('date_format', array('dd-mm-yyyy','mm-dd-yyyy'));
			$table->string('name');
			$table->boolean('is_company');
			$table->boolean('comma_separator');
			$table->timestamps();
			$table->softDeletes();
			$table->boolean('status')->default(1)->comment('0: De-active, 1: Active');
		});
	}


	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('portfolios');
	}

}
