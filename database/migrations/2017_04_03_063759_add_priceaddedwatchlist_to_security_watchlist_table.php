<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPriceaddedwatchlistToSecurityWatchlistTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        if (!Schema::hasColumn('security_watchlist', 'price_added_watchlist'))
        {
            Schema::table('security_watchlist', function (Blueprint $table)
            {
                $table->decimal('price_added_watchlist', 15, 5)->default(0.00000)->after('security_id');
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasColumn('security_watchlist', 'price_added_watchlist'))
        {
            Schema::table('security_watchlist', function (Blueprint $table) {
                $table->dropColumn('price_added_watchlist');
            });
        }


    }
}
