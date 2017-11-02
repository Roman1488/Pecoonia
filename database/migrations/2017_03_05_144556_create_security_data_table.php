<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateSecurityDataTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('security_data', function(Blueprint $table)
		{
			$table->bigInteger('id', true)->unsigned();
			$table->bigInteger('security_id')->unsigned()->index('security_data_security_id_foreign');
			$table->float('average_daily_volume', 15);
			$table->float('percentage_change', 15);
			$table->float('change', 15);
			$table->dateTime('last_trade_date');
			$table->string('last_trade_time', 20);
			$table->decimal('last_trade_price_only', 15, 5)->default(0.00000);
			$table->decimal('days_low', 15, 5)->nullable()->default(0.00000);
			$table->decimal('days_high', 15, 5)->nullable()->default(0.00000);
			$table->decimal('year_low', 15, 5)->nullable()->default(0.00000);
			$table->decimal('year_high', 15, 5)->nullable()->default(0.00000);
			$table->float('volume', 15);
			$table->string('market_capitalization', 20);
			$table->decimal('open', 15, 5)->nullable()->default(0.00000);
			$table->decimal('previous_close', 15, 5)->nullable()->default(0.00000);
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
		Schema::drop('security_data');
	}

}
