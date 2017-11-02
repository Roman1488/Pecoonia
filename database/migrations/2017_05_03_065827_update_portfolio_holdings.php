<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePortfolioHoldings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('portfolio_holdings', function(Blueprint $table)
        {
            $table->integer('user_id')->unsigned()->after('id')->index('portfolio_holdings_user_id_foreign');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('portfolio_holdings', function (Blueprint $table) {
            $table->dropColumn('user_id');

        });
    }
}
