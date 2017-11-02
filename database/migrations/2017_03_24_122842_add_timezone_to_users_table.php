<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTimezoneToUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasColumn('users', 'timezone'))
        {
            Schema::table('users', function (Blueprint $table)
            {
                $table->string('timezone')->nullable()->default(null)->after('last_login');
            });

            // Initially load default values
            // \DB::statement('UPDATE users SET timezone = "Asia/Kolkata"');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('timezone');
        });
    }
}
