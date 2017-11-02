<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;

class CreateUsersTable extends Migration {

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('users', function(Blueprint $table)
		{
			$table->increments('id');
			$table->string('name');
			$table->string('user_name')->nullable()->unique();
			$table->string('email')->unique();
			$table->string('password');
			$table->string('remember_token', 100)->nullable();
			$table->timestamp('last_login')->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->timestamp('password_last_changed_at')->nullable()->default(DB::raw('CURRENT_TIMESTAMP'));
			$table->boolean('activated')->default(0)->comment('0: No, 1: Yes');
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
		Schema::drop('users');
	}

}
